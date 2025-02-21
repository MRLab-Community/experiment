<?php
// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

$structure = WPF()->post->get_topic_fields_structure( false, [], true );
$fields    = WPF()->post->get_fields( false, 'topic' );
?>
<h1><?php _e( 'Structure', 'wpforo_tcf' ) ?></h1>
<div class="wpftcf-structure">
	<?php
	if( $structure && is_array( $structure ) ) {
		echo '<div class="wpftcf-sortable wpftcf-sortable-rows">';
		foreach( $structure as $row ) {
			echo( '<div class="wpftcf-row ' . ( count( $row ) >= 2 ? 'wpftcf-cols-has-full' : '' ) . '" data-cols="' . count( $row ) . '">
                    <div class="wpftcf-row-panel">
                        <div class="wpftcf-row-panel-title">- ' . __( 'row', 'wpforo_tcf' ) . ' -</div>
                        <div class="wpftcf-row-panel-actions">
                            <div class="wpftcf-del-row" title="' . __( 'Delete This Row', 'wpforo_tcf' ) . '"><span class="dashicons dashicons-dismiss"></span></div>
                        </div>
                    </div>
                    <div class="wpftcf-row-body">
                        <div class="wpftcf-sortable wpftcf-sortable-cols">' );
			if( $row && is_array( $row ) ) {
				foreach( $row as $col ) {
					echo( '<div class="wpftcf-col">
                                        <div class="wpftcf-col-panel">
                                            <div class="wpftcf-col-panel-title">- ' . __( 'col', 'wpforo_tcf' ) . ' -</div>
                                            <div class="wpftcf-col-panel-actions">
                                                <div class="wpftcf-del-col" title="' . __( 'Delete This Column', 'wpforo_tcf' ) . '"><span class="dashicons dashicons-dismiss"></span></div>
                                            </div>
                                        </div>
                                        <div class="wpftcf-place wpftcf-fields wpftcf-sortable wpftcf-sortable-fields">' );
					if( $col && is_array( $col ) ) {
						foreach( $col as $field ) {
							$field = WPF()->post->get_field( $field, 'topic' );
							if( $field ) {
								WPF_TCF()->print_field( $field, 'topic' );
								unset( $fields[ $field['fieldKey'] ] );
							}
						}
					}
					echo( '</div></div>' );
				}
			}
			echo( '</div>' );
			echo( '<div class="wpftcf-add-new-col" title="' . __( 'Add New Column', 'wpforo_tcf' ) . '"><span class="dashicons dashicons-plus-alt2"></span></div>' );
			echo( '</div>' );
			echo( '</div>' );
		}
		echo '</div>';
	}
	?>
    <div class="wpftcf-add-new-row" title="<?php _e( 'Add New Row', 'wpforo_tcf' ) ?>">
        <span class="dashicons dashicons-plus-alt2"></span>
    </div>

    <div class="wpftcf-structure-actions">
        <div class="wpftcf-save-structure button button-primary" title="<?php _e( 'Save All Structure Into Database', 'wpforo_tcf' ) ?>" disabled>
            <span class="dashicons dashicons-editor-table"></span>
            <span class="wpftcf-save-button-text"><?php _e( 'Saved', 'wpforo_tcf' ) ?></span>
        </div>
    </div>
</div>

<br>
<h1 style="padding-bottom: 5px;">Inactive Fields</h1>
<p style="font-size: 14px;margin: 0 0 5px 0;"><?php _e( 'If you want to remove any field, just drag and drop them from the form above to this "Inactive Fields" area.', 'wpforo_tcf' ) ?></p>
<div class="wpftcf-place wpftcf-base wpftcf-inactive wpftcf-sortable wpftcf-sortable-fields">
	<?php
	foreach( $fields as $key => $field ) {
		WPF_TCF()->print_field( $field, 'topic' );
	}
	?>
</div>
