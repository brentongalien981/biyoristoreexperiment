#!/bin/sh
cd /app
touch .env

echo "##################### START MY SHIT ####################"
echo "Setting FILE: .env"


echo "APP_NAME=${APP_NAME}" >> .env
echo "APP_ENV=${APP_ENV}" >> .env
echo "APP_KEY=${APP_KEY}" >> .env
echo "APP_DEBUG=${APP_DEBUG}" >> .env
echo "APP_URL=${APP_URL}" >> .env
echo "APP_FRONTEND_URL=${APP_FRONTEND_URL}" >> .env
echo "APP_FRONTEND_REFERER_URL=${APP_FRONTEND_REFERER_URL}" >> .env
echo "LOG_CHANNEL=${LOG_CHANNEL}" >> .env
echo "DB_CONNECTION=${DB_CONNECTION}" >> .env
echo "DB_HOST=${DB_HOST}" >> .env
echo "DB_PORT=${DB_PORT}" >> .env
echo "DB_DATABASE=${DB_DATABASE}" >> .env
echo "DB_USERNAME=${DB_USERNAME}" >> .env
echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
echo "BROADCAST_DRIVER=${BROADCAST_DRIVER}" >> .env
echo "CACHE_DRIVER=${CACHE_DRIVER}" >> .env
echo "QUEUE_CONNECTION=${QUEUE_CONNECTION}" >> .env
echo "SESSION_DRIVER=${SESSION_DRIVER}" >> .env
echo "SESSION_LIFETIME=${SESSION_LIFETIME}" >> .env
echo "REDIS_PASSWORD=${REDIS_PASSWORD}" >> .env
echo "REDIS_PORT=${REDIS_PORT}" >> .env
echo "REDIS_HOST=${REDIS_HOST}" >> .env
echo "REDIS_PRIMARY=${REDIS_PRIMARY}" >> .env
echo "REDIS_READER=${REDIS_READER}" >> .env
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
echo "SES_AWS_ACCESS_KEY_ID=${SES_AWS_ACCESS_KEY_ID}" >> .env
echo "SES_AWS_SECRET_ACCESS_KEY=${SES_AWS_SECRET_ACCESS_KEY}" >> .env
echo "SQS_AWS_ACCESS_KEY_ID=${SQS_AWS_ACCESS_KEY_ID}" >> .env
echo "SQS_AWS_SECRET_ACCESS_KEY=${SQS_AWS_SECRET_ACCESS_KEY}" >> .env
echo "SQS_PREFIX=${SQS_PREFIX}" >> .env
echo "SQS_QUEUE=${SQS_QUEUE}" >> .env
echo "PUSHER_APP_ID=${PUSHER_APP_ID}" >> .env
echo "PUSHER_APP_KEY=${PUSHER_APP_KEY}" >> .env
echo "PUSHER_APP_SECRET=${PUSHER_APP_SECRET}" >> .env
echo "PUSHER_APP_CLUSTER=${PUSHER_APP_CLUSTER}" >> .env
echo "MIX_PUSHER_APP_KEY=${MIX_PUSHER_APP_KEY}" >> .env
echo "MIX_PUSHER_APP_CLUSTER=${MIX_PUSHER_APP_CLUSTER}" >> .env
echo "STRIPE_PK=${STRIPE_PK}" >> .env
echo "STRIPE_SK=${STRIPE_SK}" >> .env
echo "EASYSHIP_SAND_ACCESS_TOKEN=${EASYSHIP_SAND_ACCESS_TOKEN}" >> .env
echo "EASYPOST_PK=${EASYPOST_PK}" >> .env
echo "EASYPOST_TK=${EASYPOST_TK}" >> .env
echo "GOOGLE_CLIENT_ID=${GOOGLE_CLIENT_ID}" >> .env
echo "GOOGLE_CLIENT_SECRET=${GOOGLE_CLIENT_SECRET}" >> .env
echo "GOOGLE_SOCIALITE_REDIRECT_LINK=${GOOGLE_SOCIALITE_REDIRECT_LINK}" >> .env
echo "FACEBOOK_APP_ID=${FACEBOOK_APP_ID}" >> .env
echo "FACEBOOK_APP_SECRET=${FACEBOOK_APP_SECRET}" >> .env
echo "FACEBOOK_SOCIALITE_REDIRECT_LINK=${FACEBOOK_SOCIALITE_REDIRECT_LINK}" >> .env
echo "DYNAMODB_CACHE_TABLE=${DYNAMODB_CACHE_TABLE}" >> .env
echo "DYNAMODB_ENDPOINT=${DYNAMODB_ENDPOINT}" >> .env
echo "DYNAMODB_AWS_ACCESS_KEY_ID=${DYNAMODB_AWS_ACCESS_KEY_ID}" >> .env
echo "DYNAMODB_AWS_SECRET_ACCESS_KEY=${DYNAMODB_AWS_SECRET_ACCESS_KEY}" >> .env
echo "PASSPORT_GRANT_PASSWORD_CLIENT_ID=${PASSPORT_GRANT_PASSWORD_CLIENT_ID}" >> .env
echo "PASSPORT_GRANT_PASSWORD_CLIENT_SECRET=${PASSPORT_GRANT_PASSWORD_CLIENT_SECRET}" >> .env
echo "WEB_DOCUMENT_ROOT=${WEB_DOCUMENT_ROOT}" >> .env



echo "PASSPORT_PRIVATE_KEY=\"" >> .env
cat /app/storage/oauth-private.key >> .env
echo "\"" >> .env


echo "PASSPORT_PUBLIC_KEY=\"" >> .env
cat /app/storage/oauth-public.key >> .env
echo "\"" >> .env


###
echo "FILE: .env has been set."


echo "Changing file ownerships..."
chown -R application:application .


echo "File ownerships have been set."
echo "##################### END MY SHIT ####################"



php artisan config:cache
php artisan route:cache
php artisan view:cache
