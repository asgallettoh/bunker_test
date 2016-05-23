#!/usr/bin/env bash
apt-get update
 
debconf-set-selections <<< 'mysql-server mysql-server/root_password password bunker'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password bunker'

apt-get install -y nginx mysql-server php5-cli php5-fpm php5-mysql git curl

if ! [ -f /usr/bin/composer ]; then
	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin
	mv -f /usr/bin/composer.phar /usr/bin/composer
fi

cd /vagrant 

composer install --prefer-dist

cp -f /vagrant/default_nginx_site /etc/nginx/sites-enabled/default && service nginx restart

curl -sS https://bunkerdb.com/twitter.sql.bz2 | bunzip2 | mysql -u root -pbunker
