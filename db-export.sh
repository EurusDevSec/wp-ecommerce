#!/bin/bash
export MSYS_NO_PATHCONV=1

echo "📤 Đang xuất dữ liệu database từ Container MySQL..."
# Tạo thư mục db nếu chưa có
mkdir -p db

# Export database wordpress ra file db/init.sql bằng mysqldump
if docker compose exec -T db mysqldump -u wordpress -pwordpress_password wordpress > db/init.sql; then
    echo "✅ Xuất database thành công! File lưu tại: db/init.sql"
    echo "👉 Bây giờ bạn có thể commit và push file 'db/init.sql' lên Git."
else
    echo "❌ Lỗi: Không thể xuất database. Hãy chắc chắn rằng docker compose đang chạy."
fi
