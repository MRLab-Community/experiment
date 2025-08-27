<?php
/**
 * Plugin Name: WP Subscribe Pro
 * Plugin URI: http://mythemeshop.com/plugins/wp-subscribe-pro/
 * Description: WP Subscribe is a simple but powerful subscription plugin which supports MailChimp, Aweber, Feedburner, GetResponse, MailerLite, BenchmarkEmail and Constant Contact.
 * Version: 1.7.0
 * Author: MyThemeShop
 * Author URI: http://mythemeshop.com/
 * Text Domain: wp-subscribe
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if free plugin is active
 */

if ( function_exists( 'wp_subscribe_register_widget' ) ) :

	add_action( 'admin_notices', 'wps_plugin_deactivation_notice' );
	/**
	 * Echo deactivation notice if free plugin is active
	 * @return void
	 */
	function wps_plugin_deactivation_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please deactivate WP Subscribe plugin first to use the Premium features!', 'wp-subscribe' ) ?></p>
		</div>
		<?php
	}
endif;

/**
 * Include Base Class
 * From which all other classes are derived
 */
include_once 'includes/class-wps-base.php';

if ( ! class_exists('MTS_WP_Subscribe') ) :

	final class MTS_WP_Subscribe extends WPS_Base {

		/**
		 * Plugin Version
		 * @var string
		 */
		private $version = '1.7.0';

		/**
		 * Hold an instance of MTS_WP_Subscribe class
		 * @var MTS_WP_Subscribe
		 */
		protected static $instance = null;

		/**
		 * Hold WPS_Settings instance.
		 * @var WPS_Settings
		 */
		public $settings;

		/**
		 *  Hold an instance of MTS_ContentLocker class.
		 * @return MTS_WP_Subscribe
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new MTS_WP_Subscribe();
			}

			return self::$instance;
		}

		/**
		 * You cannot clone this class
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-subscribe' ), $this->version );
		}

		/**
		 * You cannot unserialize instances of this class
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-subscribe' ), $this->version );
		}

		/**
		 * The Constructor
		 */
		private function __construct() {

			// Include files.
			include_once 'includes/wps-helpers.php';
			include_once 'includes/wps-functions-options.php';
			include_once 'includes/wps-options.php';
			include_once 'includes/wps-widget.php';

			$this->settings	= new WPS_Settings;

			$this->autoloader();
			$this->hooks();
		}

		/**
		 * Register file autoloading mechanism
		 * @return void
		 */
		private function autoloader() {

			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Add hooks
		 * @return void
		 */
		private function hooks() {

			$this->add_action( 'init', 'load_textdomain' );

			// Admin
			if( is_admin() ) {

				$this->add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'plugin_action_links' );
				$this->add_action( 'add_meta_boxes', 'add_meta_boxes' );
				$this->add_action( 'save_post', 'save_post_meta' );
			}

			// output form
			$this->add_action( 'the_content', 'add_catch_element' );
			$this->add_action( 'the_content', 'single_post_form' );
			if ( $this->settings->get( 'enable_popup' ) ) {
				$this->add_action( 'wp_footer', 'add_subscribe_popup' );
			}

			// add shortcode
			add_shortcode( 'wp-subscribe', array( $this, 'shortcode_handler' ) );

			// AJAX
			$this->add_action( 'wp_ajax_wps_get_service_list', 'get_service_list' );
			$this->add_action( 'wp_ajax_validate_subscribe', 'validate_subscribe' );
			$this->add_action( 'wp_ajax_nopriv_validate_subscribe', 'validate_subscribe' );
			$this->add_action( 'wp_ajax_connect_aweber', 'connect_aweber' );
		}

		/**
		 * Autoload strategy
		 *
		 * @param  string $class
		 * @return void
		 */
		public function autoload( $class ) {

			if ( ! wps_str_start_with( 'WPS_', $class ) ) {
				return;
			}

			$path  = '';
			$class = strtolower( $class );
			$file  = 'class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
			$path  = $this->plugin_dir() . '/includes/';

			if ( wps_str_start_with( 'wps_subscription', $class ) ) {
				$path .= 'subscription/';
				$file  = str_replace( 'subscription-', '', $file );
			}

			// Load File
			$load = $path . $file;
			if ( $load && is_readable( $load ) ) {
				include_once $load;
			}
		}

		/**
		 * Load localization files
		 * @return void
		 */
		public function load_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-subscribe' );

			load_textdomain( 'wp-subscribe', WP_LANG_DIR . '/wp-subscribe/wp-subscribe-' . $locale . '.mo' );
			load_plugin_textdomain( 'wp-subscribe', false, $this->plugin_dir() . '/languages' );
		}

		/**
		 * Add settings link to plugin action links.
		 * @param  array $links
		 * @return array
		 */
		function plugin_action_links( $links ) {
			$settings_link = sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'options-general.php?page=wps-subscribe' ), esc_html_x( 'Settings', 'wp subscrib plugin action link', 'wp-subscribe') );
			array_unshift( $links, $settings_link );
			return $links;
		}

		/**
		 * Add subscribe popup
		 */
		function add_subscribe_popup() {

			$options = wps_get_options();

			// Short-circuit for display condition
			if ( has_filter( 'wp_subscribe_show_popup' ) ) {

				$filter_value = apply_filters( 'wp_subscribe_show_popup', null );

				// Do nothing now Let the plugin handle it
				if ( is_null( $filter_value ) ) {
				}

				// Don't show the popup
				elseif ( $filter_value == false ) {
					return;
				}

				// Show the popup
				else {
					wps_popup_html( $options );
					wps_popup_javascript();
					wps_enqueue_popup_css();
					wps_enqueue_popup_js();
					return;
				}
			}

			 // if cookie is not set
			if ( empty( $_COOKIE['wps_cookie_' . $options['cookie_hash'] ] ) && empty( $_SESSION['wps_cookie_' . $options['cookie_hash'] ] ) ) {

				// check if popup should be displayed on current page or not (global settings)
				$show_on = $options['popup_show_on'];
				if ( ( is_front_page() && ! $show_on['front_page'] ) ||
					 ( is_singular() && ! is_front_page() && ! $show_on['single'] ) ||
					 ( is_archive() && ! $show_on['archive'] ) ||
					 ( is_search() && ! $show_on['search'] ) ||
					 ( is_404() && ! $show_on['404_page'] )
				) {
					return;
				}

				// check if popup is excluded on individual post
				if ( is_singular() ) {
					if ( get_post_meta( get_the_ID(), '_wp_subscribe_disable_popup', true ) ) {
						return;
					}
				}

				wps_popup_html( $options );
				wps_popup_javascript();
				wps_enqueue_popup_css();
				wps_enqueue_popup_js();
			}
		}

		/**
		 * Add the catch element
		 * @param html $content
		 */
		function add_catch_element( $content ) {

			$options = wps_get_options();

			if ( $options['enable_popup'] && ! empty( $options['popup_triggers']['on_reach_bottom'] ) ) {
				if ( ( is_singular( 'post' ) || is_singular( 'page' ) ) &&
					 is_main_query() &&
					 in_the_loop() &&
					 ! ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) ) {
					$content .= '<div id="wp-subscribe-content-bottom"></div>';
				}
			}

			return $content;
		}

		/**
		 * Form on singular
		 * @param  html $content
		 * @return string
		 */
		function single_post_form( $content ) {

			$options = wps_get_options();

			if ( ! $options['enable_single_post_form'] ||
				 ! is_singular('post') ||
				 ! is_main_query() ||
				 ! in_the_loop() ||
				 function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
				return $content;
			}

			// Abort
			$disabled = get_post_meta( get_the_ID(), '_wp_subscribe_disable_single', true );
			if ( $disabled ) {
				return $content;
			}

			// Add before/after post content
			$position_classes = array(
				'top' => ' wp-subscribe-before-content',
				'bottom' => ' wp-subscribe-after-content',
			);
			$position = isset( $position_classes[$options['single_post_form_location']] ) ? $position_classes[$options['single_post_form_location']] : '';

			$options['before_widget'] = '<div class="wp-subscribe-single' . $position . '">';
			$options['popup_content'] = 'subscribe_form';

			ob_start();

				wps_popup_html( $options, 'single_post' );

			$widget = ob_get_clean();

			if ( 'top' == $options['single_post_form_location'] ) {
				$content = $widget . $content;
			}
			elseif ( 'bottom' == $options['single_post_form_location'] )  {
				$content = $content . $widget;
			}

			return $content;
		}

		/**
		 * Shortcode handler
		 * @return html
		 */
		public function shortcode_handler() {

			$options = wps_get_options();

			if( !$options['enable_single_post_form'] ) {
				return '';
			}

			$options['popup_content'] = 'subscribe_form';

			ob_start();

				wps_popup_html( $options, 'single_post' );

			return ob_get_clean();
		}

		public function connect_aweber() {

			// check for data
			$aweber_code = isset( $_REQUEST['aweber_code'] ) ? $_REQUEST['aweber_code'] : array();
			if ( empty( $aweber_code ) ) {
				wp_send_json( array(
					'success' => false,
					'error' => esc_html__( 'No aweber authorization code found.', 'wp-subscribe' ),
				) );
			}

			try {
				$service = new WPS_Subscription_Aweber();
				$data    = $service->connect( $aweber_code );

				wp_send_json(array(
					'success' => true,
					'data'    => $data
				));
			}
			catch( Exception $e ) {
				wp_send_json(array(
					'success' => false,
					'error'   => $e->getMessage()
				));
			}
		}

		/**
		 * Validate subscription
		 * @return void
		 */
		public function validate_subscribe() {

			// check for data
			$data = isset( $_POST['wps_data'] ) ? $_POST['wps_data'] : array();
			if ( empty( $data ) ) {
				wp_send_json( array(
					'success' => false,
					'error'   => esc_html__( 'No data found.', 'wp-subscribe' ),
				) );
			}

			// check for valid data
			if ( empty( $data['email'] ) ) {
				wp_send_json( array(
					'success' => false,
					'error'   => esc_html__( 'No email address found.', 'wp-subscribe' ),
				) );
			}

			if ( ! filter_var( $data['email'], FILTER_VALIDATE_EMAIL ) ) {
				wp_send_json( array(
					'success' => false,
					'error'   => esc_html__( 'Not a valid email address.', 'wp-subscribe' ),
				) );
			}

			// check for valid service
			$services = wps_get_mailing_services('options');
			if ( ! array_key_exists( $data['service'], $services ) ) {
				wp_send_json( array(
					'success' => false,
					'error'   => esc_html__( 'Unknown mailing service called.', 'wp-subscribe' ),
				) );
			}

			// Call service subscription method
			try {
				$service = wps_get_subscription_service( $data['service'] );

				$status = $service->subscribe( $data, $service->get_options( $data ) );

				wp_send_json(array(
					'success' => true,
					'status'  => $status['status'],
				));
			}
			catch( Exception $e ) {
				wp_send_json(array(
					'success' => false,
					'error'   => $e->getMessage(),
				));
			}
		}

		/**
		 * Get mailing lists according to service
		 *
		 * @return array
		 */
		public function get_service_list() {

			$name = $_REQUEST['service'];
			$args = $_REQUEST['args'];

			if ( empty( $name ) || empty( $args ) ) {
				wp_send_json(array(
					'success' => false,
					'error'   => esc_html__( 'Not permitted.', 'wp-subscribe' ),
				));
			}

			$service = wps_get_subscription_service( $name );

			if ( is_null( $service ) ) {
				wp_send_json(array(
					'success' => false,
					'error'   => esc_html__( 'Service not defined.', 'wp-subscribe' ),
				));
			}

			try {
				$args['raw'] = $args;

				$lists = call_user_func_array( array( $service, 'get_lists' ), $args );
			}
			catch( Exception $e ) {
				wp_send_json(array(
					'success' => false,
					'error'   => $e->getMessage(),
				));
			}

			if( empty( $lists ) ) {
				wp_send_json(array(
					'success' => false,
					'error'   => esc_html__( 'No lists found.', 'wp-subscribe' ),
				));
			}

			// Save for letter use
			update_option( 'mts_wps_'. $name . '_lists', $lists );

			wp_send_json(array(
				'success' => true,
				'lists' => $lists
			));
		}

		/**
		 * Register metaboxes
		 */
		public function add_meta_boxes() {
			$screens = get_post_types( array( 'public' => true ) );
			add_meta_box( 'wp_subscribe_metabox', esc_html__( 'WP Subscribe Pro', 'wp-subscribe'), array( $this, 'metabox_content' ), $screens, 'side', 'default' );
		}

		/**
		 * Metabox content
		 * @param  WP_Post $post
		 * @return void
		 */
		function metabox_content( $post ) {

			$post_type      = get_post_type( $post );
			$disable_popup  = get_post_meta( $post->ID, '_wp_subscribe_disable_popup', true );
			$disable_single = get_post_meta( $post->ID, '_wp_subscribe_disable_single', true );

			wp_nonce_field( 'wp_subscribe_metabox', 'wp_subscribe_metabox' );
			?>
			<p>
				<label for="wp_subscribe_disable_popup">
					<input type="hidden" name="wp_subscribe_disable_popup" value="0">
					<input type="checkbox" name="wp_subscribe_disable_popup" id="wp_subscribe_disable_popup" <?php checked( $disable_popup ) ?> value="1">
					<?php printf( esc_html__( 'Disable popup for this %s', 'wp-subscribe' ), $post_type ) ?>
				</label>
				<br>
				<?php if ($post_type == 'post') { ?>
				<label for="wp_subscribe_disable_single">
					<input type="hidden" name="wp_subscribe_disable_single" value="0">
					<input type="checkbox" name="wp_subscribe_disable_single" id="wp_subscribe_disable_single" <?php checked( $disable_single ) ?> value="1">
					<?php esc_html_e( 'Disable subscribe form before/after content', 'wp-subscribe' ) ?>
				</label><br>
				<?php } ?>
			</p>
			<?php
		}

		/**
		 * Save post meta
		 * @param  int $post_id
		 * @return void
		 */
		function save_post_meta( $post_id ) {

			// Check if our nonce is set.
			if ( ! isset( $_POST['wp_subscribe_metabox'] ) ) {
				return $post_id;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['wp_subscribe_metabox'], 'wp_subscribe_metabox' ) ) {
				return $post_id;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			// Check the user's permissions.
			if ( 'page' == $_POST['post_type'] ) {

				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}

			}
			else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return $post_id;
				}
			}

			// OK, its safe for us to save the data now.
			if ( isset( $_POST['wp_subscribe_disable_popup'] ) ) {
				update_post_meta( $post_id, '_wp_subscribe_disable_popup', (bool) $_POST['wp_subscribe_disable_popup'] );
			}
			if ( isset( $_POST['wp_subscribe_disable_single'] ) ) {
				update_post_meta( $post_id, '_wp_subscribe_disable_single', (bool) $_POST['wp_subscribe_disable_single'] );
			}
		}

		// Helper ------------------------------------------------------

		/**
		 * Get plugin directory
		 *
		 * @return string
		 */
		public function plugin_dir() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Get plugin uri
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Get plugin version
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

	}

	/**
	 * Main instance of MTS_WP_Subscribe
	 *
	 * Return the main instance of MTS_WP_Subscribe to prevent the need to use globals.
	 *
	 * @return MTS_WP_Subscribe
	 */
	function wps() {
		return MTS_WP_Subscribe::get_instance();
	}
	wps(); // Init it

endif;
