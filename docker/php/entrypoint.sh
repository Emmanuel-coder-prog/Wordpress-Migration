#!/usr/bin/env bash
set -Eeuo pipefail

: "${WP_ROOT:?WP_ROOT is required}"

if [[ "${WP_ROOT}" != /var/www/html* ]]; then
  echo "Unsafe WP_ROOT: ${WP_ROOT}" >&2
  exit 1
fi

mkdir -p "${WP_ROOT}"

if [[ ! -f "${WP_ROOT}/wp-settings.php" ]]; then
  echo "Initializing WordPress ${WORDPRESS_VERSION} in ${WP_ROOT}"

  rsync -a \
    --delete \
    --exclude='wp-content/' \
    /usr/src/wordpress/ \
    "${WP_ROOT}/"
fi

install \
  -o www-data \
  -g www-data \
  -m 0640 \
  /opt/cetech/wp-config.php \
  "${WP_ROOT}/wp-config.php"

mkdir -p \
  "${WP_ROOT}/wp-content" \
  "${WP_ROOT}/wp-content/uploads" \
  "${WP_ROOT}/wp-content/cache" \
  "${WP_ROOT}/wp-content/upgrade" \
  "${WP_ROOT}/wp-content/mu-plugins" \
  "${WP_ROOT}/wp-content/plugins" \
  "${WP_ROOT}/wp-content/themes"

if [[ -d /opt/cetech/wp-content ]]; then
  rsync -a \
    --ignore-existing \
    /opt/cetech/wp-content/ \
    "${WP_ROOT}/wp-content/"
fi

chown -R www-data:www-data \
  "${WP_ROOT}/wp-content/uploads" \
  "${WP_ROOT}/wp-content/cache" \
  "${WP_ROOT}/wp-content/upgrade"

find "${WP_ROOT}/wp-content/uploads" -type d -exec chmod 0755 {} +
find "${WP_ROOT}/wp-content/uploads" -type f -exec chmod 0644 {} +

if [[ "${1:-}" == "php-fpm" ]]; then
  exec "$@"
fi

exec "$@"
