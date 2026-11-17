<?php
/**
 * CT Custom bootstrap: constants, includes and module start-up.
 *
 * @package CtCustom
 */

defined( 'ABSPATH' ) || exit;

const CT_CUSTOM_VERSION  = '1.0.0';
const CT_CUSTOM_PATH     = __DIR__ . DIRECTORY_SEPARATOR;
const CT_CUSTOM_CLASSES  = CT_CUSTOM_PATH . 'includes' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
const CT_CUSTOM_BUILD    = CT_CUSTOM_PATH . 'build' . DIRECTORY_SEPARATOR;
const CT_CUSTOM_TEXT_DOM = 'ct-custom';

require_once CT_CUSTOM_CLASSES . 'class-theme.php';
require_once CT_CUSTOM_CLASSES . 'class-setup.php';
require_once CT_CUSTOM_CLASSES . 'class-assets.php';
require_once CT_CUSTOM_CLASSES . 'class-icons.php';
require_once CT_CUSTOM_CLASSES . 'class-settings.php';
require_once CT_CUSTOM_CLASSES . 'class-settings-page.php';
require_once CT_CUSTOM_CLASSES . 'class-mailer.php';
require_once CT_CUSTOM_CLASSES . 'class-fonts.php';
require_once CT_CUSTOM_CLASSES . 'class-customizer-schema.php';
require_once CT_CUSTOM_CLASSES . 'class-customizer-css.php';
require_once CT_CUSTOM_CLASSES . 'class-customizer.php';
require_once CT_CUSTOM_CLASSES . 'class-menu.php';
require_once CT_CUSTOM_CLASSES . 'class-breadcrumbs.php';
require_once CT_CUSTOM_CLASSES . 'class-blocks.php';

// Underscores template helpers used by the blog templates (single, archive, search).
require_once CT_CUSTOM_PATH . 'inc/template-tags.php';
require_once CT_CUSTOM_PATH . 'inc/template-functions.php';

CtCustom\Theme::start();
