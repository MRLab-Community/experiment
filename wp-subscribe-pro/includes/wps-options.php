<?php
/**
 * The setting page for plugin
 */

if ( ! defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

if( ! class_exists('WPS_Settings') ) :

	class WPS_Settings extends WPS_Base {

		/**
		 * Option key for updating and getting options from wp_options.
		 * @var string
		 */
		private $option_key = 'wp_subscribe_options';

		/**
		 * Hold Plugin Options
		 * @var array
		 */
		public $options;

		/**
		 * The Construct
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Hooks
		 * @return void
		 */
		public function hooks() {

			$this->setup_options();
			$this->add_action( 'admin_init', 'admin_init' );
			$this->add_action( 'admin_menu', 'add_options_page' );
			$this->add_filter( 'admin_body_class', 'body_class' );
			$this->add_action( 'admin_enqueue_scripts', 'enqueue_scripts' );

			// AJAX
			$this->add_action( 'wp_ajax_preview_popup', 'ajax_preview_popup' );
		}

		/**
		 * Setup option and if plugin loads first time setup defaults
		 * @return void
		 */
		public function setup_options() {

			$defaults = wps_get_option_defaults();
			$this->options = get_option( $this->option_key, array() );

		    if ( empty( $this->options ) ) {
		    	update_option( $this->option_key, $defaults );
		    }

			$this->options = wp_parse_args( $this->options, $defaults );
		}

		/**
		 * Init
		 * @return void
		 */
		function admin_init() {

			// Register setting
			register_setting( 'wp_subscribe-settings-group', $this->option_key );
		}

		/**
		 * Add setting page to menu
		 * @return void
		 */
		function add_options_page() {

			add_options_page(
				esc_html__( 'WP Subscribe', 'wp-subscribe' ),
				esc_html__( 'WP Subscribe Pro', 'wp-subscribe' ),
				'manage_options',
				'wps-subscribe',
				array( $this, 'display_page' )
			);
		}

		/**
		 * Add class to admin bosy
		 * @param  string $classes
		 * @return string
		 */
		public function body_class( $classes ) {

			$screen = get_current_screen();
			if( 'settings_page_wps-subscribe' !== $screen->base ) {
				return;
			}

			return $classes . 'wp-subscribe-admin-options';
		}

		/**
		 * Enqueue Scripts and Styles
		 * @return void
		 */
		public function enqueue_scripts() {

			$screen = get_current_screen();
			if( 'settings_page_wps-subscribe' !== $screen->base ) {
				return;
			}

			wp_enqueue_style( 'wp-subscribe-options', wps()->plugin_url() . '/assets/css/wp-subscribe-options.css' );
			wps_enqueue_popup_css();
			wps_enqueue_popup_js();
			wp_enqueue_style( 'jquery-ui-smoothness', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/smoothness/jquery-ui.css', false, null, false );

			// jQuery UI Slider
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-slider' );

			// WP Color Picker / Iris
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			wp_register_script( 'wp-subscribe-options', wps()->plugin_url() . '/assets/js/wp-subscribe-options.js', array( 'jquery', 'underscore' ) );

			wp_localize_script( 'wp-subscribe-options', 'wps_opts', array(
					'popup_removal_delay' => wps_get_popup_removal_delay()
			));
			wp_enqueue_script( 'wp-subscribe-options');
		}

		/**
		 * Display page content
		 * @return void
		 */
		public function display_page() {
			if ( !isset( $options['popup_form_options']['thanks_page'] ) ) {
				$options['popup_form_options']['thanks_page'] = '0';
			}

			if ( !isset( $options['single_post_form_options']['thanks_page'] ) ) {
				$options['single_post_form_options']['thanks_page'] = '0';
			}

			include_once wps()->plugin_dir() . '/views/settings.php';
		}

		/**
		 * Generate popup preview
		 * @return void
		 */
		function ajax_preview_popup() {

			$options = $_POST['wp_subscribe_options'];
			$content = $options['popup_content'];
			$options['preview'] = 1;

			if ( 'custom_html' == $content ) {
				echo do_shortcode( stripslashes( $options['popup_custom_html'] ) );
			}
			elseif ( 'subscribe_form' == $content ) {

				wps_popup_html( $options );

			}
			elseif ( 'posts' == $content ) {
				echo wps_get_related_posts( $options );
			}

			exit;
		}

		// --------------------- FIELD HELPERS ------------------------------

		/**
		 * Render text field
		 */
		function field_text( $args = array(), $group = 'popup_form_labels' ) {

			extract( $args );

			$new_id = 'wp_subscribe_options_labels' . ( $group ? '_' . $group : '' ) . "_{$id}";
			?>
			<div class="wp-subscribe-label-field <?php echo $group ? '_' . $group : '' ?>_<?php echo $id ?>-wrapper">

				<label for="<?php echo $new_id ?>">
					<?php echo esc_html($title) ?>
				</label>

				<div class="wps-input-wrapper">
					<?php wps_field_text(array(
						'id'	=> $new_id,
						'name'	=> 'wp_subscribe_options'. ( $group ? "[{$group}]" : '' ) . "[{$id}]",
						'value'	=> $value,
						'data_id' => $id
					)) ?>

					<?php if( isset( $link ) ) {
						printf( ' <a target="_blank" href="%s" class="button">%s</a>', esc_url( $link ), esc_html__( 'Click here', 'wp-subscribe' ) );
					} ?>

					<?php if( isset( $desc ) ) {
						printf( '<span class="wps-desc">%s</span>', wp_kses_post( $desc ) );
					} ?>

				</div>

			</div>
			<?php
		}

		/**
		 * Render text field
		 */
		function field_textarea( $args = array(), $group = 'popup_form_labels' ) {

			extract( $args );

			$new_id = 'wp_subscribe_options_labels' . ( $group ? '_' . $group : '' ) . "_{$id}";
			?>
			<div class="wp-subscribe-label-field <?php echo $group ? '_' . $group : '' ?>_<?php echo $id ?>-wrapper">

				<label for="<?php echo $new_id ?>">
					<?php echo esc_html($title) ?>
				</label>

				<div class="wps-input-wrapper">

					<?php wps_field_editor(array(
						'id'	=> $new_id,
						'name'	=> 'wp_subscribe_options'. ( $group ? "[{$group}]" : '' ) . "[{$id}]",
						'value'	=> $value,
						'data_id' => $id
					)) ?>

					<?php if( isset( $link ) ) {
						printf( ' <a target="_blank" href="%s" class="button">%s</a>', esc_url( $link ), esc_html__( 'Click here', 'wp-subscribe' ) );
					} ?>

					<?php if( isset( $desc ) ) {
						printf( '<span class="wps-desc">%s</span>', wp_kses_post( $desc ) );
					} ?>

				</div>

			</div>
			<?php
		}

		/**
		 * Render hidden field
		 */
		function field_hidden( $args = array(), $group = 'popup_form_labels' ) {

			extract( $args );

			$new_id = 'wp_subscribe_options_labels' . ( $group ? '_' . $group : '' ) . "_{$id}";

			wps_field_hidden(array(
				'id'	=> $new_id,
				'name'	=> 'wp_subscribe_options'. ( $group ? "[{$group}]" : '' ) . "[{$id}]",
				'value'	=> $value,
				'data_id' => $id
			));
		}

		function field_raw( $args = array(), $group = 'popup_form_labels' ) {

			call_user_func_array( $args['content'], array( $args['value'] ) );
		}

		/**
		 * Render checkbox field
		 */
		function field_checkbox( $args = array(), $group = 'popup_form_labels' ) {

			extract( $args );

			$new_id = 'wp_subscribe_options_labels' . ( $group ? '_' . $group : '' ) . "_{$id}";
			$name = 'wp_subscribe_options'. ( $group ? "[{$group}]" : '' ) . "[{$id}]";

			?>
			<div class="<?php echo $group ? '_' . $group : '' ?>_<?php echo $id ?>-wrapper">

				<label for="<?php echo $new_id ?>">

					<input type="hidden" name="<?php echo $name ?>" value="0" data-id="<?php echo $id ?>">

					<input type="checkbox" id="<?php echo $new_id ?>" name="<?php echo $name ?>" value="1"<?php checked( $value ) ?> data-id="<?php echo $id ?>">

					<?php echo esc_html($title) ?>

				</label>

			</div>
			<?php
		}

		/**
		 * Render select field
		 */
		function field_select( $args = array(), $group = 'popup_form_labels' ) {

			$options = array();
			extract( $args );

			$new_id = 'wp_subscribe_options_labels' . ( $group ? '_' . $group : '' ) . "_{$id}";
			?>
			<div class="wp-subscribe-label-field <?php echo $group ? '_' . $group : '' ?>_<?php echo $id ?>-wrapper">

				<label for="<?php echo $new_id ?>">
					<?php echo esc_html($title) ?>
				</label>

				<div class="wps-input-wrapper">
					<?php wps_field_select(array(
						'id'	=> $new_id,
						'name'	=> 'wp_subscribe_options'. ( $group ? "[{$group}]" : '' ) . "[{$id}]",
						'value'	=> $value,
						'options' => $options,
						'class' => 'widefat list-selectbox'
					)) ?>

					<?php if( isset( $is_list ) && $is_list ) {
						printf( ' <button class="button wps-get-list">%s</button>', esc_html__( 'Get list', 'wp-subscribe' ) );
					} ?>

					<?php if( isset( $link ) ) {
						printf( ' <a target="_blank" href="%s" class="button">%s</a>', esc_url( $link ), esc_html__( 'Click here', 'wp-subscribe' ) );
					} ?>

					<?php if( isset( $desc ) ) {
						printf( '<span class="wps-desc">%s</span>', wp_kses_post( $desc ) );
					} ?>

				</div>

			</div>
			<?php
		}

		/**
		 * Render color field
		 */
		function field_color( $id, $title, $group = 'popup_form_colors' ) {

			$value = '';
			if( $group ) {
				$group_options = $this->get( $group );
				$value = empty( $group_options[$id] ) ? '' : $group_options[$id];
			}
			else {
				$value = $this->get($id);
			}
			?>

			<div class="wp-subscribe-color-field">

				<label for="<?php echo "wp_subscribe_options_colors_{$id}" ?>">
		            <?php echo esc_html($title) ?>
		        </label>

				<?php wps_field_text(array(
					'class'	=> 'wp-subscribe-color-select',
					'id'	=> 'wp_subscribe_options_colors' . ( $group ? '_' . $group : '' ) . "_{$id}",
					'name'	=> 'wp_subscribe_options'. ( $group ? "[{$group}]" : '' ) . "[{$id}]",
					'value'	=> $value
				)) ?>

		    </div>

			<?php
		}

		// --------------------- HELPERS ------------------------------

		/**
		 * Render color palette field
		 */
		public function color_palettes_select( $target ) {

			$palettes = wps_get_default_color_palettes();

	        if ( empty( $palettes ) ) {
				return;
			}
	        ?>
	        <div class="wps-colors-loader">

				<a href="#" class="wps-toggle-palettes"><?php esc_html_e( 'Load a predefined color set', 'wp-subscribe' ) ?></a>

	            <div class="wps-palettes">
	                <?php foreach ( $palettes as $palette ) { ?>
	                <div class="single-palette">
	                	<table class="color-palette">
	                    	<tbody>
	                        	<tr>
	                            	<?php
										$hiddens = '';
										foreach ( $palette['colors'] as $field => $color ) {
											$hiddens .= sprintf( '<input type="hidden" class="wps-palette-color" name="%1$s" value="%2$s">', "{$target}_{$field}_color" , esc_attr( $color ) );
									?>
										<td style="background-color: <?php echo esc_attr( $color ) ?>">&nbsp;</td>
									<?php } ?>
	                        	</tr>
	                    	</tbody>
	                	</table>

						<?php echo $hiddens ?>

						<a href="#" class="button button-secondary wps-load-palette"><?php esc_html_e( 'Load colors', 'wp-subscribe' ) ?></a>

					</div>
					<?php } ?>
	            </div>
	        </div>
	        <?php
	    }

		/**
		 * Get option
		 * @param  [type] $id      [description]
		 * @param  string $default [description]
		 * @return [type]          [description]
		 */
		public function get( $id, $default = '' ) {

			if ( 'all' == $id ) {
				return $this->options;
			} elseif ( isset( $this->options[ $id ] ) ) {
				return false !== $this->options[ $id ] ? $this->options[ $id ] : $default;
			}

			return $default;
		}
	}

endif;
