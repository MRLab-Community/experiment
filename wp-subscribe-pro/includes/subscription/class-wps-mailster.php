<?php

class WPS_Subscription_Mailster extends WPS_Subscription_Base {

    /** @var string */
    private $error_message;

    public function get_lists( $api_key ) {
        return array();
    }

    public function init() {
        if ( ! defined( 'MAILSTER_VERSION' ) ) {
            throw new Exception( esc_html__( 'The Mailster plugin is not found on your website.', 'wp-subscribe' ) );
        }

        if ( ! class_exists( 'MailsterSubscribers' ) || ! ( mailster( 'subscribers' ) instanceof MailsterSubscribers ) ) {
            throw new Exception( esc_html__( 'Unable to connect with the Mailster Subscribers Manager. Your version of Mailster plugin is not supported. Please contact EverPress support.', 'wp-subscribe' ) );
        }

        return mailster( 'subscribers' );
    }

    public function subscribe( $identity, $options ) {
        $manager = $this->init();
		$listId = $options['list_id'];

		$customs = array(
            'email' => $identity['email'],
        );
		if ( !empty( $identity['name'] ) ) {
			$customs['firstname'] = $identity['name'];
		}
        if ( !empty( $identity['family'] ) ) {
			$customs['lastname'] = $identity['family'];
		}
        if ( empty( $customs['name'] ) && !empty( $identity['display_name'] ) ) {
			$customs['firstname'] = $identity['display_name'];
        }

        $subscriber = $manager->get_by_mail( $identity['email'] );
        if ( ! empty( $subscriber ) ) {

			$lists = $manager->get_lists( $subscriber->ID, true );
            if ( !in_array( $listId, $lists ) ) {
                $manager->assign_lists( $subscriber->ID, $listId, false );
            }

            $manager->update( $customs );

            return array( 'status' =>  $subscriber->status ? 'subscribed' : 'pending' );
        }

        $ip = mailster_option('track_users') ? mailster_get_ip() : NULL;

        $double_optin = isset( $options['double_optin'] ) && $options['double_optin'] ? true : false;
        $customs['status'] = $double_optin ? 0 : 1;

        $result = $manager->add( $customs, false );
        if ( is_wp_error( $result ) ) {
           throw new Exception ( '[subscribe]: ' . $result->get_error_message() );
        }

        $manager->assign_lists( $result, $listId, true );

        return array( 'status' => $double_optin ? 'pending' : 'subscribed' );
    }

    public function get_fields() {
        if ( ! defined( 'MAILSTER_VERSION' ) ) {
            $this->error_mesage = esc_html__( 'The Mailster plugin is not found on your website.', 'wp-subscribe' );

            return array(
                'mailster_raw' => array(
					'id'    => 'mailster_raw',
					'name'  => 'mailster_raw',
					'type'  => 'raw',
					'content' => array( $this, 'raw_content' )
				),
            );
        }

        $lists = array();
        $model_list = mailster( 'lists' )->get();
        
        foreach( $model_list as $item ) {
            $lists[ $item->ID ] = $item->name;
        }

        $lists = array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + $lists;

        return array(
            'mailster_list_id' => array(
                'id'      => 'mailster_list_id',
                'name'    => 'mailster_list_id',
                'type'    => 'select',
                'title'   => esc_html__( 'Mailster List', 'wp-subscribe' ),
                'options' => $lists,
                'is_list' => true
            ),

            'mailster_double_optin' => array(
                'id'    => 'mailster_double_optin',
                'name'  => 'mailster_double_optin',
                'type'  => 'checkbox',
                'title' => esc_html__( 'Send double opt-in notification', 'wp-subscribe' )
            )
        );
    }

    public function raw_content() {
        echo $this->error_mesage;
    }
}
