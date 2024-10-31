<?php
/**
 * Plugin Name: Remind me to change my password
 * Description: The plugin allows you to force users into changing their password after a certain time.
 * Version: 1.0
 * Author: Whodunit
 * Author URI: https://www.whodunit.fr/
 * License: GPL-2.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: remind-me-to-change-my-password
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

define( 'RMTCMP_DIR', __DIR__ );
define( 'RMTCMP_FILE', __FILE__ );
define( 'RMTCMP_PLUGIN_FILE_BASENAME', plugin_basename( __FILE__ ) );
define( 'RMTCMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RMTCMP_PLUGIN_URL_ASSETS', RMTCMP_PLUGIN_URL . 'assets/' );
define( 'RMTCMP_VERSION', '1.0.0' );

require RMTCMP_DIR . '/vendor/autoload.php';

RMTCMP\Init\Core::get_instance();
