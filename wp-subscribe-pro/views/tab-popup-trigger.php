<!-- popup-trigger-tab -->
<div class="wps-popup-trigger-options" style="display: none;">

	<?php $options = $this->get('popup_show_on') ?>

	<h4><?php esc_html_e( 'Popup pages', 'wp-subscribe' ) ?></h4>

	<div class="wp-subscribe-field">
		<label for="wp_subscribe_popup_show_on_front_page">
			<input type="hidden" name="wp_subscribe_options[popup_show_on][front_page]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_show_on][front_page]" value="1" id="wp_subscribe_popup_show_on_front_page" <?php checked( $options['front_page'] ) ?>>
			<?php esc_html_e( 'Show popup on front page', 'wp-subscribe' ) ?>
		</label>
		<br />
		<label for="wp_subscribe_popup_show_on_single">
			<input type="hidden" name="wp_subscribe_options[popup_show_on][single]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_show_on][single]" value="1" id="wp_subscribe_popup_show_on_single" <?php checked( $options['single'] ) ?>>
			<?php esc_html_e( 'Show popup on single posts, pages, and other post types', 'wp-subscribe' ) ?>
		</label>
		<br />
		<label for="wp_subscribe_popup_show_on_archive">
			<input type="hidden" name="wp_subscribe_options[popup_show_on][archive]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_show_on][archive]" value="1" id="wp_subscribe_popup_show_on_archive" <?php checked( $options['archive'] ) ?>>
			<?php esc_html_e( 'Show popup on archive pages (posts by date, category, etc.)', 'wp-subscribe' ) ?>
		</label>
		<br />
		<label for="wp_subscribe_popup_show_on_search">
			<input type="hidden" name="wp_subscribe_options[popup_show_on][search]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_show_on][search]" value="1" id="wp_subscribe_popup_show_on_search" <?php checked( $options['search'] ) ?>>
			<?php esc_html_e( 'Show popup on search results', 'wp-subscribe' ) ?>
		</label>
		<br />
		<label for="wp_subscribe_popup_show_on_404_page">
			<input type="hidden" name="wp_subscribe_options[popup_show_on][404_page]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_show_on][404_page]" value="1" id="wp_subscribe_popup_show_on_404_page" <?php checked( $options['404_page'] ) ?>>
			<?php esc_html_e( 'Show popup on 404 Not Found page', 'wp-subscribe' ) ?>
		</label>
	</div>

	<?php $options = $this->get('popup_triggers') ?>

	<h4><?php esc_html_e( 'Popup triggers', 'wp-subscribe' ); ?></h4>

	<p class="wp-subscribe-field">

		<label for="wp_subscribe_popup_trigger_enter">
			<input type="hidden" name="wp_subscribe_options[popup_triggers][on_enter]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_triggers][on_enter]" id="wp_subscribe_popup_trigger_enter" value="1" <?php checked( $options['on_enter'] ) ?>>
			<?php esc_html_e( 'Show popup when visitor enters site', 'wp-subscribe' ) ?>
		</label>

		<br />

		<label for="wp_subscribe_popup_trigger_timeout">
			<input type="hidden" name="wp_subscribe_options[popup_triggers][on_timeout]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_triggers][on_timeout]" id="wp_subscribe_popup_trigger_timeout" value="1" <?php checked( $options['on_timeout'] ) ?>>
			<?php
				$input_seconds = '<input type="number" min="1" max="120" step="1" class="small-text" name="wp_subscribe_options[popup_triggers][timeout]" value="' . $options['timeout'] . '">';
				printf( esc_html__( 'Show popup after %s seconds.', 'wp-subscribe' ), $input_seconds );
			?>
		</label>

		<br />

		<label for="wp_subscribe_popup_trigger_reach_bottom">
			<input type="hidden" name="wp_subscribe_options[popup_triggers][on_reach_bottom]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_triggers][on_reach_bottom]" id="wp_subscribe_popup_trigger_reach_bottom" value="1" <?php checked( $options['on_reach_bottom'] ) ?>>
			<?php esc_html_e( 'Show popup when visitor reaches the end of the content (only on single posts &amp; pages)', 'wp-subscribe' ) ?>
		</label>

		<br />

		<label for="wp_subscribe_popup_trigger_exit_intent">
			<input type="hidden" name="wp_subscribe_options[popup_triggers][on_exit_intent]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_triggers][on_exit_intent]" id="wp_subscribe_popup_trigger_exit_intent" value="1" <?php checked( $options['on_exit_intent'] ) ?>>
			<?php esc_html_e( 'Show popup when visitor is about to leave (exit intent)', 'wp-subscribe' ) ?>
		</label>

		<br />

		<?php
		$mobile = isset( $options['hide_on_mobile'] ) ? $options['hide_on_mobile'] : '0';
		$screen = isset( $options['hide_on_screen'] ) ? $options['hide_on_screen'] : '400';
		?>
		<label for="wp_subscribe_popup_hide_on_mobile">
			<input type="hidden" name="wp_subscribe_options[popup_triggers][hide_on_mobile]" value="0">
			<input type="checkbox" name="wp_subscribe_options[popup_triggers][hide_on_mobile]" id="wp_subscribe_popup_hide_on_mobile" min="1" value="1" <?php checked( $mobile ) ?>>
			<?php
				$hide_on_screen = '<input type="number" min="1" step="1" class="small-text" name="wp_subscribe_options[popup_triggers][hide_on_screen]" value="'.$screen.'">';
				printf( __( 'Show popup on screens larger than %s pixels.', 'wp-subscribe' ), $hide_on_screen );
			?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Note: popup will only appear once for each visitor until the cookie expires', 'wp-subscribe') ?>
	</p>

	<p class="wp-subscribe-field">
		<label for="wp_subscribe_cookie_expiration"><?php esc_html_e( 'Cookie expiration:', 'wp-subscribe') ?>
			<br />
			<input type="number" min="0" max="365" step="1" class="small-text" name="wp_subscribe_options[cookie_expiration]" value="<?php echo $this->get('cookie_expiration') ?>" id="wp_subscribe_cookie_expiration">
			<?php esc_html_e( 'days', 'wp-subscribe') ?> <span class="description">(<?php esc_html_e( 'Set to 0 to create cookies that last only for one browser session.', 'wp-subscribe') ?>)</span>
		</label>
	</p>

	<p>
		<?php esc_html_e( 'Clear cookies for all visitors:', 'wp-subscribe' ) ?>
		<br />
		<a href="#" class="button-secondary" id="wp_subscribe_regenerate_cookie" title="<?php esc_attr_e( 'Click this button to make the popup show for all visitors once, even for those who already saw it.', 'wp-subscribe' ) ?>"><?php esc_html_e( 'Generate new cookies', 'wp-subscribe' ) ?></a>
		<input type="hidden" name="wp_subscribe_options[cookie_hash]" id="cookiehash" value="<?php echo $this->get('cookie_hash') ?>">
		<span id="cookies-cleared"><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Please save the options to apply changes.', 'wp-subscribe') ?></span>
	</p>

	<p class="submit">
		<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-subscribe' ) ?>">
	</p>

</div><!-- /popup-trigger-tab -->
