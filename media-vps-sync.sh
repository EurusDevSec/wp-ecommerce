#!/bin/bash
# HKT Fashion Media Sync Utility
export MSYS_NO_PATHCONV=1

# Cấu hình VPS DigitalOcean Droplet
VPS_IP="167.172.91.249"
SSH_USER="root"
REMOTE_PATH="/var/www/wp-ecommerce/src/wp-content/uploads/"
LOCAL_PATH="src/wp-content/uploads/"

echo "========================================================"
echo "   🔄 TIỆN ÍCH ĐỒNG BỘ HÌNH ẢNH (MEDIA SYNC UTILITY)    "
echo "========================================================"
echo "Địa chỉ VPS Staging: $VPS_IP (User: $SSH_USER)"
echo "Đường dẫn từ xa (VPS): $REMOTE_PATH"
echo "--------------------------------------------------------"
echo "1) ĐẨY ảnh từ máy Local lên VPS Staging (Push)"
echo "2) TẢI ảnh từ VPS Staging về máy Local (Pull)"
echo "3) Thoát"
echo "--------------------------------------------------------"
read -r -p "Chọn hành động của bạn [1-3]: " choice

case $choice in
    1)
        echo "📤 Đang quét và đồng bộ ảnh từ Local lên VPS..."
        # Loại trừ file .htaccess để tránh đè cấu hình online
        if rsync -avzh --progress --exclude='.htaccess' "$LOCAL_PATH" "$SSH_USER"@"$VPS_IP":"$REMOTE_PATH"; then
            echo "✅ Đồng bộ ảnh lên VPS thành công!"
        else
            echo "❌ Lỗi: Không thể đồng bộ qua rsync. Hãy chắc chắn máy đã cài rsync và cấu hình SSH Key cho user '$SSH_USER' trên CloudPanel."
        fi
        ;;
    2)
        echo "📥 Đang tải ảnh mới nhất từ VPS về Local..."
        if rsync -avzh --progress --exclude='.htaccess' "$SSH_USER"@"$VPS_IP":"$REMOTE_PATH" "$LOCAL_PATH"; then
            echo "✅ Tải ảnh từ VPS về Local thành công!"
        else
            echo "❌ Lỗi: Không thể đồng bộ qua rsync. Hãy chắc chắn máy đã cài rsync và cấu hình SSH Key cho user '$SSH_USER' trên CloudPanel."
        fi
        ;;
    *)
        echo "👋 Hủy thao tác."
        exit 0
        ;;
esac
