<?php
/**
 * The WP Subscribe widget class
 */

if( ! class_exists('wp_subscribe') ) :

class wp_subscribe extends WP_Widget {

	/**
	 * The Constructor
	 */
		public function __construct() {

				add_action( 'wp_enqueue_scripts', 					array( &$this, 'register_scripts' ) );
				add_action( 'admin_enqueue_scripts', 				array( &$this, 'enqueue_scripts' ) );
				add_action( 'customize_controls_enqueue_scripts',	array( &$this, 'enqueue_scripts' ) );

				// Widget settings
				$widget_ops = array(
			'classname'		=> 'wp_subscribe',
			'description'	=> esc_html__( 'Displays subscription form, supports FeedBurner, MailChimp, GetResponse & AWeber.', 'wp-subscribe' )
		);

				// Widget control settings
				$control_ops = array(
			'id_base' => 'wp_subscribe'
		);

				// Create the widget.
				parent::__construct(
			'wp_subscribe',
			esc_html__( 'WP Subscribe Pro Widget', 'wp-subscribe' ),
			$widget_ops,
			$control_ops
		);
		}

	/**
	 * Get default values for widget
	 * @return array
	 */
	public function get_defaults() {

		return apply_filters( 'wp_subscribe_form_defaults', array(
			'service'            => 'feedburner',
						'include_name_field' => false,
						'thanks_page'        => false,
						'thanks_page_url'    => '',

						'title'             => esc_html__( 'Get more stuff', 'wp-subscribe' ),
						'text'              => esc_html__( 'Subscribe to our mailing list and get interesting stuff and updates to your email inbox.', 'wp-subscribe' ),
						'email_placeholder' => esc_html__( 'Enter your email here', 'wp-subscribe' ),
						'consent_text'			=> esc_html__( 'I consent to my submitted data being collected via this form*', 'wp-subscribe' ),
						'name_placeholder'  => esc_html__( 'Enter your name here', 'wp-subscribe' ),
						'button_text'       => esc_html__( 'Sign Up Now', 'wp-subscribe' ),
						'success_message'   => esc_html__( 'Thank you for subscribing.', 'wp-subscribe' ),
						'error_message'     => esc_html__( 'Something went wrong.', 'wp-subscribe' ),
						'footer_text'       => sprintf(__( 'The form collects name and email so that we can add you to our newsletter list for project updates. Checkout our %sprivacy policy%s for the full story on how we protect and manage your submitted data!', 'wp-subscribe' ), '<a href="#">', '</a>'),

						'background_color'        => '#f47555',
						'title_color'             => '#FFFFFF',
						'text_color'              => '#FFFFFF',
						'field_text_color'        => '#FFFFFF',
						'field_background_color'  => '#d56144',
						'button_text_color'       => '#f47555',
						'button_background_color' => '#FFFFFF',
						'footer_text_color'       => '#FFFFFF'
				));
		}

	/**
	 * Register scripts and json to be used in plugin
	 * @return void
	 */
		function register_scripts() {

				wp_register_style( 'wp-subscribe', wps()->plugin_url() . '/assets/css/wp-subscribe-form.css' );
				wp_register_script( 'wp-subscribe', wps()->plugin_url() . '/assets/js/wp-subscribe-form.js', array( 'jquery' ) );

				wp_localize_script( 'wp-subscribe', 'wp_subscribe', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		) );
		}

	/**
	 * Enqueue script for specific screens only
	 * @return void
	 */
		function enqueue_scripts() {

		$screen = get_current_screen();
		$current_filter = current_filter();

			if ( 'widgets' === $screen->id || 'customize_controls_enqueue_scripts' === $current_filter ) {

				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );

				wp_enqueue_style( 'wp-subscribe-options', wps()->plugin_url() . '/assets/css/wp-subscribe-options.css' );
				wp_enqueue_script( 'wp-subscribe-admin', wps()->plugin_url() . '/assets/js/wp-subscribe-admin.js', array( 'jquery' ) );
			}
		}

	/**
	 * Display widget
	 * @param  array $args
	 * @param  array $instance
	 * @return void
	 */
		function widget( $args, $instance ) {

		extract( $args );
				$instance = wp_parse_args( (array) $instance, $this->get_defaults() );

		$instance['before_widget'] = $before_widget;
		$instance['after_widget'] = $after_widget;
		$instance['widget_id'] = $this->id;
		$instance['form_type'] = 'widget';

		wps_the_form( $instance );
		}

	/**
	 * Update widget
	 *
	 * @param  array $new_instance
	 * @param  array $old_instance
	 *
	 * @return array
	 */
		function update( $new_instance, $old_instance ) {

			$instance = $old_instance;
			$instance = array_merge( $instance, $new_instance );

			// Feedburner ID -- make sure the user didn't insert full url
			if( isset( $instance['feedburner_id'] ) && 0 === strpos( $instance['feedburner_id'], 'http' ) ) {
				$instance['feedburner_id'] = substr( $instance['feedburner_id'], strrpos( $instance['feedburner_id'], '/' ) + 1 );
			}

			return $instance;
		}

	/**
	 * Display widget form
	 *
	 * @param  array $instance
	 * @return void
	 */
		function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, $this->get_defaults() );
		$services = wps_get_mailing_services('options');
		?>
			<div class="wp_subscribe_options_form">

					<!-- Hidden title field to prevent WP picking up Title Color field as widget title -->
					<input type="hidden" value="" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>">

			<?php $this->field_select(array(
				'id'      => 'service',
				'name'    => 'service',
				'title'   => esc_html( 'Service:', 'wp-subscribe' ),
				'value'   => $instance['service'],
				'options' => $services,
				'class'   => 'services_dropdown'
			)); ?>

					<div class="wp_subscribe_account_details">

				<?php foreach( $services as $service_id => $service_name ): ?>
					<div class="wps-account-details wp_subscribe_account_details_<?php echo esc_attr( $service_id ) ?>" data-service="<?php echo esc_attr( $service_id ) ?>" style="display: none;">
						<?php
							$service = wps_get_subscription_service( $service_id );
							$service->display_form( $instance, $this );
						?>
					</div><!-- /wp_subscribe_account_details_<?php echo esc_attr( $service_id ) ?> -->
				<?php endforeach; ?>

					</div><!-- .wp_subscribe_account_details -->

					<p class="wp_subscribe_include_name">
						<label for="<?php echo $this->get_field_id('include_name_field') ?>">
							<input type="hidden" name="<?php echo $this->get_field_name('include_name_field'); ?>" value="0">
							<input id="<?php echo $this->get_field_id('include_name_field'); ?>" type="checkbox" class="include-name-field" name="<?php echo $this->get_field_name('include_name_field'); ?>" value="1" <?php checked($instance['include_name_field']); ?>>
							<?php echo wp_kses_post( __( 'Include <strong>Name</strong> field', 'wp-subscribe' ) ) ?>
						</label>
					</p>

					<div class="wp_subscribe_thanks_page">

						<input type="hidden" name="<?php echo $this->get_field_name('thanks_page'); ?>" value="0">

						<label>
							<input id="<?php echo $this->get_field_id('thanks_page'); ?>" type="checkbox" class="thanks-page-field" name="<?php echo $this->get_field_name('thanks_page'); ?>" value="1" <?php checked($instance['thanks_page']); ?>>
							<?php esc_html_e( 'Show Thank You Page after successful subscription', 'wp-subscribe' ); ?>
						</label>

				<div class="wp_subscribe_thanks_page_details">

					<?php $this->field_text( array(
						'id' => 'thanks_page_url',
						'name' => 'thanks_page_url',
						'title' => esc_html__('Thank You Page URL', 'wp-subscribe'),
						'value' => $instance['thanks_page_url']
					) ) ?>

									<label>
											<input type="hidden" name="<?php echo $this->get_field_name('thanks_page_new_window'); ?>" value="0">
											<input id="<?php echo $this->get_field_id('thanks_page_new_window'); ?>" type="checkbox" class="thanks-page-new-window-field" name="<?php echo $this->get_field_name('thanks_page_new_window'); ?>" value="1" <?php checked(isset($instance['thanks_page_new_window'])) ?>>
											<?php esc_html_e( 'Open in new window', 'wp-subscribe' ); ?>
									</label>

				</div>

			</div>

					<h4 class="wp_subscribe_labels_header">
				<a class="wp-subscribe-toggle" href="#" rel="wp_subscribe_labels"><?php _e('Labels', 'wp-subscribe'); ?></a>
			</h4>

					<div class="wp_subscribe_labels" style="display: none;">

				<?php

					$this->field_textarea(array(
						'id'    => 'title',
						'name'  => 'title',
						'title' => esc_html( 'Title', 'wp-subscribe' ),
						'value' => $instance['title']
					));

					$this->field_text(array(
						'id'    => 'text',
						'name'  => 'text',
						'title' => esc_html( 'Text', 'wp-subscribe' ),
						'value' => $instance['text']
					));

					$this->field_text(array(
						'id'    => 'name_placeholder',
						'name'  => 'name_placeholder',
						'title' => esc_html( 'Name Placeholder', 'wp-subscribe' ),
						'value' => $instance['name_placeholder']
					));

					$this->field_text(array(
						'id'    => 'email_placeholder',
						'name'  => 'email_placeholder',
						'title' => esc_html( 'Email Placeholder', 'wp-subscribe' ),
						'value' => $instance['email_placeholder']
					));

					$this->field_text(array(
						'id'    => 'consent_text',
						'name'  => 'consent_text',
						'title' => esc_html( 'Consent Label', 'wp-subscribe' ),
						'value' => $instance['consent_text']
					));

					$this->field_text(array(
						'id'    => 'button_text',
						'name'  => 'button_text',
						'title' => esc_html( 'Button Text', 'wp-subscribe' ),
						'value' => $instance['button_text']
					));

					$this->field_text(array(
						'id'    => 'success_message',
						'name'  => 'success_message',
						'title' => esc_html( 'Success Message', 'wp-subscribe' ),
						'value' => $instance['success_message']
					));

					$this->field_text(array(
						'id'    => 'error_message',
						'name'  => 'error_message',
						'title' => esc_html( 'Error Message', 'wp-subscribe' ),
						'value' => $instance['error_message']
					));

					$this->field_textarea(array(
						'id'    => 'footer_text',
						'name'  => 'footer_text',
						'title' => esc_html( 'Footer Text', 'wp-subscribe' ),
						'value' => $instance['footer_text']
					));
						?>

					</div><!-- .wp_subscribe_labels -->

					<h4 class="wp_subscribe_colors_header">
				<a class="wp-subscribe-toggle" href="#" rel="wp_subscribe_colors"><?php esc_html_e( 'Colors', 'wp-subscribe' ) ?></a>
			</h4>

			<div class="wp_subscribe_colors" style="display: none;">

						<?php

							$this->color_palettes_select();

					$this->field_color(array(
						'id'    => 'background_color',
						'name'  => 'background_color',
						'title' => esc_html( 'Background', 'wp-subscribe' ),
						'value' => $instance['background_color']
					));

					$this->field_color(array(
						'id'    => 'title_color',
						'name'  => 'title_color',
						'title' => esc_html( 'Title', 'wp-subscribe' ),
						'value' => $instance['title_color']
					));

					$this->field_color(array(
						'id'    => 'text_color',
						'name'  => 'text_color',
						'title' => esc_html( 'Text', 'wp-subscribe' ),
						'value' => $instance['text_color']
					));

					$this->field_color(array(
						'id'    => 'field_text_color',
						'name'  => 'field_text_color',
						'title' => esc_html( 'Field Text', 'wp-subscribe' ),
						'value' => $instance['field_text_color']
					));

					$this->field_color(array(
						'id'    => 'field_background_color',
						'name'  => 'field_background_color',
						'title' => esc_html( 'Field Background', 'wp-subscribe' ),
						'value' => $instance['field_background_color']
					));

					$this->field_color(array(
						'id'    => 'button_text_color',
						'name'  => 'button_text_color',
						'title' => esc_html( 'Button Text', 'wp-subscribe' ),
						'value' => $instance['button_text_color']
					));

					$this->field_color(array(
						'id'    => 'button_background_color',
						'name'  => 'button_background_color',
						'title' => esc_html( 'Button Background', 'wp-subscribe' ),
						'value' => $instance['button_background_color']
					));

					$this->field_color(array(
						'id'    => 'footer_text_color',
						'name'  => 'footer_text_color',
						'title' => esc_html( 'Footer Text', 'wp-subscribe' ),
						'value' => $instance['footer_text_color']
					));
						?>
					</div><!-- .wp_subscribe_colors -->

			</div><!-- .wp_subscribe_options_form -->
		<?php
		}

	// ------------------ HELPERS -------------------------

	public function color_palettes_select() {

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
										$hiddens .= sprintf( '<input type="hidden" class="wps-palette-color" name="%1$s" value="%2$s">', $this->get_field_id( $field . '_color' ), esc_attr( $color ) );
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

	// -------------------------- FIELD HELPRES ----------------------

		public function field_textarea( $args = array() ) {

		extract( $args );
		?>
				<p class="wp-subscribe-label-field wp-subscribe-<?php echo $id; ?>-field">
						<label for="<?php echo $this->get_field_id($id) ?>">
								<?php echo $title ?>
						</label>

						<textarea class="widefat" id="<?php echo $this->get_field_id($id) ?>" name="<?php echo $this->get_field_name($id) ?>"><?php echo esc_textarea( $value ) ?></textarea>
				</p>

				<?php
		}

	public function field_text( $args = array() ) {

		extract( $args );
		?>
				<div class="wp-subscribe-label-field wp-subscribe-<?php echo $id; ?>-field">
						<label for="<?php echo $this->get_field_id( $id ) ?>">
								<?php echo esc_html( $title ) ?>
						</label>

			<div class="wps-input-wrapper">

				<?php wps_field_text(array(
					'id'	=> $this->get_field_id( $id ),
					'name'	=> $this->get_field_name( $id ),
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

	public function field_hidden( $args = array() ) {

		extract( $args );

		wps_field_hidden(array(
			'id'	=> $this->get_field_id( $id ),
			'name'	=> $this->get_field_name( $id ),
			'value'	=> $value,
			'data_id' => $id
		));
		}

	public function field_raw( $args = array() ) {

		call_user_func_array( $args['content'], array( $args['value'] ) );
		}

	public function field_checkbox( $args = array() ) {

		extract( $args );
		?>
		<div class="wp-subscribe-<?php echo $id; ?>-field">

			<label for="<?php echo $this->get_field_id( $id ) ?>">

				<input type="hidden" name="<?php echo $this->get_field_name( $id ) ?>" value="0" data-id="<?php echo $this->get_field_id( $id ) ?>">

				<input type="checkbox" id="<?php echo $this->get_field_id( $id ) ?>" name="<?php echo $this->get_field_name( $id ) ?>" value="1"<?php checked( $value ) ?> data-id="<?php echo $id ?>">

				<?php echo esc_html($title) ?>

			</label>

		</div>
		<?php
	}

		public function field_select( $args = array() ) {

		$options = array();
		extract( $args );
				?>

				<div class="wp-subscribe-label-field wp-subscribe-<?php echo $id ?>-field">
						<label for="<?php echo $this->get_field_id( $id ) ?>">
								<?php echo esc_html( $title ) ?>
						</label>

			<div class="wps-input-wrapper">
				<?php wps_field_select(array(
					'id'	=> $this->get_field_id( $id ),
					'name'	=> $this->get_field_name( $id ),
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

		public function field_color( $args = array() ) {

		extract( $args );
				?>
				<div class="wp-subscribe-color-field wp-subscribe-<?php echo $id; ?>-field">
						<label for="<?php echo $this->get_field_id( $id ) ?>">
								<?php echo $title ?>
						</label>

						<input class="widefat wp-subscribe-color-select"
									 id="<?php echo $this->get_field_id( $id ) ?>"
									 name="<?php echo $this->get_field_name( $id ) ?>"
									 type="text"
									 value="<?php echo $value ?>" >
				</div>
				<?php
		}
}

/**
 * Register widget
 * @return void
 */
add_action( 'widgets_init', 'wps_register_widget' );
function wps_register_widget() {
		register_widget( 'wp_subscribe' );
}

endif;
