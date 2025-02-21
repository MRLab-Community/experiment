<?php
add_action( 'wpforo_core_inited', function() {
	if( version_compare( WPFORO_VERSION, WPFOROTCF_WPFORO_REQUIRED_VERSION, '>=' ) && wpforo_is_module_enabled( WPFOROTCF_FOLDER ) ) {
		if( wpforo_get_option( 'tcf_version', null, false ) !== WPFOROTCF_VERSION ) wpforotcf_activation();
		$GLOBALS['wpforotcf'] = WPF_TCF();
	}
} );

add_action( 'admin_notices', function() {
	if( ! function_exists( 'WPF' ) || ! version_compare( WPFORO_VERSION, WPFOROTCF_WPFORO_REQUIRED_VERSION, '>=' ) ) {
		$class   = 'notice notice-error';
		$message = __( 'IMPORTANT: wpForo Topic Custom Fields plugin is a wpForo extension, please install latest version wpForo plugin.', 'wpforo_tcf' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}
} );

add_action( 'wpforo_admin_menu', function( $parent_slug ) {
	if( version_compare( WPFORO_VERSION, WPFOROTCF_WPFORO_REQUIRED_VERSION, '>=' ) ){
		if( wpforo_is_module_enabled( WPFOROTCF_FOLDER ) && wpforo_current_user_is( 'admin' ) ) {
			add_submenu_page(
				$parent_slug, __( 'Topic Fields', 'wpforo_tcf' ), __( 'Topic Fields', 'wpforo_tcf' ), 'read', wpforo_prefix_slug( 'tcf' ), function() { WPF_TCF()->admin_page(); }
			);
		}
	}
} );

function wpforotcf_activation() {
	global $wpdb;
	$charset_collate = '';
	if( ! empty( $wpdb->charset ) ) $charset_collate = "DEFAULT CHARACTER SET " . $wpdb->charset;
	if( ! empty( $wpdb->collate ) ) $charset_collate .= " COLLATE " . $wpdb->collate;
	$sql = "CREATE TABLE IF NOT EXISTS `" . WPF()->tables->forms . "` (
	  `formid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	  `title` VARCHAR(255),
	  `type` VARCHAR(255) NOT NULL DEFAULT 'topic',
	  `structure` TEXT,
	  `fields` TEXT,
	  `fields_trash` TEXT,
	  `forumids` VARCHAR(1000),
	  `groupids` VARCHAR(255),
	  `locale` VARCHAR(10),
	  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
	  `status` TINYINT(1) NOT NULL DEFAULT 1,
	  PRIMARY KEY (`formid`),
	  KEY (`type`(10), `forumids`(165), `groupids`(10), `locale`(5), `status`)
	) ENGINE=MyISAM $charset_collate";
	if( false === @$wpdb->query( $sql ) ) {
		@$wpdb->query( preg_replace( '#\)\s*ENGINE.*$#isu', ')', $sql ) );
	}

	if( ! WPF_TCF()->form->get_form( [ 'is_default' => true, 'status' => true ] ) ) {
		WPF_TCF()->form->add(
			[
				'title'        => 'Default Form',
				'structure'    => get_option( 'wpftcf_topic_form', [] ),
				'fields'       => get_option( 'wpftcf_fields', [] ),
				'fields_trash' => get_option( 'wpftcf_fields_trash', [] ),
				'is_default'   => true,
			]
		);
	}

	wpforo_update_option( 'tcf_version', WPFOROTCF_VERSION );
}

add_filter( 'wpforo_init_tables', function( $tables ) {
	$tables[] = 'forms';

	return $tables;
} );
