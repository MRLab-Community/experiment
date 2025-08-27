<?php
/**
 * SendGrid Subscription
 */

class WPS_Subscription_SendGrid extends WPS_Subscription_Base {

	public function init( $api_key ) {

		require_once wps()->plugin_dir() . '/includes/subscription/libs/sendgrid/SendGrid.php';
		return new \SendGrid( $api_key );
	}

	public function get_lists( $api_key ) {

		try {
			$api = $this->init( $api_key );
			$result = $api->client->contactdb()->lists()->get();
			$result = $api->handle_response( $result );

			$lists = array();
			foreach( $result->lists as $list ) {
				$lists[ $list->id ] = $list->name;
			}

			return $lists;
		}
		catch( Exception $e ) {
			return array();
		}
	}

    public function subscribe( $identity, $options ) {

		$vars = array();
		$email = $identity['email'];
		$listId = $options['list_id'];

		if ( !empty( $identity['name'] ) ) {
			$vars['first_name'] = $identity['name'];
		}
		$vars['email'] = $email;

		$api = $this->init( $options['api_key'] );

		// Search for existance.
		$response = $api->client->contactdb()->recipients()->search()->get(null, array('email' => $email));
		$response = $api->handle_response( $response );

		// aleary exists
        if ( !empty( $response->recipients ) ) {

            $subscriberId = isset( $response->recipients[0]->id ) ? $response->recipients[0]->id : 0;

            // adding to a list
            if ( $subscriberId ) {
                $response = $api->client->contactdb()->lists()->_($listId)->recipients()->_($subscriberId)->post();
                $api->handle_response( $response, 201 );
            }

            return array( 'status' => 'subscribed' );
        }

		// adding a new contact
        $response = $api->client->contactdb()->recipients()->post( array( $vars ) );
        $response = $api->handle_response( $response, 201 );

        $subscriberId = isset( $response->persisted_recipients[0] ) ? $response->persisted_recipients[0] : 0;
        if ( !$subscriberId ) {
            throw new Exception( esc_html__( 'Unable to add a new user. Please contact MyThemeShop support.','wp-subscribe' ) );
        }

        // adding to a list
        $response = $api->client->contactdb()->lists()->_($listId)->recipients()->_($subscriberId)->post();
        $api->handle_response( $response, 201 );

        return array( 'status' => 'subscribed' );
	}

	public function get_fields() {

		$fields = array(
			'sendgrid_api_key' => array(
				'id'    => 'sendgrid_api_key',
				'name'  => 'sendgrid_api_key',
				'type'  => 'text',
				'title' => esc_html__( 'SendGrid API Key', 'wp-subscribe' ),
				'desc'  => esc_html__( 'Your SendGrid API key. Grant Full Access for Mail Send and Marketing Campaigns in settings of your API key.', 'wp-subscribe' ),
				'link'  => 'https://app.sendgrid.com/settings/api_keys'
			),

			'sendgrid_list_id' => array(
				'id'    => 'sendgrid_list_id',
				'name'  => 'sendgrid_list_id',
				'type'  => 'select',
				'title' => esc_html__( 'SendGrid List', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('sendgrid'),
				'is_list'  => true
			)
		);

		return $fields;
	}
}
