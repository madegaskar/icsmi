web: vendor/bin/heroku-php-apache2 -i /usr/local/etc/php/conf.d/php.ini public/
release: php artisan migrate --force
worker: php artisan queue:work
