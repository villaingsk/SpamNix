<?php
/**
 * Plugin Name: SpamNix
 * Plugin URI: https://krefstudio.com/spamnix
 * Description: Lightweight anti-spam protection for WordPress comments.
 * Version: 1.0.0
 * Author: Kref Studio
 * Author URI: https://krefstudio.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: spamnix
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) {
    exit;
}

define('SPAMNIX_VERSION', '1.0.0');
define('SPAMNIX_PATH', plugin_dir_path(__FILE__));
define('SPAMNIX_URL', plugin_dir_url(__FILE__));

require_once SPAMNIX_PATH . 'includes/helper-functions.php';
require_once SPAMNIX_PATH . 'includes/class-honeypot.php';
require_once SPAMNIX_PATH . 'includes/class-time-trap.php';
require_once SPAMNIX_PATH . 'includes/class-ip-rate-limit.php';
require_once SPAMNIX_PATH . 'includes/class-keyword-scanner.php';
require_once SPAMNIX_PATH . 'includes/class-email-filter.php';
require_once SPAMNIX_PATH . 'includes/class-core.php';
require_once SPAMNIX_PATH . 'admin/class-admin.php';

function spamnix_activate()
{
    if (! get_option('spamnix_settings')) {
        add_option('spamnix_settings', spamnix_default_settings());
    }

    if (! get_option('spamnix_stats')) {
        add_option('spamnix_stats', array('total_blocked' => 0));
    }
}
register_activation_hook(__FILE__, 'spamnix_activate');

function spamnix_boot()
{
    new SpamNix_Core();

    if (is_admin()) {
        new SpamNix_Admin();
    }
}
add_action('plugins_loaded', 'spamnix_boot');
