<div class="wrap wp-subscribe">

	<h2><?php esc_html_e( 'WP Subscribe Pro Settings', 'wp-subscribe' ) ?></h2>

	<form method="post" action="options.php" id="wp_subscribe_options_form">

		<?php $enable_popup = !$this->get('enable_popup') ? ' style="display: none;"' : '' ?>
		<h2 class="nav-tab-wrapper wps-nav-tab-wrapper">
	    	<a href="#" class="nav-tab nav-tab-active" data-rel=".wps-popup-options"><?php esc_html_e( 'Popup', 'wp-subscribe' ); ?></a>
	    	<a href="#" class="nav-tab ifpopup" id="popup-content-tab" data-rel=".wps-popup-content-options"<?php echo $enable_popup ?>><?php esc_html_e( 'Popup Content', 'wp-subscribe' ) ?></a>
	    	<a href="#" class="nav-tab ifpopup" data-rel=".wps-popup-trigger-options"<?php echo $enable_popup ?>><?php esc_html_e( 'Popup Triggers', 'wp-subscribe' ) ?></a>
	    	<a href="#" class="nav-tab" data-rel=".wps-post-options"><?php esc_html_e( 'Single Posts', 'wp-subscribe' ) ?></a>
		</h2>

		<div class="wps-tabs-wrapper">
		<?php
			include_once 'tab-popup.php';
			include_once 'tab-popup-content.php';
			include_once 'tab-popup-trigger.php';
			include_once 'tab-single-posts.php';
		?>
		</div><!-- /.wps-tabs-wrapper -->

		<?php settings_fields( 'wp_subscribe-settings-group' ) ?>

	</form>

	<?php wps_popup_html() ?>

</div>
