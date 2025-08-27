<?php
/**
 * ActiveCampaign Subscription
 */

class WPS_Subscription_ActiveCampaign extends WPS_Subscription_Base {

	public function init( $api_key, $api_url ) {

		require_once wps()->plugin_dir() . '/includes/subscription/libs/activecampaign/ActiveCampaign.class.php';
		return new ActiveCampaign( $api_url, $api_key );
	}

	public function get_lists( $api_key, $api_url ) {

		$api = $this->init( $api_key, $api_url );
		$result = $api->api('list/list?ids=all');

		$lists = array();
		if( isset( $result->success ) ) {

			$result = $this->clean_output( $result );
			foreach( $result as $list ) {

				$lists[ $list->id ] = $list->name;
			}
		}

		return $lists;
	}

    public function subscribe( $identity, $options ) {

		$email = $identity['email'];
		$list_id = $options['listid'];

		$firstName = '';
        if ( !empty( $identity['name'] ) ) {
			$firstName = $identity['name'];
		}

		$api = $this->init( $options['apikey'], $options['apiurl'] );
		$response = $api->api('contact/view?email=' . $email);
		$exists = isset( $response->id );

		$data = array();
		$data['email'] = $email;
		$data['ip4'] = $_SERVER['REMOTE_ADDR'];
		if ( !empty( $firstName ) ) {
			$data['first_name'] = $firstName;
		}
		$data['status[' . $list_id . ']'] = 1;
        $data['instantresponders[' . $list_id . ']'] = 1;

		// already exits
        if ( $exists ) {
            $lists = explode( '-', $response->listslist );

            if ( !in_array( ''.$list_id, $lists ) ) {

                $data['id'] = $response->id;

                $lists[] = $list_id;
                foreach($lists as $list_id) {
                    $data['p[' . $list_id . ']'] = $list_id;
                }

                $api->api('contact/edit', $data);
            }

            return array(
				'status' => 'subscribed'
			);
        }
		$data['p[' . $list_id . ']'] = $list_id;

		$result = $api->api('contact/add', $data);

		if( isset( $result->error ) ) {
			throw new Exception( $result->error );
		}
		if( isset( $result->success ) && isset( $result->subscriber_id ) ) {
			return array(
				'status' => 'subscribed'
			);
		}

		throw new Exception( esc_html__( 'Unknown error.', 'wp-subscribe' ) );
	}

	private function clean_output( $result ) {

		unset( $result->result_code, $result->result_message, $result->result_output, $result->http_code, $result->success );

		return $result;
	}

	public function get_fields() {

		$fields = array(

			'activecampaignapikey' => array(
				'id'    => 'activecampaignapikey',
				'name'  => 'activecampaignapikey',
				'type'  => 'text',
				'title' => esc_html__( 'ActiveCampaign API Key', 'wp-subscribe' ),
				'desc' => esc_html__( 'The API Key of your ActiveCampaign account.', 'wp-subscribe' ),
				'link' => 'https://help.activecampaign.com/hc/en-us/articles/207317590-Getting-started-with-the-API'
			),

			'activecampaignapiurl' => array(
				'id'    => 'activecampaignapiurl',
				'name'  => 'activecampaignapiurl',
				'type'  => 'text',
				'title' => esc_html__( 'ActiveCampaign API URL', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The API Url of your ActiveCampaign account.', 'wp-subscribe' ),
				'link'  => 'https://help.activecampaign.com/hc/en-us/articles/207317590-Getting-started-with-the-API'
			),

			'activecampaignlistid' => array(
				'id'      => 'activecampaignlistid',
				'name'    => 'activecampaignlistid',
				'type'    => 'select',
				'title'   => esc_html__( 'ActiveCampaign List', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('activecampaign'),
				'is_list' => true
			)
		);

		return $fields;
	}
}
