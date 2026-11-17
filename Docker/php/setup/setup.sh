#!/usr/bin/env bash
# Installs WordPress and seeds the Coalition skill-test content. Idempotent.
set -euo pipefail
cd /var/www/html

: "${WP_URL:?}" "${MYSQL_ROOT_PASSWORD:?}" "${WP_ADMIN_USER:?}" "${WP_ADMIN_PASSWORD:?}" "${WP_ADMIN_EMAIL:?}"
: "${WORDPRESS_DB_USER:?}" "${DB_TESTS_NAME:?}"

# WP-CLI's vendor code still triggers PHP 8.5 deprecations; hide only those (E_ALL & ~E_DEPRECATED).
wp() { php -d error_reporting=24575 /usr/local/bin/wp "$@"; }

# The php service entrypoint copies WordPress core + wp-config.php into the shared volume.
for _ in $(seq 1 60); do
	[ -f wp-config.php ] && [ -f wp-includes/version.php ] && break
	sleep 2
done
[ -f wp-config.php ] || { echo "setup: wp-config.php never appeared" >&2; exit 1; }

# Separate database for the theme PHPUnit suite (wiped on every test run).
# MYSQL_PWD keeps the root password off the process list.
MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -h mysql -uroot --skip-ssl \
	-e "CREATE DATABASE IF NOT EXISTS \`${DB_TESTS_NAME}\`; GRANT ALL ON \`${DB_TESTS_NAME}\`.* TO '${WORDPRESS_DB_USER}'@'%';"

if ! wp core is-installed 2>/dev/null; then
	wp core install \
		--url="$WP_URL" \
		--title="Coalition Skills Test" \
		--admin_user="$WP_ADMIN_USER" \
		--admin_password="$WP_ADMIN_PASSWORD" \
		--admin_email="$WP_ADMIN_EMAIL" \
		--skip-email
fi

# Keep core on the latest release: the image's WordPress copy only lands in an empty volume.
wp core update
wp core update-db

wp option update siteurl "$WP_URL"
wp option update home "$WP_URL"
wp rewrite structure '/%postname%/' --hard
wp plugin is-installed contact-form-7 || wp plugin install contact-form-7
wp plugin activate contact-form-7
wp theme activate ct-custom
wp plugin delete hello akismet 2>/dev/null || true

wp eval-file /usr/local/share/ct-setup/seed.php --user="$WP_ADMIN_USER"
echo "setup: done -> $WP_URL (admin user: $WP_ADMIN_USER)"
