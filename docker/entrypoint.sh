#!/bin/sh
set -e

PORT="${PORT:-10000}"

if [ -n "$FIREBASE_SERVICE_ACCOUNT_JSON" ]; then
  mkdir -p storage/app/firebase
  printf '%s' "$FIREBASE_SERVICE_ACCOUNT_JSON" > storage/app/firebase/service-account.json
  php -r 'exit(json_decode(file_get_contents("storage/app/firebase/service-account.json")) ? 0 : 1);' \
    || { echo "FIREBASE_SERVICE_ACCOUNT_JSON no es JSON válido" >&2; exit 1; }
  export FIREBASE_CREDENTIALS=storage/app/firebase/service-account.json
fi

php artisan storage:link --force 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port="$PORT"
