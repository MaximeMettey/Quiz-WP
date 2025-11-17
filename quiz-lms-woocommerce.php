<?php
/**
 * Plugin Name: Quiz & LMS for WooCommerce
 * Plugin URI: https://github.com/MaximeMettey/Quiz-WP
 * Description: A comprehensive WordPress plugin for creating quizzes, courses, and LMS with WooCommerce integration for paid/free content
 * Version: 1.0.0
 * Author: Maxime Mettey
 * Author URI: https://github.com/MaximeMettey
 * Text Domain: quiz-lms-wc
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('QLMS_VERSION', '1.0.0');
define('QLMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QLMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QLMS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if WooCommerce is active
 */
function qlms_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'qlms_woocommerce_missing_notice');
        return false;
    }
    return true;
}

/**
 * Display notice if WooCommerce is not active
 */
function qlms_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e('Quiz & LMS for WooCommerce requires WooCommerce to be installed and active.', 'quiz-lms-wc'); ?></p>
    </div>
    <?php
}

/**
 * The code that runs during plugin activation
 */
function activate_qlms() {
    require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-activator.php';
    QLMS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation
 */
function deactivate_qlms() {
    require_once QLMS_PLUGIN_DIR . 'includes/class-qlms-deactivator.php';
    QLMS_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_qlms');
register_deactivation_hook(__FILE__, 'deactivate_qlms');

/**
 * The core plugin class
 */
require QLMS_PLUGIN_DIR . 'includes/class-qlms.php';

/**
 * Begins execution of the plugin
 */
function run_qlms() {
    $plugin = new QLMS();
    $plugin->run();
}

// Initialize the plugin
add_action('plugins_loaded', 'run_qlms');
