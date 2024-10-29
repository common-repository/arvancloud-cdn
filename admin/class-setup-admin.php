<?php
namespace WP_Arvan;

use WP_Arvan\API\Key\Api_Key;
use WP_Arvan\CDN\Cache\Page_Rules;
use WP_Arvan\CDN\Domain_Info;
use WP_Arvan\Helper;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://khorshidlab.com
 * @since      0.0.1
 *
 * @package    WP_Arvancloud_CDN
 * @subpackage WP_Arvancloud_CDN/admin
 * @author     Khorshid <info@khorshidlab.com>
 */
class Setup_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Arvancloud_CDN_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Arvancloud_CDN_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/wp-arvancloud-cdn-admin.css', array(), $this->version, 'all' );

		if ( is_rtl() ) {
			wp_enqueue_style( $this->plugin_name . '-rtl', plugin_dir_url( __FILE__ ) . 'assets/css/admin-rtl.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( 'toastrjs', plugin_dir_url( __FILE__ ) . 'assets/css/toastr.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'select2-css', plugin_dir_url( __FILE__ ) . 'assets/css/select2.min.css', array(), '4.1.0', 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Arvancloud_CDN_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Arvancloud_CDN_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( isset($_GET['page']) && substr( sanitize_text_field($_GET['page']), 0, 14 ) === ACCDN_SLUG) {
			wp_enqueue_script( 'ar-cdn', plugin_dir_url( __FILE__ ) . 'assets/js/wp-arvancloud-cdn-admin.js', array( 'jquery', 'toastrjs' ), $this->version, false );
			wp_enqueue_script( 'toastrjs', plugin_dir_url( __FILE__ ) . 'assets/js/toastr.js', array( 'jquery' ), '2.1.3', false );
	
			wp_enqueue_script('chartjs', plugin_dir_url( __FILE__ ) . 'assets/js/chart.min.js');
			wp_enqueue_script('arvan-reports', plugin_dir_url( __FILE__ ) . 'assets/js/reports.js');
			wp_enqueue_script('arvan-firewall', plugin_dir_url( __FILE__ ) . 'assets/js/firewall.js');
	
			wp_enqueue_script('select2', plugin_dir_url( __FILE__ ) . 'assets/js/select2.min.js', array( 'jquery' ), '4.1.0', false );
			wp_localize_script(
				'ar-cdn',
				'ar_cdn_ajax_object',
				[
					'ajax_url'  => admin_url( 'admin-ajax.php' ),
					'security'  => wp_create_nonce( 'ar-cdn-options-nonce' ),
					'is_rtl'=> is_rtl(),
					'strings'	  => [
						'wait'	=> __('Please Wait', 'arvancloud-cdn'),
						'sent'	=> __('Request sent.', 'arvancloud-cdn'),
						'updated' => __('Updated', 'arvancloud-cdn'),
						'failed' => __('Update failed', 'arvancloud-cdn'),
					],
				],
			);
			wp_localize_script(
				'arvan-firewall',
				'ar_cdn_ajax_object',
				[
					'ajax_url'  => admin_url( 'admin-ajax.php' ),
					'security'  => wp_create_nonce( 'ar-cdn-options-nonce' ),
					'is_rtl'=> is_rtl(),
					'strings'	  => [
						'wait'	=> __('Please Wait', 'arvancloud-cdn'),
						'sent'	=> __('Request sent.', 'arvancloud-cdn'),
						'updated' => __('Updated', 'arvancloud-cdn'),
						'failed' => __('Update failed', 'arvancloud-cdn'),
						'and'	=> __('+ AND', 'arvancloud-cdn'),
					],
					'rule_options' => [
						'ip.src' => [
							''	=> '',
							'==' => __('Equal', 'arvancloud-cdn'),
							'!=' => __('Not equal', 'arvancloud-cdn'),
							'in' => __('Is in a set of:', 'arvancloud-cdn'),
						],
						'ip.geoip.country' => [
							''	=> '',
							'==' => __('Equal', 'arvancloud-cdn'),
							'!=' => __('Not equal', 'arvancloud-cdn'),
							'in' => __('Is in a set of:', 'arvancloud-cdn'),
						],
						'http.request.uri.path' => [
							''	=> '',
							'==' => __('Equal', 'arvancloud-cdn'),
							'!=' => __('Not equal', 'arvancloud-cdn'),
							'contains' => __('Contains:', 'arvancloud-cdn'),
							'matches' => __('Matches regex', 'arvancloud-cdn'),
							'in' => __('Is in a set of:', 'arvancloud-cdn'),
							'starts_with' => __('Starts with', 'arvancloud-cdn'),
							'ends_with' => __('Ends with', 'arvancloud-cdn'),
						],
						'http.host' => [
							''	=> '',
							'==' => __('Equal', 'arvancloud-cdn'),
							'!=' => __('Not equal', 'arvancloud-cdn'),
							'contains' => __('Contains:', 'arvancloud-cdn'),
							'matches' => __('Matches regex', 'arvancloud-cdn'),
							'in' => __('Is in a set of:', 'arvancloud-cdn'),
							'starts_with' => __('Starts with', 'arvancloud-cdn'),
							'ends_with' => __('Ends with', 'arvancloud-cdn'),
						],
					],
					'rule_filter_type' => [
						'' => '',
						'ip.src' => __('IP Source Address', 'arvancloud-cdn'),
						'ip.geoip.country' => __('Country', 'arvancloud-cdn'),
						'http.request.uri.path' => __('URI Path', 'arvancloud-cdn'),
						'http.host' => __('Hostname', 'arvancloud-cdn'),
					],
					'rule_labels' => [
						__('Parameter', 'arvancloud-cdn'),
						__('Operator', 'arvancloud-cdn'),
						__('Value', 'arvancloud-cdn'),
					],
					'list_of_countries' => Helper::all_countries(),
				],
			);
		}
	}


	/**
	 * setup_admin_menu
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function setup_admin_menu() {

		add_menu_page( 
			__( ACCDN_NAME, 'arvancloud-cdn' ), 
			__( ACCDN_NAME, 'arvancloud-cdn'), 
			'manage_options', 
			ACCDN_SLUG, 
			__CLASS__ . '::settings_page',
			ACCDN_PLUGIN_ROOT_URL . 'admin/assets/img/arvancloud-logo.svg'
		);
		add_submenu_page(
			ACCDN_SLUG,
			__( 'CDN General Settings', 'arvancloud-cdn' ),
			__( 'CDN General Settings', 'arvancloud-cdn' ),
			'manage_options',
			ACCDN_SLUG,
			__CLASS__ . '::settings_page',
		);


		if (self::is_plugin_setup_done()) {


			add_submenu_page(
				ACCDN_SLUG,
				__( 'Web Acceleration', 'arvancloud-cdn' ),
				__( 'Web Acceleration', 'arvancloud-cdn' ),
				'manage_options',
				ACCDN_SLUG . '-acceleration',
				__CLASS__ . '::acceleration_page',
			);

			add_submenu_page(
				ACCDN_SLUG,
				__( 'DDoS Protection', 'arvancloud-cdn' ),
				__( 'DDoS Protection', 'arvancloud-cdn' ),
				'manage_options',
				ACCDN_SLUG . '-ddos_protection',
				__CLASS__ . '::ddos_protection_page',
			);


			add_submenu_page(
				ACCDN_SLUG,
				__( 'HTTPS Settings', 'arvancloud-cdn' ),
				__( 'HTTPS Settings', 'arvancloud-cdn' ),
				'manage_options',
				ACCDN_SLUG . '-https',
				__CLASS__ . '::https_settings_page',
			);

			add_submenu_page(
				ACCDN_SLUG,
				__( 'Firewall', 'arvancloud-cdn' ),
				__( 'Firewall', 'arvancloud-cdn' ),
				'manage_options',
				ACCDN_SLUG . '-firewall',
				__CLASS__ . '::firewall_settings_page',
			);

			add_submenu_page(
				ACCDN_SLUG,
				__( 'Cache Level', 'arvancloud-cdn' ),
				__( 'Caching Settings', 'arvancloud-cdn' ),
				'manage_options',
				ACCDN_SLUG . '-caching',
				__CLASS__ . '::caching_settings_page',
			);


			add_submenu_page(
				ACCDN_SLUG,
				__( 'CDN Reports', 'arvancloud-cdn' ),
				__( 'CDN Reports', 'arvancloud-cdn' ),
				'manage_options',
				ACCDN_SLUG . '-reports',
				__CLASS__ . '::reports_page',
			);

			add_submenu_page(
				ACCDN_SLUG,
				__( 'Status Analysis', 'arvancloud-cdn' ),
				__( 'Status Analysis', 'arvancloud-cdn' ),
				'manage_options',
				ACCDN_SLUG . '-status-reports',
				__CLASS__ . '::reports_status_page',
			);

		}


		add_submenu_page(
			ACCDN_SLUG,
			__( 'About ArvanCloud', 'arvancloud-cdn' ),
			__( 'About', 'arvancloud-cdn' ),
			'manage_options',
			ACCDN_SLUG . '-about',
			__CLASS__ . '::about_us_page'
		);
	}

	public static function ar_cdn_fired_on_init() {
		Api_Key::set_accdn_api_key();
	}


	/**
	 * Add Purge cache to wp adminbar
	 *
	 * @param WP_Admin_Bar $admin_bar
	 * @return void
	 * @since 0.0.1
	 */
	public function setup_adminbar( \WP_Admin_Bar $admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) || !self::is_plugin_setup_done() ) {
			return;
		}
		new Domain_Info;
		$nonce_purge_cache = wp_create_nonce('arvan_purge_cache');
		$admin_bar->add_menu( array(
			'id'    => 'arccdn-purge-cache',
			'parent' => null,
			'group'  => null,
			'title' => __( 'Clear Cache', 'arvancloud-cdn' ),
			'href'  => admin_url( 'admin.php?page=' . ACCDN_SLUG . '&purge_arvan_cache=1' . '&_wpnonce=' . esc_attr( $nonce_purge_cache )),
			'meta' => [
				'title' => __( 'Clear Cache', 'arvancloud-cdn' ),
			]
		) );
	}

	/**
	 * settings_page
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public static function settings_page() {
		$action = isset($_GET['action']) ? $_GET['action'] : false;

		if (self::is_plugin_setup_done() && $action !== 'change-api-key') {
			self::cdn_options_page();
		} else {
			require_once( 'views/settings-display.php' );
		}

    }

	public static function reports_page() {
		require_once( 'views/reports/reports.php' );
    }

	public static function reports_status_page() {
		require_once( 'views/reports/reports_status.php' );
    }

	public static function ddos_protection_page() {
		require_once( 'views/security/ddos_protection.php' );
    }

	public static function acceleration_page() {
		require_once( 'views/acceleration.php' );
	}

	public static function https_settings_page() {
		require_once( 'views/security/https_settings.php' );
	}

	public static function firewall_settings_page() {
		require_once( 'views/security/firewall_settings.php' );
	}

	public static function caching_settings_page() {
		require_once( 'views/cache/caching_settings.php' );
	}
	
	/**
	 * about_us_page
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public static function about_us_page() {

		require_once( 'views/about-us-display.php' );

    }

	/**
	 * CDN Options page
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public static function cdn_options_page() {

		require_once( 'views/options-display.php' );

    }

	/**
	 * Check if plugin Setup is done and activated
	 *
	 * @return boolean
	 */
	public static function is_plugin_setup_done() {
		$status = get_option( 'arvan-cloud-cdn-status' );

		return $status === 'activated' ? true : false;
	}

	/**
	 * Print plugin status
	 *
	 * @return string
	 */
	public static function plugin_status() {
		
		$credentials_status = get_option( 'arvan-cloud-cdn-status' );
		switch ($credentials_status) {
			case 'activated':
				$status = '<span class="active">' . esc_html__( 'Active', 'arvancloud-cdn' ) . '</span>';
				break;
			case 'pending':
				$status = '<span class="pending">' . esc_html__( 'Pending', 'arvancloud-cdn' ) . '</span>';
				break;
			case 'pending_dns':
				$status = '<span class="pending">' . esc_html__( 'Pending DNS changes', 'arvancloud-cdn' ) . '</span>';
				break;
			case 'pending_cdn':
				$status = '<span class="pending">' . esc_html__( 'Pending CDN', 'arvancloud-cdn' ) . '</span>';
				break;
			default:
				$status = '<span class="disable">' . esc_html__( 'Disable', 'arvancloud-cdn' ) . '</span>';
				break;
		}

		return wp_kses_post($status);
				
	}

	public static function cdn_plan_level() {
		
		$cdn_level = Domain_Info::$plan_level;
		switch ($cdn_level) {
			case 1:
				$status = '<span class="active">' . esc_html__( 'Basic', 'arvancloud-cdn' ) . '</span>';
				break;
			case 2:
				$status = '<span class="active">' . esc_html__( 'Growth', 'arvancloud-cdn' ) . '</span>';
				break;
			case 3:
				$status = '<span class="active">' . esc_html__( 'Professional', 'arvancloud-cdn' ) . '</span>';
				break;
			case 4:
				$status = '<span class="active">' . esc_html__( 'Enterprise', 'arvancloud-cdn' ) . '</span>';
				break;
			default:
				$status = '';
				break;
		}

		return wp_kses_post($status);

	}

}
