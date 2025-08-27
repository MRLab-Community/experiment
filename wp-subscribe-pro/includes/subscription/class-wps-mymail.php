<?php
/**
 * MyMail Subscription
 */

class WPS_Subscription_MyMail extends WPS_Subscription_Base {

	public function get_lists( $api_key ) {

		return array();
	}

	public function init() {

		if ( !defined('MYMAIL_VERSION') ) {
            throw new Exception( esc_html__( 'The MyMail plugin is not found on your website.', 'wp-subscribe' ) );
        }

        $path = MYMAIL_DIR . '/classes/subscribers.class.php';
        if ( !file_exists( $path ) ) {
            throw new Exception( esc_html__( 'Unable to connect with the MyMail Subscribers Manager. Your version of MyMail plugin is not supported. Please contact OnePress support.', 'wp-subscribe' ) );
        }

        require_once $path;

        if ( !class_exists( 'mymail_subscribers' ) ) {
            throw new Exception( esc_html__( 'Unable to connect with the MyMail Subscribers Manager. Your version of MyMail plugin is not supported. Please contact OnePress support.', 'wp-subscribe' ) );
        }

        return mymail('subscribers');
	}

    public function subscribe( $identity, $options ) {

		$manager = $this->init();
		$listId = $options['list_id'];

		$customs = array();
		if ( !empty( $identity['name'] ) ) {
			$customs['firstname'] = $identity['name'];
		}
        if ( !empty( $identity['family'] ) ) {
			$customs['lastname'] = $identity['family'];
		}
        if ( empty( $customs['name'] ) && !empty( $identity['display_name'] ) ) {
			$customs['firstname'] = $identity['display_name'];
		}

		// if user exists.
		$subscriber = $manager->get_by_mail( $identity['email'] );
		if ( !empty( $subscriber ) ) {

			$lists = $manager->get_lists( $subscriber->ID, true );
            if ( !in_array( $listId, $lists ) ) {
                $manager->assign_lists( $subscriber->ID, $listId, false );
            }

            $manager->update( $customs );

            return array( 'status' =>  'subscribed' );
		}

		// if it's a new subscriber
        $ip = mymail_option('track_users') ? mymail_get_ip() : NULL;

		$double_optin = isset( $options['double_optin'] ) && $options['double_optin'] ? true : false;
        $customs['status'] = $double_optin ? 0 : 1;
        $customs['ip_signup'] = $ip;
        $customs['ip'] = $ip;

        // the method 'add' sends the confirmation email if the status = 0,
        // we need to replace the original confirmation link with our own link,
        // then we turn on the constant MYMAIL_DO_BULKIMPORT to prevent sending the confirmation email
        // in the methid 'add'

        define('MYMAIL_DO_BULKIMPORT', true);

        $result = $manager->add( $customs, false );
        if ( is_wp_error( $result ) ) {
           throw new Exception ( '[subscribe]: ' . $result->get_error_message() );
        }

        $manager->assign_lists( $result, $listId, true );

        return array( 'status' => 'subscribed' );
	}

	public function get_fields() {

		$error = false;
		$fields = array();

		if ( ! defined( 'MYMAIL_VERSION' ) || ! defined( 'MYMAIL_DIR' ) ) {
			$error = esc_html__( 'The MyMail plugin is not found on your website.', 'wp-subscribe' );
		}
		else {
			$path = MYMAIL_DIR . '/classes/lists.class.php';
			if ( ! file_exists( $path ) ) {
				$error = esc_html__( 'Unable to connect with the MyMail Lists Manager. Your version of MyMail plugin is not supported. Please contact OnePress support.', 'wp-subscribe' );
		    }
			else {
				require_once $path;

				if ( ! class_exists( 'mymail_lists' ) ) {
					$error = esc_html__( 'Unable to connect with the MyMail Lists Manager. Your version of MyMail plugin is not supported. Please contact OnePress support.', 'wp-subscribe' );
			    }
			}
		}

		if ( $error ) {

			$this->error = $error;

			$fields = array(
				'mymail_raw' => array(
					'id'    => 'mymail_raw',
					'name'  => 'mymail_raw',
					'type'  => 'raw',
					'content' => array( $this, 'raw_content' )
				),
			);
		}
		else {

			$lists = array();
			$model_list = mymail('lists')->get();
	        foreach( $model_list as $item ) {
	            $lists[ $item->ID ] = $item->name;
	        }

			$lists = array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + $lists;

			$fields = array(
				'mymail_list_id' => array(
					'id'      => 'mymail_list_id',
					'name'    => 'mymail_list_id',
					'type'    => 'select',
					'title'   => esc_html__( 'MyMail List', 'wp-subscribe' ),
					'options' => $lists,
					'is_list' => true
				),

				'mymail_double_optin' => array(
					'id'    => 'mymail_double_optin',
					'name'  => 'mymail_double_optin',
					'type'  => 'checkbox',
					'title' => esc_html__( 'Send double opt-in notification', 'wp-subscribe' )
				)
			);
		}

		return $fields;
	}

	public function raw_content() {

		echo $this->error;
	}
}
