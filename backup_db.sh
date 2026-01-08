#!/bin/bash
################################################################################
# Database Backup Script for Vtiger Docker Setup
# 
# Purpose: Safely backup TDB1 database from Docker container vtiger_db
# 
# Usage: ./backup_db.sh
# 
# Requirements:
# - Docker daemon must be running
# - Container vtiger_db must be running
# - Database TDB1 must exist
# 
# Output:
# - Backup file: backup/db/TDB1_backup_YYYYMMDD_HHMM.sql
# - Log file: backup/logs/backup.log
################################################################################

set -euo pipefail  # Exit on error, undefined vars, pipe failures

# Configuration
CONTAINER_NAME="vtiger_db"
DATABASE_NAME="TDB1"
MYSQL_USER="root"
BACKUP_DIR="backup/db"
LOG_DIR="backup/logs"
LOG_FILE="${LOG_DIR}/backup.log"

# Colors for output (optional, for better readability)
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

# Check if database exists
check_database() {
    local db_exists=$(docker exec "${CONTAINER_NAME}" mysql -u"${MYSQL_USER}" -p"${MYSQL_ROOT_PASSWORD}" -e "SHOW DATABASES LIKE '${DATABASE_NAME}';" 2>/dev/null | grep -c "${DATABASE_NAME}" || echo "0")
    
    if [ "$db_exists" -eq 0 ]; then
        error_exit "Database '${DATABASE_NAME}' does not exist in container '${CONTAINER_NAME}'"
    fi
    
    log_info "Database '${DATABASE_NAME}' exists"
}

################################################################################
# Main Backup Function
################################################################################

create_backup() {
    # Generate backup filename with timestamp
    local timestamp=$(date '+%Y%m%d_%H%M')
    local backup_file="${BACKUP_DIR}/TDB1_backup_${timestamp}.sql"
    
    log_info "Starting backup of database '${DATABASE_NAME}'..."
    log_info "Backup file: ${backup_file}"
    
    # Get MySQL root password from environment or docker-compose
    # Try to get from environment first, then from docker-compose.yml
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
    
    # Perform backup using docker exec
    # --single-transaction: Ensures consistent backup for InnoDB tables
    # --routines: Includes stored procedures and functions
    # --triggers: Includes triggers
    # --events: Includes events
    # --add-drop-database: Adds DROP DATABASE statement
    # --add-drop-table: Adds DROP TABLE statements
    if docker exec "${CONTAINER_NAME}" mysqldump \
        -u"${MYSQL_USER}" \
        -p"${MYSQL_ROOT_PASSWORD}" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --add-drop-database \
        --add-drop-table \
        --databases "${DATABASE_NAME}" > "${backup_file}" 2>&1; then
        
        # Check if backup file was created and has content
        if [ ! -f "${backup_file}" ]; then
            error_exit "Backup file was not created: ${backup_file}"
        fi
        
        if [ ! -s "${backup_file}" ]; then
            error_exit "Backup file is empty: ${backup_file}"
        fi
        
        # Get file size for logging
        local file_size=$(du -h "${backup_file}" | cut -f1)
        
        log_success "Backup completed successfully"
        log_info "Backup file: ${backup_file}"
        log_info "File size: ${file_size}"
        
        echo -e "${GREEN}Backup completed: ${backup_file} (${file_size})${NC}"
        return 0
    else
        error_exit "Backup failed. Check logs for details."
    fi
}

################################################################################
# Main Execution
################################################################################

main() {
    # Create directories if they don't exist
    mkdir -p "${BACKUP_DIR}" "${LOG_DIR}"
    
    log_info "=========================================="
    log_info "Database Backup Started"
    log_info "=========================================="
    
    # Run validations
    check_docker
    check_container
    check_database
    
    # Perform backup
    if create_backup; then
        log_info "=========================================="
        log_info "Database Backup Completed Successfully"
        log_info "=========================================="
        exit 0
    else
        log_info "=========================================="
        log_info "Database Backup Failed"
        log_info "=========================================="
        exit 1
    fi
}

# Run main function
main "$@"

