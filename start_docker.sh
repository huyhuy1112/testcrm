#!/bin/bash

echo "🔍 Đang kiểm tra Docker daemon..."

# Đợi Docker daemon sẵn sàng (tối đa 60 giây)
max_wait=60
elapsed=0
while ! docker info >/dev/null 2>&1; do
    if [ $elapsed -ge $max_wait ]; then
        echo "❌ Docker daemon không khởi động được sau $max_wait giây"
        echo "💡 Vui lòng khởi động Docker Desktop thủ công từ Applications"
        exit 1
    fi
    echo "⏳ Đang chờ Docker daemon... ($elapsed/$max_wait giây)"
    sleep 2
    elapsed=$((elapsed + 2))
done

echo "✅ Docker daemon đã sẵn sàng!"

# Tìm docker-compose.yml
COMPOSE_FILE=""
if [ -f "docker-compose.yml" ]; then
    COMPOSE_FILE="docker-compose.yml"
elif [ -f "docker-compose.yaml" ]; then
    COMPOSE_FILE="docker-compose.yaml"
elif [ -f "docker-setup/docker-compose.yml" ]; then
    COMPOSE_FILE="docker-setup/docker-compose.yml"
    cd docker-setup
elif [ -f "compose.yml" ]; then
    COMPOSE_FILE="compose.yml"
elif [ -f "compose.yaml" ]; then
    COMPOSE_FILE="compose.yaml"
fi

if [ -z "$COMPOSE_FILE" ]; then
    echo "⚠️  Không tìm thấy docker-compose.yml"
    echo "📋 Đang kiểm tra containers hiện có..."
    
    # Thử start tất cả containers đã tồn tại
    CONTAINERS=$(docker ps -a --format "{{.Names}}" 2>/dev/null)
    if [ -z "$CONTAINERS" ]; then
        echo "❌ Không có containers nào"
        exit 1
    else
        echo "🚀 Đang khởi động các containers..."
        echo "$CONTAINERS" | xargs -I {} docker start {} 2>/dev/null
        echo "✅ Đã khởi động containers"
        docker ps
    fi
else
    echo "📄 Tìm thấy: $COMPOSE_FILE"
    echo "🚀 Đang khởi động Docker Compose..."
    
    # Thử docker-compose trước, nếu không có thì dùng docker compose
    if command -v docker-compose &> /dev/null; then
        docker-compose -f "$COMPOSE_FILE" up -d
    else
        docker compose -f "$COMPOSE_FILE" up -d
    fi
    
    if [ $? -eq 0 ]; then
        echo "✅ Docker Compose đã khởi động thành công!"
        docker-compose ps 2>/dev/null || docker compose ps 2>/dev/null || docker ps
    else
        echo "❌ Lỗi khi khởi động Docker Compose"
        exit 1
    fi
fi


