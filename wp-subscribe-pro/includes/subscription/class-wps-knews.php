<?php
/**
 * Knews Subscription
 */

class WPS_Subscription_Knews extends WPS_Subscription_Base {

	public function get_lists( $api_key ) {

		return array();
	}

    public function subscribe( $identity, $options ) {

		if ( !class_exists('KnewsPlugin') ) {
            throw new Exception( esc_html__( 'The Knews plugin is not found on your website.', 'wp-subscribe' ) );
        }

		global $Knews_plugin;

		$customs = array();
		if ( !empty( $identity['name'] ) ) {
			$customs['name'] = $identity['name'];
		}
        if ( !empty( $identity['family'] ) ) {
			$customs['surname'] = $identity['family'];
		}
        if ( empty( $customs['name'] ) && !empty( $identity['display_name'] ) ) {
			$customs['name'] = $identity['display_name'];
		}

		$double_optin = isset( $options['double_optin'] ) && $options['double_optin'] ? false : true;
		$result = $Knews_plugin->add_user_db( 0, $identity['email'], $options['list_id'], 'en', 'en_US', $this->mapExtraFields($customs), $double_optin );

		if ( 4 == $result ) {
            throw new Exception ( '[subscribe]: Unable to add a subscriber.' );
        }

		return array(
			'status' => 'subscribed'
		);
	}

	protected function mapExtraFields( $customFields ) {
        global $wpdb;

        $sql = "SELECT * FROM " . $wpdb->prefix . 'knewsextrafields';
		$rows = $wpdb->get_results( $sql );

        $result = array();
        foreach( $rows as $row ) {
            if ( !isset( $customFields[$row->name] ) ) continue;
            $result[$row->id] = $customFields[$row->name];
        }

        return $result;
    }

	public function get_fields() {

		if ( ! class_exists( 'KnewsPlugin' ) ) {

			$fields = array(
				'knews_raw' => array(
					'id'    => 'knews_raw',
					'name'  => 'knews_raw',
					'type'  => 'raw',
					'content' => array( $this, 'raw_content' )
				),
			);
		}
		else {

			global $Knews_plugin;

			$fields = array(
				'knews_list_id' => array(
					'id'    => 'knews_list_id',
					'name'  => 'knews_list_id',
					'type'  => 'text',
					'title' => esc_html__( 'KNews List ID', 'wp-subscribe' ),
					'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + $Knews_plugin->tellMeLists()
				),

				'knews_double_optin' => array(
					'id'    => 'knews_double_optin',
					'name'  => 'knews_double_optin',
					'type'  => 'checkbox',
					'title' => esc_html__( 'Send double opt-in notification', 'wp-subscribe' )
				)
			);
		}

		return $fields;
	}

	public function raw_content() {

		echo esc_html__( 'The KNews plugin is not found on your website. Emails will not be saved.', 'wp-subscribe' );
	}
}
