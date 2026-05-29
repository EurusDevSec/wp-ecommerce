

# Dowload Wordpress-7.0

curl -LO https://wordpress.org/wordpress-7.0.zip


# unzip 

unzip  wordpress-7.0.zip

# move wordpress folder to src folder
mv wordpress src/

# remove zip
rm -rf wordpress-7.0.zip

echo "Cài đặt hoàn tất!🫠🫠🫠"

# run docker compose

docker compose up -d

# access http://localhost:8000

# then go to  http://localhost:8000/wp-admin/install.php to setup wordpress


 