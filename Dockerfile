FROM php:8.4-fpm
ENV CI_ENV=docker

COPY --from=ghcr.io/mlocati/php-extension-installer:2.10.1 /usr/bin/install-php-extensions /usr/local/bin/

# Install dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        cron \
        curl \
        libmemcachedutil2t64 \
    && install-php-extensions \
        mysqli \
        zip \
        redis \
        memcached \
        apcu \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enabling custom upload settings in PHP
RUN printf "file_uploads = On\n\
memory_limit = 256M\n\
upload_max_filesize = 64M\n\
post_max_size = 64M\n\
max_execution_time = 600\n" > $PHP_INI_DIR/conf.d/wavelog.ini

# Copy web server and process manager configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/wavelog.conf
COPY docker/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

# Copy proper file to target image
COPY ./ /var/www/html/
WORKDIR /var/www/html

# Setting permissions as: https://docs.wavelog.org/getting-started/installation/linux/#3-set-directory-ownership-and-permissions
RUN mkdir ./application/config/docker; \
    mv ./htaccess.sample ./.htaccess; \
    sed -i "s/\$config\['index_page'\] = 'index.php';/\$config\['index_page'\] = '';/g" ./install/config/config.php; \
    chown -R root:www-data /var/www/html; \
    chmod -R g+rw ./application/cache/; \
    chmod -R g+rw ./application/config/; \
    chmod -R g+rw ./application/logs/; \
    chmod -R g+rw ./assets/; \
    chmod -R g+rw ./backup/; \
    chmod -R g+rw ./updates/; \
    chmod -R g+rw ./uploads/; \
    chmod -R g+rw ./userdata/; \
    chmod -R g+rw ./images/eqsl_card_images/; \
    chmod -R g+rw ./install/;

# Create the cron job
# /etc/cron.d/ format requires a username field (www-data)
RUN printf "* * * * * www-data curl --silent http://localhost/index.php/cron/run >/dev/null 2>&1\n" \
    > /etc/cron.d/wavelog \
    && chmod 0644 /etc/cron.d/wavelog

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
