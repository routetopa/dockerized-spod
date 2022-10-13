p1=$1
p2=$2
p3=$3

EMAIL="${p1:=testmail@gmail.com}"
PASSWORD="${p2:=oxwall}"
HOST_URL="${p3:=localhost}"

echo "Creating oauth2 db..."
sed -i "s/@password/$PASSWORD/g" db.sql
sed -i "s/@password/$PASSWORD/g" .env
mysql -h mysql -p$PASSWORD  < db.sql 

php artisan migrate

echo "Populating oauth2 db..."
sed -i "s/localhost/$HOST_URL/g" init.sql 
mysql -h mysql -p$PASSWORD -e"set @client_secret= '${PASSWORD}'; \. init.sql" && rm -f init.sql

echo "Creating admin user with email $EMAIL"

sed -i "s/DEFMAIL/$EMAIL/g" init.php
sed -i "s/DEFPASS/$PASSWORD/g" init.php
more init.php | php artisan tinker
# rm -f init.php
