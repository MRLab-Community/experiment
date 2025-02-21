<?php
// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

$fields = WPF()->post->get_fields( false, 'topic' );
?>
<h1>Topic Fields Manager</h1>
<div class="wpftcf-fields-wrap">
    <div class="wpftcf-place wpftcf-base wpftcf-fields wpftcf-sortable wpftcf-sortable-fields">
		<?php
		foreach( $fields as $key => $field ) {
			WPF_TCF()->print_field( $field, 'topic', true );
		}
		?>
    </div>

    <div class="wpftcf-add-new-field" title="<?php _e( 'Add New Field', 'wpforo_tcf' ) ?>">
        <span class="dashicons dashicons-plus-alt2"></span>
    </div>

    <div class="wpftcf-fields-actions">
        <div class="wpftcf-save-fields button button-primary" title="<?php _e( 'Save Modified Fields', 'wpforo_tcf' ) ?>" disabled>
            <span class="dashicons dashicons-list-view"></span>
            <span class="wpftcf-save-button-text"><?php _e( 'Saved', 'wpforo_tcf' ) ?></span>
        </div>
    </div>
</div>
<br>
<h1 style="display: flex; align-items: center;"><span class="dashicons dashicons-trash"></span> Trashed Fields</h1>
<div class="wpftcf-place wpftcf-base wpftcf-trash wpftcf-sortable wpftcf-sortable-fields">
	<?php
	$current_form = WPF_TCF()->form->get_current();
	foreach( $current_form['fields_trash'] as $key => $field ) {
		WPF_TCF()->print_field( $field, 'topic', true );
	}
	?>
</div>
