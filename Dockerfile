FROM php:8.5-fpm-trixie

# we always need this env var, otherwise wavelog can't work properly in a docker environment
ENV CI_ENV=docker

# prepare the php install helper
COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# system packages
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        cron \
        curl \
        nginx \
        supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# php extensions
RUN install-php-extensions \
        curl \
        mysqli \
        mbstring \
        xml \
        zip \
        redis \
        memcached \
        apcu \
        gd

# dedicated non-root user for files and cronjob
# (nginx and php-fpm masters keep running as root, their workers run as www-data)
RUN useradd --system --no-create-home --shell /usr/sbin/nologin --gid www-data --uid 999 wavelog

# server config
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
RUN echo "* * * * * wavelog /usr/bin/curl --silent http://localhost/index.php/cron/run >/dev/null 2>&1" > /etc/cron.d/wavelog \
    && chmod 0644 /etc/cron.d/wavelog

# application
WORKDIR /var/www/html
COPY --chown=wavelog:www-data ./ ./

# basic setup steps and permissions
RUN mkdir -p ./application/config/docker \
    && chown wavelog:www-data ./application/config/docker \
    && mv ./htaccess.sample ./.htaccess \
    && sed -i "s/\$config\['index_page'\] = 'index.php';/\$config\['index_page'\] = '';/g" ./install/config/config.php \
    && while read -r dir || [ -n "$dir" ]; do \
        case "$dir" in ''|'#'*) continue ;; esac; \
        chmod -R g+rw "./$dir"; \
    done < ./docker/writable-dirs

# Entrypoint
RUN mv ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
