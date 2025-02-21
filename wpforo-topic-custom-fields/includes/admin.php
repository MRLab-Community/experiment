<?php
// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;
if( ! wpforo_current_user_is( 'admin' ) ) exit;
?>
<div id="wpf-admin-wrap" class="wrap">
	<?php
	if( wpfval( $_GET, 'wpfaction' ) === 'wpforotcf_save_form' ) {
		$formid      = (int) wpfval( $_GET, 'formid' );
		$tabs        = [
			'settings'  => '<span class="dashicons dashicons-admin-generic"></span>&nbsp;' . __( 'General', 'wpforo_tcf' ),
			'fields'    => '<span class="dashicons dashicons-list-view"></span>&nbsp;' . __( 'Fields', 'wpforo_tcf' ),
			'structure' => '<span class="dashicons dashicons-editor-table"></span>&nbsp;' . __( 'Structure', 'wpforo_tcf' ),
		];
		$current_tab = wpfval( $_GET, 'tab' );
		if( ! $current_tab ) $current_tab = 'settings';
		?>
        <div class="wrap">
            <h2 style="padding:20px 0 10px;line-height: 20px;">
				<?php echo( $formid ? sprintf( __( 'Edit Form #%1$d', 'wpforo_tcf' ), $formid ) : __( 'Add New Form', 'wpforo_tcf' ) ); ?>
            </h2>
        </div>
		<?php WPF()->notice->show() ?>
		<?php do_action( 'wpforo_settings_page_top' ) ?>
		<?php
		echo '<h2 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $label ) {
			if( $formid ) {
				printf(
					'<a class="nav-tab %1$s" href="?page=%2$s&tab=%3$s&wpfaction=%4$s&formid=%5$d">%6$s</a>',
					( $tab === $current_tab ) ? 'nav-tab-active' : '',
					wpforo_prefix_slug( 'tcf' ),
					$tab,
					'wpforotcf_save_form',
					$formid,
					$label
				);
			} else {
				printf(
					'<a class="nav-tab nav-tab-disabled %1$s" title="%2$s">%3$s</a>',
					( $tab === $current_tab ) ? 'nav-tab-active' : '',
					( $tab === $current_tab ) ? '' : __( 'The tabs are inactive until the form is not saved', 'wpforo_tcf' ),
					$label
				);
			}
		}
		echo '</h2>';
		?>
        <div class="wpftcf-wrap wpf-info-bar">
			<?php
			switch( $current_tab ) {
				case 'structure':
					include_once( 'topic-form.php' );
				break;
				case 'fields':
					include_once( 'fields.php' );
				break;
				default:
					include_once( 'settings.php' );
				break;
			}
			?>
        </div>
	<?php } else { ?>
        <div class="wrap">
            <h2 style="padding:20px 0 10px;line-height: 20px;">
				<?php _e( 'Forms', 'wpforo_tcf' ) ?>
                <a href="<?php echo admin_url( 'admin.php?page=' . wpforo_prefix_slug( 'tcf' ) . '&wpfaction=wpforotcf_save_form' ) ?>" class="add-new-h2">
					<?php _e( 'Add New', 'wpforoad' ) ?>
                </a>
            </h2>
        </div>
		<?php WPF()->notice->show() ?>
		<?php do_action( 'wpforo_settings_page_top' ) ?>
		<?php WPF_TCF()->list_table->display();
	} ?>
</div>
