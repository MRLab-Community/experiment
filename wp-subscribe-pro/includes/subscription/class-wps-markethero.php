<?php
/**
 * ConvertKit Subscription
 */
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if( ! class_exists( 'WPS_Subscription_Markethero' ) ) {
	class WPS_Subscription_Markethero extends WPS_Subscription_Base {

		public function init( $api_key ) {

			require_once wps()->plugin_dir() . '/includes/subscription/libs/markethero.php';
			return new MarketHero( $api_key );

		}
		public function get_lists( $api_key ) {

			$markethero = $this->init( $api_key );
			$results = $markethero->tag_list();
			if( isset( $results->error )) {
				throw new Exception( $results->error );
			}
			$tags = array();
			foreach( $results->tags as $tag ) {
					$tags[$tag] = $tag;
			}
			return $tags;
		}
		public function subscribe( $identity, $options ) {

			$api_key = $options['api_key'];
			if( !empty( $identity['name'] )){
				$lead_name = $identity['name'];
			} else {
				$lead_name = esc_html__( 'No Name' , 'wp-subscribe' );
			}
			$send_tags = array();
			if( !empty( $options['tag_id'] )){
				$send_tags[] = $options['tag_id'];
			}

			$post_params = array(
				'apiKey'    => $api_key,
				'email'     => $identity['email'],
				'firstName' => $lead_name,
				'tags'      => $send_tags,
			);
			$markethero = $this->init( $api_key );
			$subscription_details = $markethero->tag_lead( $post_params );

			if( !isset( $subscription_details['result'] )) {
				throw new Exception( $subscription_details['errors']['error'] );
			}
			return array(
				'status' => 'subscribed'
			);
		}

		public function get_fields() {

			$fields = array(
				'markethero_api_key' => array(
					'id'    => 'markethero_api_key',
					'name'  => 'markethero_api_key',
					'type'  => 'text',
					'title' => esc_html__( 'Market Hero API Key', 'wp-subscribe' ),
					'desc'  => esc_html__( 'The API Key of your Market Hero account, available in Account Settings.', 'wp-subscribe' ),
					'link' => '//app.markethero.io/#/mh/settings'
				),
				'markethero_tag_id' => array(
					'id'    => 'markethero_tag_id',
					'name'  => 'markethero_tag_id',
					'type'  => 'select',
					'title' => esc_html__( 'Market Hero Tags', 'wp-subscribe' ),
					'options' => array( 'none' => esc_html__( 'Select Tag', 'wp-subscribe' ) ) + wps_get_service_list('markethero'),
					'is_list'  => true
				),
			);

			return $fields;
		}
	}
}
