<?php
/**
 * ConstantContact Subscription
 */

use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;

class WPS_Subscription_ConstantContact extends WPS_Subscription_Base {

	public function init( $api_key ) {

		require_once require_once wps()->plugin_dir() . '/includes/subscription/libs/constant-contact/autoload.php';
		return new ConstantContact( $api_key );
	}

	public function get_lists( $api_key, $token ) {

		$api = $this->init( $api_key );
		$result = $api->listService->getLists( $token );

		$lists = array();
		foreach( $result as $id => $list ) {
			$lists[ $list->id ] = $list->name;
		}

		return $lists;
	}

    public function subscribe( $identity, $options ) {

		$api = $this->init( $options['api_key'] );

		$vars = array(
			'status' => 'ACTIVE',
			'email_addresses' => array(
				array( 'email_address' => $identity['email'] )
			),
			'lists' => array(
				array( 'id' => $options['list_id'] )
			)
		);

        if ( !empty( $identity['name'] ) ) {
            $vars['first_name'] = $identity['name'];
        }

		try {
			$api->contactService->addContact( $options['token'], Contact::create($vars) );
		}
		catch( Exception $e ) {

			$message = $e->getErrors();
			$message = $message[0]->error_message;

			if ( false === strpos( $message, 'already exists' ) ) {
                throw new Exception( $message );
            }
		}

		return array(
			'status' => 'subscribed'
		);
	}

	public function get_fields() {

		$fields = array(
			'constantcontact_api_key' => array(
				'id'    => 'constantcontact_api_key',
				'name'  => 'constantcontact_api_key',
				'type'  => 'text',
				'title' => esc_html__( 'Constant Contact API key', 'wp-subscribe' ),
				'link'  => 'http://support2.constantcontact.com/articles/FAQ/1388'
			),

			'constantcontact_token' => array(
				'id'    => 'constantcontact_token',
				'name'  => 'constantcontact_token',
				'type'  => 'text',
				'title' => esc_html__( 'Constant Contact access token', 'wp-subscribe' ),
				'link'  => 'http://support2.constantcontact.com/articles/FAQ/1388'
			),

			'constantcontact_list_id' => array(
				'id'      => 'constantcontact_list_id',
				'name'    => 'constantcontact_list_id',
				'type'    => 'select',
				'title'   => esc_html__( 'Constant Contact List ID', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('constantcontact'),
				'is_list' => true
			)
		);

		return $fields;
	}
}
