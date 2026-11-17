# Latest WordPress on PHP 8.5 FPM (the tag tracks WordPress releases; only the PHP line is pinned).
# The version-less `wordpress:fpm` / `latest` tags ship an older PHP.
FROM wordpress:php8.5-fpm

# WP-CLI pinned to a release and checked against its published SHA-512; bump both together.
ARG WP_CLI_VERSION=2.12.0
ARG WP_CLI_SHA512=be928f6b8ca1e8dfb9d2f4b75a13aa4aee0896f8a9a0a1c45cd5d2c98605e6172e6d014dda2e27f88c98befc16c040cbb2bd1bfa121510ea5cdf5f6a30fe8832

# WP-CLI for automated install/seed; composer for theme dev tooling (PHPUnit, PHPCS).
RUN curl -fsSL -o /usr/local/bin/wp "https://github.com/wp-cli/wp-cli/releases/download/v${WP_CLI_VERSION}/wp-cli-${WP_CLI_VERSION}.phar" \
	&& echo "${WP_CLI_SHA512}  /usr/local/bin/wp" | sha512sum -c - \
	&& chmod +x /usr/local/bin/wp \
	&& apt-get update && apt-get install -y --no-install-recommends unzip default-mysql-client \
	&& rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/zz-coalitiontest.ini
COPY setup/ /usr/local/share/ct-setup/
RUN chmod +x /usr/local/share/ct-setup/setup.sh
