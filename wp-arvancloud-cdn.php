<?php

/**
 *
 * @link              https://khorshidlab.com
 * @since             0.0.1
 * @package           WP_Arvancloud_CDN
 *
 * @wordpress-plugin
 * Plugin Name:       ArvanCloud CDN
 * Plugin URI:        https://www.arvancloud.ir/fa/products/cdn
 * Description:       ArvanCloud CDN service caches your website content. Using this plugin, you will be able to purge and update the cached version, either manually or automatically, so that your users visit the latest version of your website at any time.
 * Version:           0.9.11
 * Requires PHP:      7.2
 * Author:            Khorshid, ArvanCloud
 * Author URI:        https://www.arvancloud.ir/en/products/cdn
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       arvancloud-cdn
 * Domain Path:       /languages
 */
use WP_Arvan\Setup;
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define( 'ACCDN_VERSION', '0.9.11' );
define( 'ACCDN_NAME', __( 'ArvanCloud CDN', 'arvancloud-cdn' ) );
define( 'ACCDN_SLUG', 'arvancloud-cdn');
define( 'ACCDN_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'ACCDN_PLUGIN_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'ACCDN_PLUGIN_ABSOLUTE', __FILE__ );
define( 'ACCDN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Currently plugin version.
 */
define( 'WP_ARVANCLOUD_CDN_VERSION', '0.9.9' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-setup.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
(new Setup())->run();

register_activation_hook( __FILE__ , array( new WP_Arvan\Setup, 'plugin_activation' ) );