<?php
/**
 * BenchmarkEmail Subscription
 */

class WPS_Subscription_Benchmark extends WPS_Subscription_Base {

	const API_URI = 'http://api.benchmarkemail.com/1.3';

	private $rpc;

	public function __construct( $config ) {

		parent::__construct( $config );

		require_once ABSPATH . 'wp-includes/class-IXR.php';

		$this->rpc = new IXR_CLIENT( self::API_URI );
	}

	public function init( $user, $pass ) {

		if ( ! $this->rpc->query( 'login', $user, $pass ) ) {
			throw new Exception( $this->rpc->getErrorMessage() );
		}
		
		return $this->rpc->getResponse();
	}

	public function get_lists( $user, $pass ) {

		$token = $this->init( $user, $pass );

		if ( ! $this->rpc->query( 'listGet', $token, '', 1, 50, '', '' ) ) {
			throw new Exception( $this->rpc->getErrorMessage() );
		}

		$result = $this->rpc->getResponse();

		$lists = array();
		foreach( $result as $list ) {
			$lists[ $list['id'] ] = $list['listname'];
		}

		return $lists;
	}

    public function subscribe( $identity, $options ) {

		$token = $this->init( $options['user'], $options['pass'] );

		$vars = array();
		$vars['email'] = $identity['email'];

        if ( !empty( $identity['name'] ) ) {
            $vars['firstname'] = $identity['name'];
        }

        $double_optin = isset( $options['double_optin'] ) && $options['double_optin'] ? '1' : '0';

		if ( ! $this->rpc->query(
			'listAddContactsOptin',
			$token,
			$options['list_name'],
			array( $vars ),
			$double_optin
		) ) {
			throw new Exception( $this->rpc->getErrorMessage() );
		}

		return array(
			'status' => 'subscribed'
		);
	}

	public function get_fields() {

		$fields = array(
			'benchmark_user' => array(
				'id'    => 'benchmark_user',
				'name'  => 'benchmark_user',
				'type'  => 'text',
				'title' => esc_html__( 'BenchmarkEmail Username', 'wp-subscribe' ),
			),

			'benchmark_pass' => array(
				'id'    => 'benchmark_pass',
				'name'  => 'benchmark_pass',
				'type'  => 'text',
				'title' => esc_html__( 'BenchmarkEmail Password', 'wp-subscribe' ),
			),

			'benchmark_list_name' => array(
				'id'    => 'benchmark_list_name',
				'name'  => 'benchmark_list_name',
				'type'  => 'select',
				'title' => esc_html__( 'BenchmarkEmail List', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('benchmark'),
				'is_list' => true
			),

			'benchmark_double_optin' => array(
				'id'    => 'benchmark_double_optin',
				'name'  => 'benchmark_double_optin',
				'type'  => 'checkbox',
				'title' => esc_html__( 'Send double opt-in notification', 'wp-subscribe' )
			)
		);

		return $fields;
	}
}
