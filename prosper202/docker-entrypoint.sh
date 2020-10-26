#!/bin/bash
set -e

if [ ! -f /usr/local/etc/php/php.ini ]; then
	cat <<EOF > /usr/local/etc/php/php.ini
date.timezone = "${PHP_INI_DATE_TIMEZONE}"
always_populate_raw_post_data = -1
memory_limit = ${PHP_MEMORY_LIMIT}
file_uploads = On
upload_max_filesize = ${PHP_MAX_UPLOAD}
post_max_size = ${PHP_MAX_UPLOAD}
max_execution_time = ${PHP_MAX_EXECUTION_TIME}
EOF
fi


if [ -n "$MYSQL_PORT_3306_TCP" ]; then
        if [ -z "$PROSPER202_DB_HOST" ]; then
                export PROSPER202_DB_HOST='mysql'
                if [ "$PROSPER202_DB_USER" = 'root' ] && [ -z "$PROSPER202_DB_PASSWORD" ]; then
                        export PROSPER202_DB_PASSWORD="$MYSQL_ENV_MYSQL_ROOT_PASSWORD"
                fi
        else
                echo >&2 "warning: both PROSPER202_DB_HOST and MYSQL_PORT_3306_TCP found"
                echo >&2 "  Connecting to PROSPER202_DB_HOST ($PROSPER202_DB_HOST)"
                echo >&2 "  instead of the linked mysql container"
        fi
fi



if [ -z "$PROSPER202_DB_HOST" ]; then
        echo >&2 "error: missing PROSPER202_DB_HOST and MYSQL_PORT_3306_TCP environment variables"
        echo >&2 "  Did you forget to --link some_mysql_container:mysql or set an external db"
        echo >&2 "  with -e PROSPER202_DB_HOST=hostname:port?"
        exit 1
fi


if [ -z "$PROSPER202_DB_PASSWORD" ]; then
        echo >&2 "error: missing required PROSPER202_DB_PASSWORD environment variable"
        echo >&2 "  Did you forget to -e PROSPER202_DB_PASSWORD=... ?"
        echo >&2
        echo >&2 "  (Also of interest might be PROSPER202_DB_USER and PROSPER202_DB_NAME.)"
        exit 1
fi

if ! [ -e index.php -a -e health/index.php ]; then
        echo >&2 "Prosper202 not found in $(pwd) - copying now..."

        if [ "$(ls -A)" ]; then
                echo >&2 "WARNING: $(pwd) is not empty - press Ctrl+C now if this is an error!"
                ( set -x; ls -A; sleep 10 )
        fi

        tar cf - --one-file-system -C /usr/src/prosper202 . | tar xf -

        echo >&2 "Complete! Prosper202 has been successfully copied to $(pwd)"
fi

# Ensure the MySQL Database is created
php /makedb.php "$PROSPER202_DB_HOST" "$PROSPER202_DB_USER" "$PROSPER202_DB_PASSWORD" "$PROSPER202_DB_NAME"

echo >&2 "========================================================================"
echo >&2
echo >&2 "This server is now configured to run Prosper202!"
echo >&2 "The following information will be prefilled into the installer (keep password field empty):"
echo >&2 "Host Name: $PROSPER202_DB_HOST"
echo >&2 "Database Name: $PROSPER202_DB_NAME"
echo >&2 "Database Username: $PROSPER202_DB_USER"
echo >&2 "Database Password: $PROSPER202_DB_PASSWORD"

# Write the database connection to the config so the installer prefills it
if ! [ -e 202-config/local.php ]; then
        php /makeconfig.php

        # Make sure our web user owns the config file if it exists
        chown www-data:www-data 202-config/local.php
        mkdir -p /var/www/html/health/logs
        chown www-data:www-data /var/www/html/health/logs
fi

"$@" &
MAINPID=$!

shut_down() {    
    kill -TERM $MAINPID || echo 'Main process not killed. Already gone.'
}
trap 'shut_down;' TERM INT

# wait until all processes end (wait returns 0 retcode)
while :; do
    if wait; then
        break
    fi
done
