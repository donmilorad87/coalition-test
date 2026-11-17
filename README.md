# Coalition WordPress skill test — CT Custom

A responsive, pixel-perfect WordPress theme built on a fully dockerized stack: PHP 8.5-FPM with the latest
WordPress, MySQL 26.10 and nginx 1.31.

- **Admin-managed content:** logo, contact details, social links and SMTP email live in a custom
  **Theme Settings** page.
- **Customizer:** every colour, font and size is adjustable, with live preview.
- **Navigation:** a three-level, keyboard-accessible menu.
- **Gutenberg blocks:** Reach Us and Social Links, built with the Interactivity API.
- **Fonts:** come from the native WordPress Font Library.
- **Contact form:** Contact Form 7, sending through SMTP.

Everything is seeded automatically with WP-CLI, and the code is covered by PHPUnit and Playwright tests. It
passes WordPress and WordPress VIP coding standards.

## 1. Docker infrastructure

```
docker-compose.yml          wires the services below; every credential is read from .env
.env.example                template for .env (copy it, fill in real values)
Docker/
  php/Dockerfile            wordpress:php8.5-fpm (latest WordPress on PHP 8.5) + WP-CLI 2.12.0 (SHA-512 checked) + Composer 2
  php/php.ini               upload / memory limits
  php/setup/setup.sh        installer: WordPress, Contact Form 7, theme, latest core update
  php/setup/seed.php        demo content: pages, menu, form, Theme Settings, logo, fonts, SMTP
  php/setup/fonts/          Ubuntu + Bebas Neue woff2 files installed into the Font Library
  mysql/Dockerfile          mysql:26.10
  nginx/Dockerfile          nginx:1.31 + default.conf (PHP-FPM upstream, static caching, denies *.log and PHP in uploads)
```

| Service | Image | Purpose |
|---------|-------|---------|
| `mysql` | `mysql:26.10` | WordPress database (+ a separate test database for PHPUnit) |
| `php` | `wordpress:php8.5-fpm` | PHP-FPM running WordPress; the theme is bind-mounted from `./ct-custom` |
| `nginx` | `nginx:1.31` | Web server on port 80, bound to `BIND_IP_LOCAL` and `BIND_IP_LAN` |
| `setup` | same as `php` | One-shot WP-CLI installer/seeder. Runs on every `up` and is idempotent: it only fills in what is missing and never overwrites your edits |

Volumes: `db_data` (MySQL) and `wp_data` (WordPress core, plugins, uploads). The theme is **not** in a volume;
it is your working copy in `./ct-custom`.

> The image tag pins only the PHP line (8.5). The tag always contains the latest WordPress, and `setup`
> runs `wp core update` so existing volumes stay on the latest release too. The version-less
> `wordpress:latest` / `wordpress:fpm` tags ship an older PHP. MySQL and nginx are pinned so a rebuild never
> jumps a major version over the existing `db_data` volume.

## 2. Running it

**A real `.env` file is required.** `docker compose` refuses to start without one: each variable uses
`${VAR:?}`, so a missing value fails immediately with a clear message.

```bash
cp .env.example .env          # then edit: passwords, admin user, IPs, SMTP (MAIL_*)
docker compose up -d --build --pull always
docker compose logs -f setup  # wait for "setup: done"
```

Hosts file entry, pointing at the IP nginx listens on:

| Where you browse from | Hosts entry |
|-----------------------|-------------|
| This Linux machine | `127.0.0.6 coalitiontest.local` (`BIND_IP_LOCAL`) |
| Another machine on the LAN (e.g. Windows: `C:\Windows\System32\drivers\etc\hosts`) | `192.168.0.109 coalitiontest.local` (`BIND_IP_LAN`) |

Then open **http://coalitiontest.local** — admin at **/wp-admin** with `WP_ADMIN_USER` / `WP_ADMIN_PASSWORD`
from `.env` (skill-test values: `coalitiontest` / `wordpresstest`).

| Task | Command |
|------|---------|
| Re-run install/seed | `docker compose run --rm setup` |
| Stop | `docker compose down` |
| Reset everything (deletes DB + WordPress volume) | `docker compose down -v` |
| WP-CLI | `docker compose exec -u 33 php wp <command>` |
| Logs (PHP errors) | `docker compose exec php tail -f /var/www/html/wp-content/debug.log` |

### `.env` variables

| Variable | Used for |
|----------|----------|
| `WP_URL` | Site URL / home URL (`http://coalitiontest.local`) |
| `BIND_IP_LOCAL`, `BIND_IP_LAN` | IPs nginx (and MySQL on the local IP) bind to |
| `MYSQL_ROOT_PASSWORD`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` | Database |
| `DB_TESTS_NAME` | Throw-away database for the PHPUnit suite |
| `WP_ADMIN_USER`, `WP_ADMIN_PASSWORD`, `WP_ADMIN_EMAIL` | Admin account created on first install |
| `MAIL_*` | SMTP server, copied into Theme Settings → Email on first setup (e.g. Mailtrap sandbox) |

---

## 3. Theme structure (`ct-custom/`)

Classic theme (Underscores base) with a small class-per-module PHP layout. Each class registers its own
hooks in `start()`, and `functions.php` boots them all.

```
functions.php                 constants, require includes/classes/*, CtCustom\Theme::start()
homepage.php                  "Homepage" template (front page) → partials/page-layout.php
page.php, header.php, footer.php
partials/                     top-bar, site-header, breadcrumbs, page-layout, site-footer
patterns/contact-section.php  "Contact us" (Contact Form 7) + "Reach us" columns, auto-registered by core
includes/classes/
  class-theme.php             boots every module
  class-setup.php             theme supports, Primary menu location, logo → default site icon
  class-assets.php            loads build/ assets (dependency lists from *.asset.php), Customizer CSS variables
  class-fonts.php             families from the Font Library → Customizer choices + preload
  class-settings.php          Theme Settings option: defaults, get(), sanitizers
  class-settings-page.php     Appearance → Theme Settings screen (Settings API)
  class-mailer.php            SMTP for wp_mail() / Contact Form 7 + "send test email"
  class-customizer-schema.php every design token (section, type, default, limits) → --ct-* CSS variable
  class-customizer-css.php    :root { --ct-… } output, values re-validated when read
  class-customizer.php        Customizer panel, controls and live preview
  class-menu.php              3-level primary menu + accessible submenu toggles
  class-breadcrumbs.php       breadcrumb trail + "Page header" overrides (display title, breadcrumb parent)
  class-blocks.php            registers build/blocks/*, block category, block styles
  class-icons.php             inline SVG icons
src/                          sources built by @wordpress/scripts into build/ (build/ is committed)
  front.js, scripts/          navigation (ES6 class), clipboard helper
  styles/                     SCSS (base/, ui/), using only var(--ct-*) values
  blocks/reach-us/            block.json · edit.js · render.php · view.js · style.scss
  blocks/social-links/        block.json · edit.js · items-control.js · render.php · view.js · style.scss
  editor/                     "Page header" panel in the page sidebar
  admin/                      Theme Settings media pickers + social links repeater
  customizer/                 live-preview script
tests/phpunit/                WordPress test suite (settings, mailer, fonts, customizer CSS, breadcrumbs, blocks)
tests/e2e/                    Playwright (desktop 1600px + mobile): menu, blocks, contact form, layout
```

---

## 4. Managing the site in WordPress

### Appearance → Theme Settings
Built with the Settings API, no plugin.
- **Logo** (media upload). It is also the default browser **favicon** until a Site Icon is set in
  *Customize → Site Identity*.
- **Contact information**: company name, address, phone and fax numbers.
- **Social media links**: repeatable items, each with an image, name and URL. Facebook, Twitter, LinkedIn and
  Pinterest get a built-in icon when no image is set.
- **Email (SMTP)**: host, port, encryption, username, password (never shown again after saving), and from
  name/address. A **Send test email** button checks the connection. Contact Form 7 sends through this server.

### Appearance → Menus
The **Primary** location supports 3 levels (menu → sub menu → sub-sub menu):
- **Desktop:** opens on hover and on keyboard focus; Escape closes it.
- **Mobile:** a hamburger with expandable sections.

The current page's branch is bold and underlined.

### Appearance → Fonts
Fonts come from WordPress's native **Font Library**; the theme bundles no font files. Ubuntu and Bebas Neue
are installed and active. Install or upload more fonts here, then choose them in
*Customize → Theme design → Typography*.

### Gutenberg (page content)
All page content is edited in the block editor.
- **Reach Us** block: company, address, phone and fax. Empty fields fall back to Theme Settings. It has
  "copy" buttons, built with the Interactivity API.
- **Social Links** block: items added one by one (image, name, URL). With no items it shows the Theme
  Settings links; "Start from Theme Settings" copies them into the block. It has an optional "share this
  page" button, also built with the Interactivity API.
- **Contact section** pattern, the **Section title (bar)** heading style and the **Intro (muted)**
  paragraph style.
- **Page header** panel in the page sidebar: displayed title and breadcrumb parent.

### Customize → Theme design (Customizer)
Every value previews live and is printed as a CSS variable, covering:
- global colours and container width
- fonts and font sizes (body, H1–H4)
- top bar and header/logo
- main menu, sub menu and sub-sub menu
- breadcrumbs and section borders
- form fields and buttons
- social icons
- footer, which can also be turned off

### Login & Register
The top bar shows **Login** and **Signup** (WordPress login/registration; registration is enabled on the first setup run).
Logged-in users see **Account** and **Logout** instead.

---

## 5. Development

```bash
cd ct-custom
npm install
npm run build                 # or: npm start (watch)
npm run lint:js               # ESLint (@wordpress/eslint-plugin)
npm run lint:css              # Stylelint (@wordpress/stylelint-config)
npm run test:e2e              # Playwright
npm run design-diff           # screenshot in the PSD's "menu open" state for pixel comparison

# PHP tools run inside the php container
docker compose exec -u 1000 -w /var/www/html/wp-content/themes/ct-custom php composer install
docker compose exec -u 1000 -w /var/www/html/wp-content/themes/ct-custom php vendor/bin/phpunit
docker compose exec -u 1000 -w /var/www/html/wp-content/themes/ct-custom php vendor/bin/phpcs   # WordPress + WordPress VIP Go
```
