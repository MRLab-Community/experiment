<?php
/**
 * The Popup Content Tab View
 */

$enable_popup_content = ( 'subscribe_form' != $this->get('popup_content') ) ? ' style="display: none;"' : '';
$services = wps_get_mailing_services('options');
$options = $this->get('popup_form_options');
?>

<!-- popup-content-tab -->
<div class="wps-popup-content-options" style="display: none;">

	<div class="wp-subscribe-field" id="wp-subscribe-form-options"<?php echo $enable_popup_content ?>>

		<div class="wp-subscribe-field">
			<?php
				wps_field_select(array(
					'id'      => 'popup_form_service',
					'name'    => 'wp_subscribe_options[popup_form_options][service]',
					'value'   => $options['service'],
					'options' => $services,
					'class'   => 'services_dropdown'
				));
			?>
		</div>

		<div class="wp_subscribe_account_details">

			<?php foreach( $services as $service_id => $service_name ): ?>
				<div class="wps-account-details wp_subscribe_account_details_<?php echo esc_attr( $service_id ) ?>" data-service="<?php echo esc_attr( $service_id ) ?>" style="display: none;">
					<?php
						$service = wps_get_subscription_service( $service_id );
						$service->display_form( $options, $this, $group = 'popup_form_options' );
					?>
				</div><!-- /wp_subscribe_account_details_<?php echo esc_attr( $service_id ) ?> -->
			<?php endforeach; ?>

		</div><!-- .wp_subscribe_account_details -->

		<div class="wp-subscribe-field wp_subscribe_include_name_wrapper" <?php echo 'feedburner' == $options['service'] ? ' style="display: none;"' : '' ?>>

			<label for="wp_subscribe_popup_form_include_name">
				<input type="hidden" name="wp_subscribe_options[popup_form_options][include_name_field]" value="0">
				<input id="wp_subscribe_popup_form_include_name" type="checkbox" name="wp_subscribe_options[popup_form_options][include_name_field]" value="1" <?php checked( $options['include_name_field'] ) ?>>
				<?php echo wp_kses_post( __( 'Include <strong>Name</strong> field', 'wp-subscribe' ) ) ?>
			</label>

		</div>

		<div class="wp-subscribe-field wp_subscribe_thanks_page">

			<?php
			$thanks_page = (isset($options['thanks_page']) && $options['thanks_page']) ? 'checked="checked"' : '';
			$thanks_page_url = isset($options['thanks_page_url']) ? $options['thanks_page_url'] : '';
			?>

			<label>
				<input type="hidden" name="wp_subscribe_options[popup_form_options][thanks_page]" value="0">
				<input id="wp_subscribe_popup_form_thanks_page" type="checkbox" class="thanks-page-field" name="wp_subscribe_options[popup_form_options][thanks_page]" value="1" <?php echo $thanks_page; ?>>
				<?php esc_html_e( 'Show Thank You Page after successful subscription', 'wp-subscribe' ); ?>
			</label>

			<div class="wp_subscribe_thanks_page_details">

				<?php $this->field_text( array(
					'id'    => 'thanks_page_url',
					'title' => esc_html__( 'Thank You Page URL', 'wp-subscribe' ),
					'value' => $thanks_page_url
				), 'popup_form_options' ) ?>

			</div>

		</div>

		<?php
			$options = $this->get('popup_form_labels');
			$options['consent_text'] = isset($options['consent_text']) ? $options['consent_text'] : '';
		?>

		<?php $this->field_text( array(
			'id'    => 'title',
			'title' => esc_html__( 'Title', 'wp-subscribe' ),
			'value' => $options['title']
		) ) ?>

		<?php $this->field_text( array(
			'id'    => 'text',
			'title' => esc_html__( 'Text', 'wp-subscribe' ),
			'value' => $options['text']
		) ) ?>

		<?php $this->field_text( array(
			'id'    => 'name_placeholder',
			'title' => esc_html__( 'Name Placeholder Text', 'wp-subscribe' ),
			'value' => $options['name_placeholder']
		) ) ?>

		<?php $this->field_text( array(
			'id'    => 'email_placeholder',
			'title' => esc_html__( 'Email Placeholder Text', 'wp-subscribe' ),
			'value' => $options['email_placeholder']
		) ) ?>

		<?php $this->field_text( array(
			'id'    => 'consent_text',
			'title' => __( 'Consent Label', 'wp-subscribe' ),
			'value' => $options['consent_text']
		) ) ?>

		<?php $this->field_text( array(
			'id'    => 'button_text',
			'title' => esc_html__( 'Button Text', 'wp-subscribe' ),
			'value' => $options['button_text']
		) ) ?>

		<?php $this->field_text( array(
			'id'    => 'success_message',
			'title' => esc_html__( 'Success Message', 'wp-subscribe' ),
			'value' => $options['success_message']
		) ) ?>

		<?php $this->field_text( array(
			'id'    => 'error_message',
			'title' => esc_html__( 'Error Message', 'wp-subscribe' ),
			'value' => $options['error_message']
		) ) ?>

		<?php $this->field_textarea( array(
			'id'    => 'footer_text',
			'title' => esc_html__( 'Footer Text', 'wp-subscribe' ),
			'value' => $options['footer_text']
		) ) ?>

		<div class="wp-subscribe-content-colors">

		<?php
			$this->color_palettes_select( 'wp_subscribe_options_colors_popup_form_colors' );

			$this->field_color( 'background_color', esc_html__( 'Background color', 'wp-subscribe' ) );
			$this->field_color( 'title_color', esc_html__( 'Title color', 'wp-subscribe' ) );
			$this->field_color( 'text_color', esc_html__( 'Text color', 'wp-subscribe' ) );
			$this->field_color( 'field_text_color', esc_html__( 'Field text color', 'wp-subscribe' ) );
			$this->field_color( 'field_background_color', esc_html__( 'Field background color', 'wp-subscribe' ) );
			$this->field_color( 'button_text_color', esc_html__( 'Button text color', 'wp-subscribe' ) );
			$this->field_color( 'button_background_color', esc_html__( 'Button background color', 'wp-subscribe' ) );
			$this->field_color( 'footer_text_color', esc_html__( 'Footer text color', 'wp-subscribe' ) );
		?>

		</div>

	</div>

	<div class="wp-subscribe-field" id="wp-subscribe-custom-html-field"<?php echo $this->get('popup_content') != 'custom_html' ? ' style="display: none;"' : ''; ?>>
		<?php wp_editor(
				$this->get('popup_custom_html'),
				'wp_subscribe_options[popup_custom_html]',
				array(
					'textarea_rows' => 8,
					'tinymce'       => false,
					'media_buttons' => false,
					'wpautop'       => false,
					'quicktags'     => array( 'buttons' => 'strong,em,block,del,ins,img,ul,ol,li,code,close' )
				)
			);
		?>
	</div>

	<div class="wp-subscribe-field" id="wp-subscribe-popup-posts-options"<?php echo $this->get('popup_content') != 'posts' ? ' style="display: none;"' : ''; ?>>

		<?php $options = $this->get('popup_posts_labels'); ?>

		<?php $this->field_text( array(
			'id'    => 'title',
			'title' => esc_html__( 'Title', 'wp-subscribe' ),
			'value' => $options['title']
		), 'popup_posts_labels' ) ?>

		<?php $this->field_text( array(
			'id'    => 'text',
			'title' => esc_html__( 'Text', 'wp-subscribe' ),
			'value' => $options['text']
		), 'popup_posts_labels' ) ?>

		<div class="wp-subscribe-content-colors">

			<?php
				$this->field_color( 'background_color', esc_html__( 'Background color', 'wp-subscribe' ), 'popup_posts_colors' );
				$this->field_color( 'title_color', esc_html__( 'Title color', 'wp-subscribe' ), 'popup_posts_colors' );
				$this->field_color( 'text_color', esc_html__( 'Text color', 'wp-subscribe' ), 'popup_posts_colors' );
				$this->field_color( 'line_color', esc_html__( 'Line color', 'wp-subscribe' ), 'popup_posts_colors' );
			?>

		</div>

		<h4><?php esc_html_e( 'Post Meta', 'wp-subscribe' ) ?></h4>

		<label class="postmeta-label" for="meta_showcategory">

			<input type="hidden" name="wp_subscribe_options[popup_posts_meta][category]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_posts_meta][category]" id="meta_showcategory" value="1" <?php checked( $this->get('popup_posts_meta')['category'] ) ?>>
			<?php esc_html_e( 'Show post categories', 'wp-subscribe' ) ?>

		</label>

		<label class="postmeta-label" for="meta_showexcerpt">

			<input type="hidden" name="wp_subscribe_options[popup_posts_meta][excerpt]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_posts_meta][excerpt]" id="meta_showexcerpt" value="1" <?php checked( $this->get('popup_posts_meta')['excerpt'] ) ?>>
			<?php esc_html_e( 'Show post excerpt', 'wp-subscribe' ) ?>

		</label>

	</div>

	<p class="submit">
		<a href="#wp_subscribe_popup" class="button-secondary wp-subscribe-preview-popup ifpopup" data-animatein="<?php echo $this->get('popup_animation_in') ?>" data-animateout="<?php echo $this->get('popup_animation_out') ?>">
			<?php esc_html_e( 'Preview Popup', 'wp-subscribe' ) ?>
		</a>
		<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-subscribe' ) ?>" >
	</p>
</div><!-- /popup-content-tab -->
