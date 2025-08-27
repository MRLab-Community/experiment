<?php
/**
 * ConvertKit Subscription
 */

class WPS_Subscription_ConvertKit extends WPS_Subscription_Base {

	/**
	 * API Key
	 * @var string
	 */
	public $api_key;

	/**
	 * API URL
	 * @var string
	 */
	public $api_url;

	public function init( $api_key ) {
		require_once wps()->plugin_dir() . '/includes/subscription/libs/convertkit.php';
		return new WPS_ConvertKitApi( $api_key );
	}

	public function get_lists( $api_key ) {
		$convertkit = $this->init( $api_key );
		$forms = $convertkit->getForms();

		if(!empty($forms)) {
			$forms = json_decode($forms, 1);

			if( isset($forms['error_message']) ) {
				throw new Exception( $forms['error_message'] );
			}

			$lists = array();
			foreach($forms['forms'] as $form) {
				$lists[ $form['id'] ] = $form['name'];
			}

			return $lists;
		}
	}

	public function subscribe( $identity, $options ) {

		$convertkit = $this->init( $options['api_key'] );
		$subscription = $convertkit->subscribeToAForm($options['form_id'],$identity['email'],$identity['name']);
		$subscription_details = json_decode($subscription, 1);
		
		$subscription_details = $subscription_details['subscription'];

		return array(
			'status' => 'subscribed'
		);
	}

	public function get_fields() {

		$fields = array(
			'convertkit_api_key' => array(
				'id'    => 'convertkit_api_key',
				'name'  => 'convertkit_api_key',
				'type'  => 'text',
				'title' => esc_html__( 'ConvertKit API Key', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The API Key of your ConvertKit account, available in Account Settings.', 'wp-subscribe' ),
				'link' => '//app.convertkit.com/account/edit'
			),

			'convertkit_form_id' => array(
				'id'    => 'convertkit_form_id',
				'name'  => 'convertkit_form_id',
				'type'  => 'select',
				'title' => esc_html__( 'ConvertKit Form', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select Form', 'wp-subscribe' ) ) + wps_get_service_list('convertkit'),
				'is_list'  => true
			),
		);

		return $fields;
	}
}
