<?php
/**
 * GetResponse Subscription
 */

class WPS_Subscription_GetResponse extends WPS_Subscription_Base {

	public function init( $api_key ) {

		require_once wps()->plugin_dir() . '/includes/subscription/libs/getresponse.php';
		return new GetResponse( $api_key );
	}

	public function get_lists( $api_key ) {

		$api = $this->init( $api_key );
		$result = $api->getCampaigns();

		$lists = array();
		if( empty( $result ) ) {
			return $lists;
		}
		foreach( $result as $list ) {
			$lists[ $list->campaignId ] = $list->name;
		}

		return $lists;
	}

	public function subscribe( $identity, $options ) {

		$dataToPass = array (
			'campaign'  => array( 'campaignId' => $options['list_id'] ),
			'email'     => $identity['email'],
			'dayOfCycle' => 0,
		);

		$name = $this->get_fullname( $identity );
		if ( !empty( $name ) ) {
			$dataToPass['name'] = $name;
		}

		$api = $this->init( $options['api_key'] );
		$result = $api->addContact( $dataToPass );

		if( isset( $result->uuid ) ) {

			return array(
				'status' => 'subscribed'
			);
		}

		if( 202 === intval( $api->http_status ) ) {
			return array(
				'status' => 'subscribed'
			);
		}

		throw new Exception( esc_html__( 'Unknown error.', 'wp-subscribe' ) );
	}

	public function get_fields() {

		$fields = array(

			'getresponse_api_key' => array(
				'id'    => 'getresponse_api_key',
				'name'  => 'getresponse_api_key',
				'type'  => 'text',
				'title' => esc_html__( 'GetResponse API Key', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The API key of your GetResponse account.', 'wp-subscribe' ),
				'link'  => 'http://support.getresponse.com/faq/where-i-find-api-key'
			),

			'getresponse_list_id' => array(
				'id'      => 'getresponse_list_id',
				'name'    => 'getresponse_list_id',
				'type'    => 'select',
				'title'   => esc_html__( 'GetResponse List', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('getresponse'),
				'is_list' => true
			)
		);

		return $fields;
	}
}
