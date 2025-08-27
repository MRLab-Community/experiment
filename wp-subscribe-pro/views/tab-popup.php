<!-- popup-tab -->
<div class="wps-popup-options">

	<h3 class="wp-subscribe-field enable-popup">
		<label for="wp_subscribe_enable_popup">
			<input type="hidden" name="wp_subscribe_options[enable_popup]" value="0">
			<input id="wp_subscribe_enable_popup" type="checkbox" name="wp_subscribe_options[enable_popup]" value="1" <?php checked( $this->get('enable_popup') ) ?>>
			<?php esc_html_e( 'Enable Popup', 'wp-subscribe' ) ?>
		</label>
	</h3>

	<p>
		<?php esc_html_e( 'Enable site-wide popup that shows subscribe form, related posts, or custom HTML.', 'wp-subscribe' ) ?>
	</p>

	<?php $enable_popup = !$this->get('enable_popup') ? ' style="display: none;"' : '' ?>
	<div id="wp-subscribe-popup-options"<?php echo $enable_popup ?>>

		<div class="wp-subscribe-field">

			<label for="wp_subscribe_show_form">
				<input class="popup_content_field" type="radio" name="wp_subscribe_options[popup_content]" value="subscribe_form" id="wp_subscribe_show_form" <?php checked( $this->get( 'popup_content' ), 'subscribe_form' ) ?>>
				<?php esc_html_e( 'Show subscribe form in popup', 'wp-subscribe' ) ?>
			</label>
			<br />
			<label for="wp_subscribe_show_posts">
				<input class="popup_content_field" type="radio" name="wp_subscribe_options[popup_content]" value="posts" id="wp_subscribe_show_posts" <?php checked( $this->get( 'popup_content' ), 'posts' ) ?>>
				<?php esc_html_e( 'Show related posts in popup', 'wp-subscribe' ) ?>
			</label>
			<br />
			<label for="wp_subscribe_show_custom">
				<input class="popup_content_field" type="radio" name="wp_subscribe_options[popup_content]" value="custom_html" id="wp_subscribe_show_custom" <?php checked( $this->get( 'popup_content' ), 'custom_html' ) ?>>
				<?php esc_html_e( 'Show custom HTML or shortcode in popup', 'wp-subscribe' ) ?>
			</label>

		</div>

		<div class="wp-subscribe-field">
			<label for="wp_subscribe_popup_width"><?php esc_html_e( 'Popup width', 'wp-subscribe' ) ?></label>
			<div id="wp-subscribe-popup-width-slider"></div>
			<input type="number" min="200" max="1200" step="10" name="wp_subscribe_options[popup_width]" id="wp_subscribe_popup_width" value="<?php echo $this->get( 'popup_width' ) ?>" /><span class="width-px-label"><?php esc_html_e( 'px', 'wp-subscribe' ) ?></span>
		</div>

		<div class="wp-subscribe-field">
			<h4><?php esc_html_e( 'Popup Animation', 'wp-subscribe' ); ?></h4>
			<p>
				<?php wps_get_animations( 'popup_animation_in', 'wp_subscribe_options[popup_animation_in]', $this->get('popup_animation_in') ) ?>
				<?php wps_get_animations( 'popup_animation_out', 'wp_subscribe_options[popup_animation_out]', $this->get('popup_animation_out') ) ?>
			</p>
		</div>

		<div class="wp-subscribe-field">
			<?php $this->field_color( 'popup_overlay_color', esc_html__( 'Popup overlay color', 'wp-subscribe' ), '' ) ?>
		</div>

		<div class="wp-subscribe-field">
			<label for="wp_subscribe_overlay_opacity">
				<?php esc_html_e( 'Popup overlay opacity', 'wp-subscribe' ) ?>
			</label>
			<div id="wp-subscribe-opacity-slider"></div>
			<input type="number" min="0" max="1" step="0.01" name="wp_subscribe_options[popup_overlay_opacity]" id="wp_subscribe_overlay_opacity" value="<?php echo $this->get('popup_overlay_opacity') ?>" >
		</div>

	</div>

	<p class="submit">
		<a href="#wp_subscribe_popup" class="button-secondary wp-subscribe-preview-popup ifpopup" data-animatein="<?php echo $this->get('popup_animation_in') ?>" data-animateout="<?php echo $this->get('popup_animation_out') ?>"<?php echo $enable_popup ?>>
			<?php esc_html_e( 'Preview Popup', 'wp-subscribe' ) ?>
		</a>
		<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-subscribe' ) ?>" >
	</p>

</div> <!-- /popup-tab -->
