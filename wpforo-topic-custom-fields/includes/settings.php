<?php
$form    = WPF_TCF()->form->decode( WPF_TCF()->form->get_form( (int) wpfval( $_GET, 'formid' ) ) );
$display = ( $form['is_default'] ? 'style="display: none"' : 'style="display: flex"' );
?>
<form id="wpftcf-form" method="post">
	<?php
	if( $form['formid'] ) {
		wp_nonce_field( 'wpforotcf-edit-form' );
		echo '<input type="hidden" name="wpfaction" value="wpforotcf_edit_form">';
	} else {
		wp_nonce_field( 'wpforotcf-add-form' );
		echo '<input type="hidden" name="wpfaction" value="wpforotcf_add_form">';
	}
	?>
    <input type="hidden" name="form[formid]" value="<?php echo $form['formid'] ?>">

    <div class="wpftcf-form-title">
        <input id="wpftcf-form-title" type="text" name="form[title]" value="<?php echo $form['title'] ?>" placeholder="<?php _e( 'Enter form title here', 'wpforo_tcf' ); ?>">
    </div>
    <div class="wpftcf-form-is_default">
        <label><?php _e( 'Is Default', 'wpforo_tcf' ); ?></label>
        <div class="wpf-switch-field">
            <input type="radio" value="1" name="form[is_default]" id="wpftcf-form-is_default_1" <?php wpfo_check( $form['is_default'], true ); ?>><label for="wpftcf-form-is_default_1"><?php _e( 'Yes', 'wpforo_tcf' ) ?></label> &nbsp;
            <input type="radio" value="0" name="form[is_default]" id="wpftcf-form-is_default_0" <?php wpfo_check( $form['is_default'], false ); ?>><label for="wpftcf-form-is_default_0"><?php _e( 'No', 'wpforo_tcf' ) ?></label>
        </div>
    </div>
    <div class="wpftcf-form-forumids-groupids">
        <div class="wpftcf-form-forumids" <?php echo $display; ?>>
            <label for="wpftcf-form-forumids"><?php _e( 'Forums', 'wpforo_tcf' ); ?></label>
            <select name="form[forumids][]" id="wpftcf-form-forumids" multiple <?php echo( $form['is_default'] ? '' : 'required' ) ?>>
				<?php WPF()->forum->tree( 'select_box', false, $form['forumids'], true, WPF_TCF()->form->get_all_used_forumids( $form['locale'], $form['formid'] ) ); ?>
            </select>
            <p class="wpf-info" style="padding: 5px 0 3px 0"><?php _e( 'The disabled items are either used in other forms or they are categories. Topics can not be attached to categories.', 'wpforo_tcf' ) ?></p>
            <p class="wpf-info"><?php _e( 'Hold the CTRL key and click the items in a list to choose them. Click all the items you want to select. They don\'t have to be next to each other.', 'wpforo_tcf' ) ?></p>
        </div>
        <div class="wpftcf-form-groupids" <?php echo $display; ?>>
            <label for="wpftcf-form-groupids"><?php _e( 'User Groups', 'wpforo_tcf' ); ?></label>
            <select name="form[groupids][]" id="wpftcf-form-groupids" multiple style="min-height: 120px;">
				<?php echo WPF()->usergroup->get_selectbox( $form['groupids'] ); ?>
            </select>
            <p class="wpf-info" style="padding: 3px 0"><?php _e( 'You can choose the usergroups who can see the custom fields of this form. If none of usergroups are selected, all usergroups will see the custom fields.', 'wpforo_tcf' ) ?></p>
            <p class="wpf-info" style="padding: 3px 0"><?php _e( 'IMPORTANT: If some usergroups are selected but the Admin usergroup is not selected, you\'ll not see the custom fields on the front-end. You should login as a user of the selected usergroups to see and check the custom form and fields.', 'wpforo_tcf' ) ?></p>
        </div>
    </div>
    <div class="wpftcf-form-locale">
        <label for="wpftcf-form-locale"><?php _e( 'Language', 'wpforo_tcf' ); ?></label>
		<?php wp_dropdown_languages( [ 'id' => 'wpftcf-form-locale', 'name' => 'form[locale]', 'selected' => $form['locale'] ] ); ?>
    </div>
    <div class="wpftcf-form-status">
        <label><?php _e( 'Status', 'wpforo_tcf' ); ?></label>
        <div class="wpf-switch-field">
            <input type="radio" value="1" name="form[status]" id="wpftcf-form-status_1" <?php wpfo_check( $form['status'], true ); ?>><label for="wpftcf-form-status_1"><?php _e( 'Enabled', 'wpforo_tcf' ) ?></label> &nbsp;
            <input type="radio" value="0" name="form[status]" id="wpftcf-form-status_0" <?php wpfo_check( $form['status'], false ); ?>><label for="wpftcf-form-status_0"><?php _e( 'Disabled', 'wpforo_tcf' ) ?></label>
        </div>
    </div>

    <div class="wpftcf-form-submit">
        <div></div>
        <input type="submit" value="<?php _e( 'Save', 'wpforo_tcf' ) ?>" class="button button-primary">
    </div>
</form>
