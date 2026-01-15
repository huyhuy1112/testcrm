#!/bin/bash
# Script đơn giản để start Docker Compose

echo "🚀 Đang khởi động Docker Compose..."

# Thử docker-compose trước
if command -v docker-compose &> /dev/null; then
    docker-compose start 2>/dev/null && docker-compose ps && exit 0
fi

# Nếu không có docker-compose, dùng docker compose
if command -v docker &> /dev/null; then
    docker compose start 2>/dev/null && docker compose ps && exit 0
fi

# Nếu không có compose file, thử start tất cả containers
CONTAINERS=$(docker ps -a --format "{{.Names}}" 2>/dev/null)
if [ ! -z "$CONTAINERS" ]; then
    echo "📦 Đang khởi động các containers..."
    echo "$CONTAINERS" | xargs docker start
    docker ps
    exit 0
fi

echo "❌ Không tìm thấy docker-compose.yml hoặc containers"
echo "💡 Vui lòng đảm bảo Docker Desktop đã chạy"



