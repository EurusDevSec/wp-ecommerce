#!/bin/bash
export MSYS_NO_PATHCONV=1

# Kiểm tra xem file db/init.sql có tồn tại không
if [ ! -f "db/init.sql" ]; then
    echo "❌ Lỗi: Không tìm thấy file db/init.sql để import!"
    exit 1
fi

echo "📥 Đang nhập dữ liệu database vào Container MySQL..."
# Import file db/init.sql vào database wordpress trong container
if docker compose exec -T db mysql -u wordpress -pwordpress_password wordpress < db/init.sql; then
    echo "✅ Nhập database thành công!"
    
    # Hỏi người dùng xem có cần đổi URL không (ví dụ từ localhost sang tên miền khác)
    echo "🔗 Bạn có muốn cập nhật URL trong Database không? (y/n)"
    read -r change_url
    if [[ "$change_url" =~ ^[Yy]$ ]]; then
        echo "Nhập URL cũ cần thay thế (mặc định: http://localhost:8000):"
        read -r old_url
        old_url=${old_url:-http://localhost:8000}
        
        echo "Nhập URL mới mong muốn (ví dụ: http://localhost:8000 hoặc https://your-tunnel.trycloudflare.com):"
        read -r new_url
        
        if [ -n "$new_url" ]; then
            echo "⚡ Đang chạy WP-CLI để thay đổi URL từ '$old_url' thành '$new_url'..."
            docker compose run --rm cli wp search-replace "$old_url" "$new_url" --allow-root
            echo "✅ Cập nhật URL thành công!"
        else
            echo "⚠️ URL mới trống, bỏ qua bước đổi URL."
        fi
    fi
else
    echo "❌ Lỗi: Không thể nhập database. Hãy chắc chắn rằng docker compose đang chạy."
fi
