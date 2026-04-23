#!/bin/bash
set -e

cd /var/www/html

echo "==> Checking vendor dependencies..."
if [ ! -f vendor/autoload.php ]; then
    echo "==> Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

echo "==> Generating JWT keys if missing..."
if [ ! -f config/jwt/private.pem ]; then
    mkdir -p config/jwt
    openssl genpkey -algorithm RSA \
        -out config/jwt/private.pem \
        -aes256 \
        -pass pass:"${JWT_PASSPHRASE:-jwt_passphrase_change_me}"
    openssl pkey \
        -in config/jwt/private.pem \
        -passin pass:"${JWT_PASSPHRASE:-jwt_passphrase_change_me}" \
        -pubout \
        -out config/jwt/public.pem
    echo "==> JWT keys generated."
fi

# Always ensure correct permissions (key may exist from previous run as root)
chown www-data:www-data config/jwt/private.pem config/jwt/public.pem 2>/dev/null || true
chmod 640 config/jwt/private.pem 2>/dev/null || true
chmod 644 config/jwt/public.pem 2>/dev/null || true

echo "==> Clearing cache..."
php bin/console cache:clear --no-warmup --env="${APP_ENV:-dev}" 2>/dev/null || true

echo "==> Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "==> Starting Apache..."
exec apache2-foreground
