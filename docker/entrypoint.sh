#!/bin/bash
set -e

# Ensure .htaccess exists. In dev env this might be missing when bind-mounting the source directory, but it's required for the rewrite rules to work.
[ -f /var/www/html/.htaccess ] || cp /var/www/html/htaccess.sample /var/www/html/.htaccess

if [ -f "/var/www/html/application/config/$CI_ENV/config.php" ] \
	&& [ -f "/var/www/html/application/config/$CI_ENV/database.php" ]; then
	echo "INFO: config.php and database.php found, lock the installer"
	touch /var/www/html/install/.lock
fi

# Set PHP Settings
PHP_MEMORY_LIMIT=${PHP_MEMORY_LIMIT:-256M}
PHP_UPLOAD_MAX_FILESIZE=${PHP_UPLOAD_MAX_FILESIZE:-64M}
PHP_POST_MAX_SIZE=${PHP_POST_MAX_SIZE:-64M}
PHP_MAX_EXECUTION_TIME=${PHP_MAX_EXECUTION_TIME:-600}

cat > "$PHP_INI_DIR/conf.d/wavelog.ini" <<EOF
file_uploads = On
memory_limit = ${PHP_MEMORY_LIMIT}
upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}
post_max_size = ${PHP_POST_MAX_SIZE}
max_execution_time = ${PHP_MAX_EXECUTION_TIME}
EOF

# Optionally remap the www-data user/group to a host-provided UID/GID so that
# bind-mount users (e.g. Unraid/NAS) can align file ownership with their host.
# When PUID/PGID are unset the image behaves exactly as before (www-data = 33:33,
# no remap and no chown run at all).
if [ -n "$PUID" ] || [ -n "$PGID" ]; then
	: "${PUID:=33}"
	: "${PGID:=33}"

	groupmod --non-unique --gid "$PGID" www-data
	usermod  --non-unique --uid "$PUID" www-data

	while read -r dir || [ -n "$dir" ]; do
		case "$dir" in ''|'#'*) continue ;; esac
		path="/var/www/html/$dir"
		if [ -d "$path" ] && { [ "$(stat -c '%u' "$path")" != "$PUID" ] || [ "$(stat -c '%g' "$path")" != "$PGID" ]; }; then
			chown -R www-data:www-data "$path"
		fi
	done < /var/www/html/docker/writable-dirs
fi

# WAVELOG_LOG_STDOUT sends the application log to the container stdout as NDJSON
# instead of application/logs/. The access log is switched to the same format,
# so stdout stays machine-readable as a whole. When the variable is unset, the
# application config decides (see $config['log_to_stdout']).
if [ -n "${WAVELOG_LOG_STDOUT:-}" ]; then
	case "$(echo "$WAVELOG_LOG_STDOUT" | tr '[:upper:]' '[:lower:]')" in
		1|true|yes|on) WAVELOG_LOG_STDOUT=true ;;
		*)             WAVELOG_LOG_STDOUT=false ;;
	esac
	export WAVELOG_LOG_STDOUT

	if [ "$WAVELOG_LOG_STDOUT" = "true" ]; then
		sed -i 's|/access.log combined|/access.log wavelog_json|' /etc/apache2/sites-enabled/000-default.conf
	fi
fi

# start cron daemon (runs as wavelog via /etc/cron.d/wavelog), unless DISABLE_CRON=true
if [ "${DISABLE_CRON:-false}" != "true" ]; then
	cron
fi

exec "$@"
