# syntax=docker/dockerfile:1.7

ARG PHP_IMAGE
ARG WP_CLI_IMAGE

FROM ${WP_CLI_IMAGE} AS wpcli

FROM ${PHP_IMAGE} AS runtime

ARG WORDPRESS_VERSION
ARG PHPREDIS_VERSION=6.3.0
ARG APP_DIR
ARG APP_SLUG
ARG INSTALL_DEV_TOOLS=0

ENV DEBIAN_FRONTEND=noninteractive \
    WORDPRESS_VERSION=${WORDPRESS_VERSION} \
    WP_CLI_ALLOW_ROOT=1 \
    PATH="/usr/local/bin:${PATH}" \
    WORDPRESS_DB_NAME=wordpress \
    WORDPRESS_DB_USER=wordpress \
    WORDPRESS_DB_PASSWORD=wordpress \
    WORDPRESS_DB_HOST=mariadb:3306 \
    WORDPRESS_AUTH_KEY=build-auth-key \
    WORDPRESS_SECURE_AUTH_KEY=build-secure-auth-key \
    WORDPRESS_LOGGED_IN_KEY=build-logged-in-key \
    WORDPRESS_NONCE_KEY=build-nonce-key \
    WORDPRESS_AUTH_SALT=build-auth-salt \
    WORDPRESS_SECURE_AUTH_SALT=build-secure-auth-salt \
    WORDPRESS_LOGGED_IN_SALT=build-logged-in-salt \
    WORDPRESS_NONCE_SALT=build-nonce-salt \
    WORDPRESS_HOME=http://localhost \
    WORDPRESS_SITEURL=http://localhost \
    WORDPRESS_VALKEY_HOST=127.0.0.1 \
    WORDPRESS_VALKEY_PORT=6379 \
    WORDPRESS_VALKEY_PASSWORD=build-valkey-password \
    WORDPRESS_VALKEY_PREFIX=build: \
    WORDPRESS_CACHE_KEY_SALT=build-cache-key-salt

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      ca-certificates \
      curl \
      default-mysql-client \
      ghostscript \
      git \
      imagemagick \
      less \
      libfcgi-bin \
      libavif-dev \
      libfreetype6-dev \
      libicu-dev \
      libjpeg62-turbo-dev \
      libmagickwand-dev \
      libonig-dev \
      libpng-dev \
      libwebp-dev \
      libxml2-dev \
      libzip-dev \
      mariadb-client \
      msmtp \
      netcat-openbsd \
      procps \
      rsync \
      unzip \
      zlib1g-dev; \
    docker-php-ext-configure gd \
      --with-freetype \
      --with-jpeg \
      --with-webp \
      --with-avif; \
    docker-php-ext-install -j"$(nproc)" \
      bcmath \
      exif \
      gd \
      intl \
      mysqli \
      opcache \
      pcntl \
      pdo_mysql \
      soap \
      sockets \
      zip; \
    pecl install "redis-${PHPREDIS_VERSION}"; \
    docker-php-ext-enable redis; \
    set +e; \
    pecl install imagick; \
    INSTALL_STATUS=$?; \
    set -e; \
    if [ "$INSTALL_STATUS" -ne 0 ]; then \
      if pecl list | awk 'NR>1 {print $1}' | grep -q '^imagick$'; then \
        echo 'imagick already installed, continuing'; \
      else \
        exit $INSTALL_STATUS; \
      fi; \
    fi; \
    docker-php-ext-enable imagick; \
    rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=wpcli /usr/local/bin/wp /usr/local/bin/wp

RUN set -eux; \
    rm -rf /usr/src/wordpress; \
    mkdir -p /usr/src/wordpress; \
    wp core download \
      --version="${WORDPRESS_VERSION}" \
      --path=/usr/src/wordpress \
      --locale=en_US \
      --skip-content \
      --force; \
    wp core verify-checksums \
      --version="${WORDPRESS_VERSION}" \
      --path=/usr/src/wordpress; \
    mkdir -p \
      /opt/cetech/wp-content \
      /var/run/php; \
    chown -R www-data:www-data \
      /usr/src/wordpress \
      /opt/cetech \
      /var/run/php

COPY docker/php/wp-config.php /opt/cetech/wp-config.php
COPY dependencies/ /opt/cetech/dependencies/
COPY docker/php/install-public-plugins.sh \
  /usr/local/bin/install-public-plugins

RUN set -eux; \
    cp /opt/cetech/wp-config.php /usr/src/wordpress/wp-config.php; \
    chmod 0755 /usr/local/bin/install-public-plugins; \
    /usr/local/bin/install-public-plugins \
      "${APP_SLUG}" \
      "${INSTALL_DEV_TOOLS}"

COPY docker/php/php.ini /usr/local/etc/php/conf.d/10-cetech.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/20-opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-cetech.conf
COPY docker/php/wp-config.php /opt/cetech/wp-config.php
COPY docker/php/entrypoint.sh /usr/local/bin/cetech-entrypoint
COPY apps/shared/wp-content/ /opt/cetech/wp-content/
COPY ${APP_DIR}/wp-content/ /opt/cetech/wp-content/
COPY docker/php/mailpit-sendmail /usr/local/bin/mailpit-sendmail

RUN set -eux; \
    find /usr/local/etc/php-fpm.d \
      -type f \
      -name '*.conf' \
      -exec sed -ri \
        '/^[[:space:]]*[#;]?[[:space:]]*listen\.allowed_clients([[:space:]]*=.*)?$/d' \
        '{}' +; \
    chmod 0755 \
      /usr/local/bin/cetech-entrypoint \
      /usr/local/bin/mailpit-sendmail; \
    find /opt/cetech/wp-content -type d -exec chmod 0755 {} +; \
    find /opt/cetech/wp-content -type f -exec chmod 0644 {} +; \
    chown -R www-data:www-data /opt/cetech/wp-content

WORKDIR /var/www/html

EXPOSE 9000

STOPSIGNAL SIGQUIT

HEALTHCHECK \
  --interval=15s \
  --timeout=5s \
  --start-period=30s \
  --retries=5 \
  CMD nc -z 127.0.0.1 9000

ENTRYPOINT ["cetech-entrypoint"]
CMD ["php-fpm", "-F"]