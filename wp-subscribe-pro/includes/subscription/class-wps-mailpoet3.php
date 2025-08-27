<?php

use \MailPoet\Models\Segment;
use \MailPoet\Models\Subscriber;
use MailPoet\Models\Setting;

class WPS_Subscription_MailPoet3 extends WPS_Subscription_Base {

    public function get_lists() {
        $segments = Segment::getSegmentsWithSubscriberCount();
        $lists = array();

        foreach ( $segments as $segment ) {
            $lists[ $segment['id'] ] = $segment['name'];
        }

        return $lists;
    }

    public function subscribe( $identity, $options ) {
        if ( ! class_exists( '\MailPoet\Models\Subscriber' ) ) {
            throw new Exception( esc_html__( 'The MailPoet 3 plugin is not found on your website.', 'wp-subscribe' ) );
        }

        $subscriber_data = array(
            'email' => $identity['email'],
        );

        if ( ! empty( $identity['name'] ) ) {
            $subscriber_data['first_name'] = $identity['name'];
        }

        $subscriber = Subscriber::subscribe( $subscriber_data, array( $options['list_name'] ) );

        return array(
            'status' => Subscriber::STATUS_SUBSCRIBED === $subscriber->status ? 'subscribed' : 'pending',
        );
    }

    public function get_fields() {
        if ( ! class_exists( '\MailPoet\Models\Subscriber' ) ) {
            return array(
				'mailpoet3_raw' => array(
					'id'    => 'mailpoet3_raw',
					'name'  => 'mailpoet3_raw',
					'type'  => 'raw',
					'content' => array( $this, 'raw_content' ),
				),
			);
        }

        return array(
            'mailpoet3_list_name' => array(
                'id'    => 'mailpoet3_list_name',
                'name'  => 'mailpoet3_list_name',
                'type'  => 'select',
                'title' => esc_html__( 'MailPoet 3 List', 'wp-subscribe' ),
                'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('mailpoet3'),
                'is_list' => true
            ),
            'mailpoet3_nullarg' => array(
                'id' => 'mailpoet3_nullarg',
                'name' => 'mailpoet3_nullarg',
                'type' => 'hidden',
                'value' => '',
            ),
        );
    }

    public function raw_content() {
        printf( esc_html__( 'The MailPoet 3 plugin is %1$srequired%2$s but not found on your website.', 'wp-subscribe' ), '<strong>', '</strong>' );
    }
}
