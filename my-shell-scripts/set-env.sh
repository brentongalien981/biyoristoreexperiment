#!/bin/sh

cp /app/config/cors.php /app/vendor/fruitcake/laravel-cors/config/cors.php

cd /app
touch .env


echo "APP_NAME=${APP_NAME}" >> .env
echo "APP_ENV=${APP_ENV}" >> .env
echo "APP_KEY=${APP_KEY}" >> .env
echo "APP_DEBUG=${APP_DEBUG}" >> .env
echo "APP_URL=${APP_URL}" >> .env
echo "LOG_CHANNEL=${LOG_CHANNEL}" >> .env
echo "LOG_LEVEL=${LOG_LEVEL}" >> .env
echo "DB_CONNECTION=${DB_CONNECTION}" >> .env
echo "DB_HOST=${DB_HOST}" >> .env
echo "DB_PORT=${DB_PORT}" >> .env
echo "DB_DATABASE=${DB_DATABASE}" >> .env
echo "DB_USERNAME=${DB_USERNAME}" >> .env
echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
echo "BROADCAST_DRIVER=${BROADCAST_DRIVER}" >> .env
echo "CACHE_DRIVER=${CACHE_DRIVER}" >> .env
echo "FILESYSTEM_DRIVER=${FILESYSTEM_DRIVER}" >> .env
echo "QUEUE_CONNECTION=${QUEUE_CONNECTION}" >> .env
echo "SESSION_DRIVER=${SESSION_DRIVER}" >> .env
echo "SESSION_LIFETIME=${SESSION_LIFETIME}" >> .env
echo "MEMCACHED_HOST=${MEMCACHED_HOST}" >> .env
echo "REDIS_HOST=${REDIS_HOST}" >> .env
echo "REDIS_PASSWORD=${REDIS_PASSWORD}" >> .env
echo "REDIS_PORT=${REDIS_PORT}" >> .env
echo "MAIL_MAILER=${MAIL_MAILER}" >> .env
echo "MAIL_HOST=${MAIL_HOST}" >> .env
echo "MAIL_PORT=${MAIL_PORT}" >> .env
echo "MAIL_USERNAME=${MAIL_USERNAME}" >> .env
echo "MAIL_PASSWORD=${MAIL_PASSWORD}" >> .env
echo "MAIL_ENCRYPTION=${MAIL_ENCRYPTION}" >> .env
echo "MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}" >> .env
echo "MAIL_FROM_NAME=${MAIL_FROM_NAME}" >> .env
echo "AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}" >> .env
echo "AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}" >> .env
echo "AWS_DEFAULT_REGION=${AWS_DEFAULT_REGION}" >> .env
echo "AWS_BUCKET=${AWS_BUCKET}" >> .env
echo "AWS_USE_PATH_STYLE_ENDPOINT=${AWS_USE_PATH_STYLE_ENDPOINT}" >> .env
echo "PUSHER_APP_ID=${PUSHER_APP_ID}" >> .env
echo "PUSHER_APP_KEY=${PUSHER_APP_KEY}" >> .env
echo "PUSHER_APP_SECRET=${PUSHER_APP_SECRET}" >> .env
echo "PUSHER_APP_CLUSTER=${PUSHER_APP_CLUSTER}" >> .env
echo "MIX_PUSHER_APP_KEY=${MIX_PUSHER_APP_KEY}" >> .env
echo "MIX_PUSHER_APP_CLUSTER=${MIX_PUSHER_APP_CLUSTER}" >> .env
echo "WEB_DOCUMENT_ROOT=${WEB_DOCUMENT_ROOT}" >> .env






chown -R application:application .



php artisan config:cache
php artisan route:cache
php artisan view:cache
