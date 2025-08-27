<?php
/**
 * Sendy Subscription
 */

class WPS_Subscription_Sendy extends WPS_Subscription_Base {

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

	public function get_lists( $api_key ) {

		return array();
	}

	public function request( $method, $args = array(), $requestMethod = 'GET' ) {

        if ( empty( $this->api_key ) ) {
			throw new Exception( esc_html__( 'The Sendy API Key is not specified.', 'wp-subscribe' ) );
		}

        if ( empty( $this->api_url ) ) {
			throw new Exception( esc_html__( 'The Sendy Installation is not specified.', 'wp-subscribe' ) );
		}

        $this->api_url = trim($this->api_url, '/');
        if ( false === strpos($this->api_url, 'http://') ) {
			$this->api_url = 'http://' . $this->api_url;
		}

        $url = $this->api_url . $method;
        $args['api_key'] = $this->api_key;

        $result = wp_remote_post( $url, array(
            'timeout' => 30,
            'body' => $args
        ));

        if (is_wp_error( $result )) {
            throw new Exception( sprintf( esc_html__( 'Unexpected error occurred during connection to Sendy: %s', 'wp-subscribe' ), $result->get_error_message() ) );
        }

        $code = isset( $result['response']['code'] ) ? intval ( $result['response']['code'] ) : 0;
        if ( 200 !== $code ) {
            throw new Exception( sprintf( esc_html__( 'Unexpected error occurred during connection to Sendy: %s', 'wp-subscribe' ), $result['response']['message'] ) );
        }

        if ( empty( $result['body'] ) ) {
			return false;
		}

        return $result['body'];
    }

    public function subscribe( $identity, $options ) {

		$email = $identity['email'];
		$listId = $options['list_id'];
		$this->api_key = $options['api_key'];
		$this->api_url = $options['api_url'];

		$result = $this->request('/api/subscribers/subscription-status.php', array(
            'email' => $email,
            'list_id' => $listId
        ));

		// if not subscribed yet
		if ( strpos($result, 'does not exist') > 0 ) {

			$data = array(
				'email' => $identity['email'],
				'list' => $listId ,
				'boolean' => true
			);

			if ( !empty( $identity['name'] ) ) {
				$data['name'] = $identity['name'];
				$data['firstname'] = $identity['name'];
			}

			$result = $this->request('/subscribe', $data);

			if ( 'true' === $result || strpos( $result, 'subscribed' ) || strpos( $result, 'confirmation email' ) ) {
				return array( 'status' => 'subscribed' );
			} else {
				throw new Exception( $result );
			}
		}

		// if already subscribed
		$success = array( 'subscribed', 'unsubscribed', 'bounced', 'soft bounced', 'unconfirmed', 'complained' );
		if ( !in_array( strtolower( $result ), $success ) ) {
			throw new Exception( $result );
		}

		if ( 'subscribed' === strtolower( $result ) ) {
			return array( 'status' => 'subscribed' );
		} else {
			return array( 'status' => 'pending' );
		}
	}

	public function get_fields() {

		$fields = array(
			'sendy_api_url' => array(
				'id'    => 'sendy_api_url',
				'name'  => 'sendy_api_url',
				'type'  => 'text',
				'title' => esc_html__( 'Sendy API URL', 'wp-subscribe' ),
				'desc'  => esc_html__( 'An URL for your Sendy installation, http://your_sendy_installation', 'wp-subscribe' )
			),

			'sendy_api_key' => array(
				'id'    => 'sendy_api_key',
				'name'  => 'sendy_api_key',
				'type'  => 'text',
				'title' => esc_html__( 'Sendy API Key', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The API Key of your Sendy application, available in Settings.', 'wp-subscribe' )
			),

			'sendy_list_id' => array(
				'id'    => 'sendy_list_id',
				'name'  => 'sendy_list_id',
				'type'  => 'text',
				'title' => esc_html__( 'Sendy List', 'wp-subscribe' ),
				'desc'  => esc_html__( 'Specify the list ID to add subscribers.', 'wp-subscribe' )
			)
		);

		return $fields;
	}
}
