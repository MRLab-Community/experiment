<?php
/**
 * MailRelay Subscription
 */

class WPS_Subscription_MailRelay extends WPS_Subscription_Base {

	public function init( $api_key, $username ) {

		require_once wps()->plugin_dir() . '/includes/subscription/libs/class-mailrelay-api.php';
		return new MailRelay_API( $api_key, $username );
	}

	public function get_lists( $api_key, $username ) {

		$api = $this->init( $api_key, $username );
		$result = $api->getGroups();

		$lists = array();
		foreach( $result as $list ) {
			$lists[ $list['id'] ] = $list['name'];
		}

		return $lists;
	}

	public function subscribe( $identity, $options ) {

		$api = $this->init( $options['api_key'], $options['host'] );

		$fields = array(
			'email'  => $identity['email'],
			'groups' => array( $options['group_id'] )
		);
		if ( ! empty( $identity['name'] ) ) {
			$fields['name'] = $identity['name'];
		}

		$result = $api->addSubscriber( $fields );

		if ( ! empty( $result['errors'] ) ) {
			if ( empty( $result['errors']['email'][0] ) || 'Subscriber already exists.' !== $result['errors']['email'][0] ) {
				throw new Exception( $result['error'] );
			}
		}

		return array(
			'status' => 'subscribed',
		);
	}

	public function get_fields() {

		$fields = array(
			'mailrelay_api_key' => array(
				'id'    => 'mailrelay_api_key',
				'name'  => 'mailrelay_api_key',
				'type'  => 'text',
				'title' => esc_html__( 'MailRelay API key', 'wp-subscribe' ),
			),

			'mailrelay_host' => array(
				'id'    => 'mailrelay_host',
				'name'  => 'mailrelay_host',
				'type'  => 'text',
				'title' => esc_html__( 'MailRelay host address', 'wp-subscribe' ),
			),

			'mailrelay_group_id' => array(
				'id'    => 'mailrelay_group_id',
				'name'  => 'mailrelay_group_id',
				'type'  => 'select',
				'title' => esc_html__( 'MailRelay Group', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('mailrelay'),
				'is_list' => true
			),
		);

		return $fields;
	}
}
