<?php
/**
 * REST API endpoint: POST /wp-json/smart-duplicator/v1/duplicate/{id}
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Smart_Duplicator_REST_API {

	public static function init() {
		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
	}

	public static function register_routes() {
		register_rest_route( 'smart-duplicator/v1', '/duplicate/(?P<id>\d+)', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'handle' ],
			'permission_callback' => [ __CLASS__, 'check_permission' ],
			'args'                => [
				'id' => [
					'required'          => true,
					'validate_callback' => fn( $val ) => is_numeric( $val ),
					'sanitize_callback' => 'absint',
				],
				'status' => [
					'default'           => 'draft',
					'sanitize_callback' => 'sanitize_key',
				],
				'title_suffix' => [
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	public static function check_permission( WP_REST_Request $request ): bool {
		return current_user_can( 'edit_post', $request->get_param( 'id' ) );
	}

	public static function handle( WP_REST_Request $request ) {
		$post_id = $request->get_param( 'id' );

		$opts = [];
		if ( $request->get_param( 'status' ) ) {
			$opts['status'] = $request->get_param( 'status' );
		}
		if ( $request->get_param( 'title_suffix' ) !== '' ) {
			$opts['title_suffix'] = $request->get_param( 'title_suffix' );
		}

		$new_id = Smart_Duplicator::duplicate( $post_id, $opts );

		if ( is_wp_error( $new_id ) ) {
			return new WP_REST_Response( [ 'error' => $new_id->get_error_message() ], 400 );
		}

		$new_post = get_post( $new_id );

		return new WP_REST_Response( [
			'id'        => $new_id,
			'title'     => $new_post->post_title,
			'status'    => $new_post->post_status,
			'edit_link' => get_edit_post_link( $new_id, 'raw' ),
			'link'      => get_permalink( $new_id ),
		], 201 );
	}
}
