#!/bin/bash
################################################################################
# Database Restore Script for Vtiger Docker Setup
# 
# Purpose: Safely restore TDB1 database from backup file
# 
# Usage: ./restore_db.sh <backup_file>
# Example: ./restore_db.sh backup/db/TDB1_backup_20260107_1430.sql
# 
# Requirements:
# - Docker daemon must be running
# - Container vtiger_db must be running
# - Backup file must exist and be valid
# 
# Safety:
# - Asks for confirmation before overwriting database
# - Validates backup file before restore
# - Logs all operations
################################################################################

set -euo pipefail  # Exit on error, undefined vars, pipe failures

# Configuration
CONTAINER_NAME="vtiger_db"
DATABASE_NAME="TDB1"
MYSQL_USER="root"
LOG_DIR="backup/logs"
LOG_FILE="${LOG_DIR}/backup.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

################################################################################
# Helper Functions
################################################################################

# Log message with timestamp
log() {
    local level="$1"
    shift
    local message="$*"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] [$level] $message" | tee -a "$LOG_FILE"
}

# Log error and exit
error_exit() {
    log "ERROR" "$@"
    echo -e "${RED}ERROR:${NC} $*" >&2
    exit 1
}

# Log success
log_success() {
    log "SUCCESS" "$@"
    echo -e "${GREEN}SUCCESS:${NC} $*"
}

# Log warning
log_warning() {
    log "WARNING" "$@"
    echo -e "${YELLOW}WARNING:${NC} $*"
}

# Log info
log_info() {
    log "INFO" "$@"
    echo -e "INFO: $*"
}

################################################################################
# Validation Functions
################################################################################

# Check if Docker daemon is running
check_docker() {
    if ! command -v docker &> /dev/null; then
        error_exit "Docker command not found. Please install Docker."
    fi
    
    if ! docker info &> /dev/null; then
        error_exit "Docker daemon is not running. Please start Docker Desktop."
    fi
    
    log_info "Docker daemon is running"
}

# Check if container exists and is running
check_container() {
    if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
        error_exit "Container '${CONTAINER_NAME}' is not running. Please start it with: docker compose up -d"
    fi
    
    log_info "Container '${CONTAINER_NAME}' is running"
}

# Validate backup file
validate_backup_file() {
    local backup_file="$1"
    
    # Check if file exists
    if [ ! -f "${backup_file}" ]; then
        error_exit "Backup file not found: ${backup_file}"
    fi
    
    # Check if file is readable
    if [ ! -r "${backup_file}" ]; then
        error_exit "Backup file is not readable: ${backup_file}"
    fi
    
    # Check if file is not empty
    if [ ! -s "${backup_file}" ]; then
        error_exit "Backup file is empty: ${backup_file}"
    fi
    
    # Check if file looks like a SQL dump (basic validation)
    if ! head -n 5 "${backup_file}" | grep -qi "mysql\|sql\|database\|table"; then
        log_warning "Backup file may not be a valid SQL dump. Proceeding anyway..."
    fi
    
    log_info "Backup file validated: ${backup_file}"
    local file_size=$(du -h "${backup_file}" | cut -f1)
    log_info "File size: ${file_size}"
}

# Ask for confirmation
ask_confirmation() {
    local backup_file="$1"
    
    echo ""
    echo -e "${YELLOW}========================================${NC}"
    echo -e "${YELLOW}WARNING: Database Restore${NC}"
    echo -e "${YELLOW}========================================${NC}"
    echo ""
    echo "This will RESTORE database '${DATABASE_NAME}' from backup file:"
    echo "  ${backup_file}"
    echo ""
    echo -e "${RED}WARNING: This will OVERWRITE all existing data in database '${DATABASE_NAME}'${NC}"
    echo ""
    echo -n "Are you sure you want to continue? (yes/no): "
    read -r confirmation
    
    if [ "${confirmation}" != "yes" ]; then
        log_info "Restore cancelled by user"
        echo "Restore cancelled."
        exit 0
    fi
    
    log_info "User confirmed restore operation"
}

################################################################################
# Restore Function
################################################################################

restore_database() {
    local backup_file="$1"
    
    log_info "Starting restore of database '${DATABASE_NAME}'..."
    log_info "Backup file: ${backup_file}"
    
    # Get MySQL root password from environment or docker-compose
    if [ -z "${MYSQL_ROOT_PASSWORD:-}" ]; then
        # Extract password from docker-compose.yml (if available)
        if [ -f "docker-compose.yml" ]; then
            # Look for MYSQL_ROOT_PASSWORD in db service section
            MYSQL_ROOT_PASSWORD=$(grep "MYSQL_ROOT_PASSWORD:" docker-compose.yml | head -1 | awk '{print $2}' | tr -d '"' | tr -d "'" || echo "")
        fi
        
        if [ -z "${MYSQL_ROOT_PASSWORD:-}" ]; then
            error_exit "MYSQL_ROOT_PASSWORD not set. Please set it as environment variable: export MYSQL_ROOT_PASSWORD=132120"
        fi
    fi
    
    # Restore database using docker exec
    # Pipe SQL file into mysql command inside container
    log_info "Restoring database (this may take a while)..."
    
    if docker exec -i "${CONTAINER_NAME}" mysql \
        -u"${MYSQL_USER}" \
        -p"${MYSQL_ROOT_PASSWORD}" \
        < "${backup_file}" 2>&1 | tee -a "${LOG_FILE}"; then
        
        log_success "Database restore completed successfully"
        log_info "Database '${DATABASE_NAME}' has been restored from: ${backup_file}"
        
        echo -e "${GREEN}Restore completed successfully!${NC}"
        return 0
    else
        error_exit "Database restore failed. Check logs for details."
    fi
}

################################################################################
# Main Execution
################################################################################

main() {
    # Check arguments
    if [ $# -eq 0 ]; then
        echo "Usage: $0 <backup_file>"
        echo "Example: $0 backup/db/TDB1_backup_20260107_1430.sql"
        exit 1
    fi
    
    local backup_file="$1"
    
    # Create log directory if it doesn't exist
    mkdir -p "${LOG_DIR}"
    
    log_info "=========================================="
    log_info "Database Restore Started"
    log_info "=========================================="
    
    # Run validations
    check_docker
    check_container
    validate_backup_file "${backup_file}"
    
    # Ask for confirmation
    ask_confirmation "${backup_file}"
    
    # Perform restore
    if restore_database "${backup_file}"; then
        log_info "=========================================="
        log_info "Database Restore Completed Successfully"
        log_info "=========================================="
        exit 0
    else
        log_info "=========================================="
        log_info "Database Restore Failed"
        log_info "=========================================="
        exit 1
    fi
}

# Run main function
main "$@"

