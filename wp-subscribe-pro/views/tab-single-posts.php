<?php
/**
 * The Popup Content Tab View
 */

$services = wps_get_mailing_services('options');
$options = $this->get('single_post_form_options');
?>
<!-- popup-single-posts-tab -->
<div class="wps-post-options" style="display: none;">

	<h3 class="wp-subscribe-field">
		<label for="wp_subscribe_enable_single_post_form">
			<input type="hidden" name="wp_subscribe_options[enable_single_post_form]" value="0">
			<input id="wp_subscribe_enable_single_post_form" type="checkbox" name="wp_subscribe_options[enable_single_post_form]" value="1" <?php checked( $this->get('enable_single_post_form') ) ?>>
			<?php esc_html_e( 'Add Subscribe Form to Single Posts', 'wp-subscribe' ); ?>
		</label>
	</h3>

	<p>
		<?php esc_html_e( 'Show subscribe form before, after, or inside the content on single posts and pages.', 'wp-subscribe' ) ?>
	</p>

	<div id="wp-subscribe-single-options"<?php echo !$this->get('enable_single_post_form') ? ' style="display: none;"' : '' ?>>

		<p class="wp-subscribe-field">

			<label for="wp_subscribe_form_before_single">
				<input type="radio" name="wp_subscribe_options[single_post_form_location]" value="top" id="wp_subscribe_form_before_single" <?php checked( $this->get('single_post_form_location'), 'top' ) ?>>
				<?php esc_html_e( 'Before post content', 'wp-subscribe' ) ?>
			</label>
			<br />
			<label for="wp_subscribe_form_after_single">
				<input type="radio" name="wp_subscribe_options[single_post_form_location]" value="bottom" id="wp_subscribe_form_after_single" <?php checked( $this->get('single_post_form_location'), 'bottom' ) ?>>
				<?php esc_html_e( 'After post content', 'wp-subscribe' ) ?>
			</label>
			<br />
			<label for="wp_subscribe_form_custom_single">
				<input type="radio" name="wp_subscribe_options[single_post_form_location]" value="custom" id="wp_subscribe_form_custom_single" <?php checked( $this->get('single_post_form_location'), 'custom' ) ?>>
				<?php esc_html_e( 'Only shortcode', 'wp-subscribe' ) ?>
			</label>
		</p>

		<p class="wp-subscribe-field">
			<a href="#" id="copy_options_popup_to_single" class="button-secondary ifpopup"<?php echo $this->get('enable_popup') ? '' : ' style="display: none;"'; ?>><?php esc_html_e( 'Copy popup form settings', 'wp-subscribe' ) ?></a>
		</p>

		<p class="wp-subscribe-field">
			<?php
				wps_field_select(array(
					'id'      => 'single_post_form_service',
					'name'    => 'wp_subscribe_options[single_post_form_options][service]',
					'value'   => $options['service'],
					'options' => $services,
					'class'   => 'services_dropdown'
				))
			?>
		</p>

		<div class="wp_subscribe_account_details">

			<?php foreach( $services as $service_id => $service_name ): ?>
				<div class="wps-account-details wp_subscribe_account_details_<?php echo esc_attr( $service_id ) ?>" data-service="<?php echo esc_attr( $service_id ) ?>" style="display: none;">
					<?php
						$service = wps_get_subscription_service( $service_id );
						$service->display_form( $options, $this, $group = 'single_post_form_options' );
					?>
				</div><!-- /wp_subscribe_account_details_<?php echo esc_attr( $service_id ) ?> -->
			<?php endforeach; ?>

		</div><!-- .wp_subscribe_account_details -->

		<div class="wp-subscribe-field wp_subscribe_include_name_wrapper" <?php echo 'feedburner' == $options['service'] ? ' style="display: none;"' : '' ?>>
			<label for="wp_subscribe_single_post_form_include_name">
				<input type="hidden" name="wp_subscribe_options[single_post_form_options][include_name_field]" value="0">
				<input id="wp_subscribe_single_post_form_include_name" type="checkbox" name="wp_subscribe_options[single_post_form_options][include_name_field]" value="1" <?php checked( $options['include_name_field'] ) ?>>
				<?php echo wp_kses_post( __( 'Include <strong>Name</strong> field', 'wp-subscribe' ) ) ?>
			</label>
		</div>

		<div class="wp-subscribe-field wp_subscribe_thanks_page">

			<label>
				<input type="hidden" name="wp_subscribe_options[single_post_form_options][thanks_page]" value="0">
				<input id="wp_subscribe_single_post_form_thanks_page" type="checkbox" class="thanks-page-field" name="wp_subscribe_options[single_post_form_options][thanks_page]" value="1" <?php checked( $options['thanks_page'] ) ?>>
				<?php esc_html_e( 'Show Thank You Page after successful subscription', 'wp-subscribe' ) ?>
			</label>

			<div class="wp_subscribe_thanks_page_details">

				<?php $this->field_text( array(
					'id'    => 'thanks_page_url',
					'title' => esc_html__( 'Thank You Page URL', 'wp-subscribe' ),
					'value' => $options['thanks_page_url']
				), 'single_post_form_options' ) ?>

			</div>

		</div>

		<div class="wp-subscribe-field" id="wp-subscribe-single-options">

			<?php
				$options = $this->get('single_post_form_labels');
				$options['consent_text'] = isset($options['consent_text']) ? $options['consent_text'] : '';
			?>

			<?php $this->field_text( array(
				'id'    => 'title',
				'title' => esc_html__( 'Title', 'wp-subscribe' ),
				'value' => $options['title']
			), 'single_post_form_labels' ) ?>

			<?php $this->field_text( array(
				'id'    => 'text',
				'title' => esc_html__( 'Text', 'wp-subscribe' ),
				'value' => $options['text']
			), 'single_post_form_labels' ) ?>

			<?php $this->field_text( array(
				'id'    => 'name_placeholder',
				'title' => esc_html__( 'Name Placeholder Text', 'wp-subscribe' ),
				'value' => $options['name_placeholder']
			), 'single_post_form_labels' ) ?>

			<?php $this->field_text( array(
				'id'    => 'email_placeholder',
				'title' => esc_html__( 'Email Placeholder Text', 'wp-subscribe' ),
				'value' => $options['email_placeholder']
			), 'single_post_form_labels' ) ?>

			<?php $this->field_text( array(
				'id'    => 'consent_text',
				'title' => __( 'Consent Label', 'wp-subscribe' ),
				'value' => $options['consent_text']
			), 'single_post_form_labels' ) ?>

			<?php $this->field_text( array(
				'id'    => 'button_text',
				'title' => esc_html__( 'Button Text', 'wp-subscribe' ),
				'value' => $options['button_text']
			), 'single_post_form_labels' ) ?>

			<?php $this->field_text( array(
				'id'    => 'success_message',
				'title' => esc_html__( 'Success Message', 'wp-subscribe' ),
				'value' => $options['success_message']
			), 'single_post_form_labels' ) ?>

			<?php $this->field_text( array(
				'id'    => 'error_message',
				'title' => esc_html__( 'Error Message', 'wp-subscribe' ),
				'value' => $options['error_message']
			), 'single_post_form_labels' ) ?>

			<?php $this->field_textarea( array(
				'id'    => 'footer_text',
				'title' => esc_html__( 'Footer Text', 'wp-subscribe' ),
				'value' => $options['footer_text']
			), 'single_post_form_labels' ) ?>

			<div class="wp-subscribe-content-colors">

				<?php
					$this->color_palettes_select( 'wp_subscribe_options_colors_single_post_form_colors' );

					$this->field_color( 'background_color', esc_html__( 'Background color', 'wp-subscribe' ), 'single_post_form_colors' );
					$this->field_color( 'title_color', esc_html__( 'Title color', 'wp-subscribe' ), 'single_post_form_colors' );
					$this->field_color( 'text_color', esc_html__( 'Text color', 'wp-subscribe' ), 'single_post_form_colors' );
					$this->field_color( 'field_text_color', esc_html__( 'Field text color', 'wp-subscribe' ), 'single_post_form_colors' );
					$this->field_color( 'field_background_color', esc_html__( 'Field background color', 'wp-subscribe' ), 'single_post_form_colors' );
					$this->field_color( 'button_text_color', esc_html__( 'Button text color', 'wp-subscribe' ), 'single_post_form_colors' );
					$this->field_color( 'button_background_color', esc_html__( 'Button background color', 'wp-subscribe' ), 'single_post_form_colors' );
					$this->field_color( 'footer_text_color', esc_html__( 'Footer text color', 'wp-subscribe' ), 'single_post_form_colors' );
				?>

			</div>

		</div>

		<p>
			<?php _e( 'You may also use the <code>[wp-subscribe]</code> shortcode in your posts &amp; pages.', 'wp-subscribe' ) ?>
		</p>

	</div>

	<p class="submit">
		<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-subscribe' ) ?>">
	</p>

</div><!-- /popup-single-posts-tab -->
