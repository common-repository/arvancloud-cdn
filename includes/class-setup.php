<?php
namespace WP_Arvan;

use WP_Arvan\Setup_Admin;
use WP_Arvan\CDN\CDN_Options;
use WP_Arvan\CDN\Purge_Cache;
use WP_Arvan\CDN\Acceleration;
use WP_Arvan\CDN\Cache\Caching_Settings;
use WP_Arvan\CDN\Cache\Page_Rules;
use WP_Arvan\CDN\Auto_Add_Domain;
use WP_Arvan\Security\DDoS_Protection;
use WP_Arvan\Security\HTTPS_Settings;
use WP_Arvan\Security\Firewall;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://khorshidlab.com
 * @since      0.0.1
 *
 * @package    WP_Arvancloud_CDN
 * @subpackage WP_Arvancloud_CDN/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    WP_Arvancloud_CDN
 * @subpackage WP_Arvancloud_CDN/includes
 * @author     Khorshid <info@khorshidlab.com>
 */
class Setup {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      WP_Arvancloud_CDN_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 *
	 * @since    0.0.1
	 */
	public function __construct() {
		$this->version 		= defined( 'WP_ARVANCLOUD_CDN_VERSION' ) ? WP_ARVANCLOUD_CDN_VERSION : '0.0.1';
		$this->plugin_name 	= 'arvancloud-cdn';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();


	}

	public static function plugin_activation() {
		if (Setup_Admin::is_plugin_setup_done()) {
			Page_Rules::setup_recommended_rules();
		}

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Arvancloud_CDN_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Arvancloud_CDN_Admin. Defines all hooks for the admin area.
	 * - WP_Arvancloud_CDN_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-setup-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/API/HTTP/class-request-arvan.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/API/Key/class-api-key.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/API/Key/class-encryption.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/CDN/class-cdn-options.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/CDN/class-domain-info.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/CDN/class-purge-cache.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/CDN/class-reports.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/CDN/class-acceleration.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/CDN/Cache/class-caching-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/CDN/Cache/class-page-rules.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/Security/class-ddos_protection.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/Security/class-https-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/Security/class-firewall.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/CDN/class-domain-auto-add.php';

		$this->loader = new Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the load_plugin_textdomain function in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {

		$this->loader->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {


		$plugin_admin 	= new Setup_Admin( $this->plugin_name, $this->version );
		$purge_cache 	= new Purge_Cache();
		$cdn_options 	= new CDN_Options();
		$acceleration 	= new Acceleration();
		$DDoS_Protection= new DDoS_Protection();
		$HTTPS_Settings = new HTTPS_Settings();
		$firewall 		= new firewall();
		$Cache_Settings = new Caching_Settings();
		$Page_Rules		= new Page_Rules();
        $auto_add_domain= new Auto_Add_Domain();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'setup_admin_menu' );
		$this->loader->add_action( 'admin_bar_menu', $plugin_admin, 'setup_adminbar', 500);
		$this->loader->add_action( 'init', $plugin_admin, 'ar_cdn_fired_on_init' );
        $this->loader->add_action( 'init', $auto_add_domain, 'request_from_arvan_to_add_domain' );


		$this->loader->add_action( 'wp_ajax_ar_cdn_options', $cdn_options, 'ajax_saving_options' );
		$this->loader->add_action( 'wp_ajax_ar_acceleration_options', $acceleration, 'ajax_saving_options' );
		$this->loader->add_action( 'wp_ajax_ar_cache_status', $Cache_Settings, 'ajax_saving_options' );
		$this->loader->add_action( 'init', $Cache_Settings, 'save_expiration_time_options' );
		$this->loader->add_action( 'wp_ajax_ar_ddos_protection_options', $DDoS_Protection, 'ajax_saving_options' );
		$this->loader->add_action( 'wp_ajax_ar_https_options', $HTTPS_Settings, 'ajax_saving_options' );
		$this->loader->add_action( 'wp_ajax_ar_firewall', $firewall, 'ajax_saving_options' );
		$this->loader->add_action( 'wp_ajax_ar_firewall_change_rules_priority', $firewall, 'change_rule_priority' );
		$this->loader->add_action( 'wp_ajax_ar_firewall_create_rule', $firewall, 'ajax_add_rules' );
		$this->loader->add_action( 'wp_ajax_ar_firewall_delete_rule', $firewall, 'ajax_delete_rules' );
		$this->loader->add_action( 'wp_ajax_ar_firewall_get_rule', $firewall, 'ajax_get_rule' );
		$this->loader->add_action( 'wp_ajax_ar_firewall_update_rule', $firewall, 'ajax_update_rules' );
		$this->loader->add_action( 'init', $purge_cache, 'purge_arvan_cache' );
		$this->loader->add_action( 'save_post', $purge_cache, 'purge_arvan_cache_onsave' );
		$this->loader->add_action( 'delete_post', $purge_cache, 'purge_arvan_cache_onsave' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    WP_Arvancloud_CDN_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.0.1
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'arvancloud-cdn',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
