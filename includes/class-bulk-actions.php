<?php
/**
 * Adds "Duplicate" to bulk actions dropdown.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Smart_Duplicator_Bulk_Actions {

	public static function init() {
		foreach ( Smart_Duplicator::get_supported_post_types() as $pt ) {
			$hook = ( $pt === 'post' ) ? 'posts' : ( $pt === 'page' ? 'pages' : 'edit-' . $pt );
			add_filter( "bulk_actions-{$hook}",          [ __CLASS__, 'register' ] );
			add_filter( "handle_bulk_actions-{$hook}",   [ __CLASS__, 'handle' ], 10, 3 );
		}
		add_action( 'admin_notices', [ __CLASS__, 'notice' ] );
	}

	public static function register( array $actions ): array {
		$actions['smart_dup_bulk_duplicate'] = __( 'Duplicate', 'smart-duplicator' );
		return $actions;
	}

	public static function handle( string $redirect, string $action, array $ids ): string {
		if ( $action !== 'smart_dup_bulk_duplicate' ) {
			return $redirect;
		}

		$count = 0;
		foreach ( $ids as $id ) {
			$post_id = absint( $id );
			if ( current_user_can( 'edit_post', $post_id ) ) {
				$result = Smart_Duplicator::duplicate( $post_id );
				if ( ! is_wp_error( $result ) ) {
					$count++;
				}
			}
		}

		return add_query_arg( 'smart_dup_bulk_count', $count, remove_query_arg( [ 'action', 'action2' ], $redirect ) );
	}

	public static function notice() {
		$count = isset( $_GET['smart_dup_bulk_count'] ) ? absint( $_GET['smart_dup_bulk_count'] ) : 0;
		if ( ! $count ) return;

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( sprintf(
				/* translators: %d: number of posts duplicated */
				_n( '%d post duplicated successfully.', '%d posts duplicated successfully.', $count, 'smart-duplicator' ),
				$count
			) )
		);
	}
}
