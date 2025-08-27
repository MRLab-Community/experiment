<?php
/**
 * MailPoet Subscription
 */

class WPS_Subscription_MailPoet extends WPS_Subscription_Base {

	public function get_lists( $api_key ) {

		return array();
	}

    public function subscribe( $identity, $options ) {

		if ( !defined('WYSIJA') ) {
            throw new Exception( esc_html__( 'The MailPoet plugin is not found on your website.', 'wp-subscribe' ) );
        }

		$userModel = WYSIJA::get('user','model');
        $userListModel = WYSIJA::get('user_list','model');
        $manager = WYSIJA::get('user','helper');
		$listId = $options['list_id'];

		$subscriber = $userModel->getOne( false, array(
			'email' => $identity['email']
		) );

		$customs = array();
		if ( !empty( $identity['name'] ) ) {
			$customs['firstname'] = $identity['name'];
		}

		// if user exists.
		if ( !empty( $subscriber ) ) {

			$subscriberId = intval( $subscriber['user_id'] );

            // adding the user to the specified list if the user has not been yet added
            $lists = $userListModel->get_lists( array( $subscriberId ) );

            if ( !isset( $lists[$subscriberId] ) || !in_array( $listId, $lists[$subscriberId] ) ) {
                $manager->addToList( $listId, array( $subscriberId ) );
            }

            if ( isset( $customs['firstname'] ) || isset( $customs['lastname'] ) ) {

                $modelUser = WYSIJA::get('user', 'model');

                if ( isset( $customs['firstname'] ) ) $customs['firstname'] = trim( $customs['firstname'] );
                if ( empty( $customs['firstname'] ) ) $customs['firstname'] = $subscriber['firstname'];

                $modelUser->update($customs, array( 'user_id' => $subscriberId ));
            }

            return array('status' =>  'subscribed');
		}

		// if new user
		$ip = $manager->getIP();
		$double_optin = isset( $options['double_optin'] ) && $options['double_optin'] ? true : false;
		$userData = array(
            'email' => $identity['email'],
            'status' => $double_optin ? 0 : 1,
            'ip' => $ip,
            'created_at' => time()
        );

		if ( !empty( $identity['name'] ) ) {
			$userData['firstname'] = $identity['name'];
		}

        $subscriberId = $userModel->insert( $userData );

		// adds custom fields
        WJ_FieldHandler::handle_all( $customs, $subscriberId );

		if ( !$subscriberId ) {
            throw new Exception ( '[subscribe]: Unable to add a subscriber.' );
        }

        // adds the user the the specified list
        $manager->addToList( $listId, array( $subscriberId ) );

		return array(
			'status' => 'subscribed'
		);
	}

	public function get_fields() {

		if ( ! defined( 'WYSIJA' ) ) {

			$fields = array(
				'mailpoet_raw' => array(
					'id'    => 'mailpoet_raw',
					'name'  => 'mailpoet_raw',
					'type'  => 'raw',
					'content' => array( $this, 'raw_content' )
				),
			);
		}
		else {

			$lists = array();
			$model_list = WYSIJA::get( 'list', 'model' );
	        foreach( $model_list->getLists() as $item ) {
	            $lists[ $item['list_id'] ] = $item['name'];
	        }

			$fields = array(

				'mailpoet_list_id' => array(
					'id'    => 'mailpoet_list_id',
					'name'  => 'mailpoet_list_id',
					'type'  => 'select',
					'title' => esc_html__( 'MailPoet List', 'wp-subscribe' ),
					'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + $lists,
					'is_list'  => true
				),

				'mailpoet_double_optin' => array(
					'id'    => 'mailpoet_double_optin',
					'name'  => 'mailpoet_double_optin',
					'type'  => 'checkbox',
					'title' => esc_html__( 'Send double opt-in notification', 'wp-subscribe' )
				)
			);
		}

		return $fields;
	}

	public function raw_content() {

		esc_html_e( 'The MailPoet plugin is not found on your website. Emails will not be saved.', 'wp-subscribe' );
	}
}
