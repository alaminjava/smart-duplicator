<?php
/**
 * Adds "Duplicate" row action in post list tables.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Smart_Duplicator_Row_Actions {

	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'register_hooks' ] );
		add_action( 'admin_action_smart_dup_duplicate', [ __CLASS__, 'handle' ] );
	}

	public static function register_hooks() {
		foreach ( Smart_Duplicator::get_supported_post_types() as $pt ) {
			add_filter( "post_row_actions",  [ __CLASS__, 'add_action' ], 10, 2 );
			add_filter( "page_row_actions",  [ __CLASS__, 'add_action' ], 10, 2 );
		}
	}

	/**
	 * Add the Duplicate link to row actions.
	 */
	public static function add_action( array $actions, WP_Post $post ): array {
		if ( ! in_array( $post->post_type, Smart_Duplicator::get_supported_post_types(), true ) ) {
			return $actions;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg( [
				'action'  => 'smart_dup_duplicate',
				'post_id' => $post->ID,
			], admin_url( 'admin.php' ) ),
			'smart_dup_duplicate_' . $post->ID
		);

		$actions['smart_duplicate'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( $url ),
			esc_attr( sprintf( __( 'Duplicate &#8220;%s&#8221;', 'smart-duplicator' ), get_the_title( $post ) ) ),
			esc_html__( 'Duplicate', 'smart-duplicator' )
		);

		return $actions;
	}

	/**
	 * Handle the duplication request.
	 */
	public static function handle() {
		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

		if ( ! $post_id ) {
			wp_die( esc_html__( 'Invalid post ID.', 'smart-duplicator' ) );
		}

		check_admin_referer( 'smart_dup_duplicate_' . $post_id );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You do not have permission to duplicate this post.', 'smart-duplicator' ) );
		}

		$new_id = Smart_Duplicator::duplicate( $post_id );

		if ( is_wp_error( $new_id ) ) {
			wp_die( esc_html( $new_id->get_error_message() ) );
		}

		$settings = Smart_Duplicator_Settings::get_all();
		$redirect = ( $settings['redirect_to'] ?? 'edit' ) === 'edit'
			? get_edit_post_link( $new_id, 'raw' )
			: add_query_arg( 'smart_dup_created', 1, get_edit_post_link( $post_id, 'raw' ) );

		wp_safe_redirect( $redirect );
		exit;
	}
}
