<?php
/**
 * Core duplication engine.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Smart_Duplicator {

	/**
	 * Duplicate a post/page/CPT.
	 *
	 * @param int   $post_id   Source post ID.
	 * @param array $options   Override defaults (status, title_suffix, copy_meta, copy_terms, copy_comments, copy_thumbnail, author).
	 * @return int|WP_Error   New post ID on success.
	 */
	public static function duplicate( int $post_id, array $options = [] ) {
		$source = get_post( $post_id );

		if ( ! $source || ! is_a( $source, 'WP_Post' ) ) {
			return new WP_Error( 'invalid_post', __( 'Source post not found.', 'smart-duplicator' ) );
		}

		$settings = Smart_Duplicator_Settings::get_all();

		$opts = wp_parse_args( $options, [
			'status'           => $settings['default_status'] ?? 'draft',
			'title_suffix'     => $settings['title_suffix']   ?? __( ' (Copy)', 'smart-duplicator' ),
			'copy_meta'        => $settings['copy_meta']       ?? true,
			'copy_terms'       => $settings['copy_terms']      ?? true,
			'copy_comments'    => false,
			'copy_thumbnail'   => $settings['copy_thumbnail']  ?? true,
			'author'           => get_current_user_id(),
			'parent'           => $source->post_parent,
			'date'             => current_time( 'mysql' ),
		] );

		// --- Build new post args ---
		$new_post_args = [
			'post_author'    => $opts['author'],
			'post_content'   => $source->post_content,
			'post_excerpt'   => $source->post_excerpt,
			'post_name'      => self::unique_slug( $source->post_name, $source->post_type ),
			'post_parent'    => $opts['parent'],
			'post_password'  => $source->post_password,
			'post_status'    => $opts['status'],
			'post_title'     => $source->post_title . $opts['title_suffix'],
			'post_type'      => $source->post_type,
			'post_date'      => $opts['date'],
			'post_date_gmt'  => get_gmt_from_date( $opts['date'] ),
			'menu_order'     => $source->menu_order,
			'comment_status' => $source->comment_status,
			'ping_status'    => $source->ping_status,
		];

		$new_id = wp_insert_post( wp_slash( $new_post_args ), true );

		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		// --- Post meta ---
		if ( $opts['copy_meta'] ) {
			self::copy_meta( $post_id, $new_id, $opts['copy_thumbnail'] );
		}

		// --- Taxonomies ---
		if ( $opts['copy_terms'] ) {
			self::copy_terms( $post_id, $new_id, $source->post_type );
		}

		/**
		 * Fires after a post has been duplicated.
		 *
		 * @param int     $new_id    New post ID.
		 * @param int     $post_id   Original post ID.
		 * @param WP_Post $source    Original post object.
		 * @param array   $opts      Options used for duplication.
		 */
		do_action( 'smart_duplicator_after_duplicate', $new_id, $post_id, $source, $opts );

		return $new_id;
	}

	/**
	 * Copy all post meta from source to destination.
	 */
	private static function copy_meta( int $from, int $to, bool $copy_thumbnail = true ) {
		$meta_rows = get_post_meta( $from );

		// Keys to always skip.
		$skip = apply_filters( 'smart_duplicator_skip_meta_keys', [
			'_edit_lock', '_edit_last', '_wp_old_slug', '_wp_old_date',
		] );

		if ( ! $copy_thumbnail ) {
			$skip[] = '_thumbnail_id';
		}

		foreach ( $meta_rows as $key => $values ) {
			if ( in_array( $key, $skip, true ) ) {
				continue;
			}
			foreach ( $values as $value ) {
				add_post_meta( $to, $key, maybe_unserialize( $value ) );
			}
		}
	}

	/**
	 * Copy taxonomies / terms from source to destination.
	 */
	private static function copy_terms( int $from, int $to, string $post_type ) {
		$taxonomies = get_object_taxonomies( $post_type );

		foreach ( $taxonomies as $tax ) {
			$terms = wp_get_object_terms( $from, $tax, [ 'fields' => 'ids' ] );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				wp_set_object_terms( $to, $terms, $tax );
			}
		}
	}

	/**
	 * Generate a unique post slug.
	 */
	private static function unique_slug( string $slug, string $post_type ): string {
		return wp_unique_post_slug(
			sanitize_title( $slug . '-copy' ),
			0,
			'draft',
			$post_type,
			0
		);
	}

	/**
	 * Get supported post types.
	 */
	public static function get_supported_post_types(): array {
		$settings = Smart_Duplicator_Settings::get_all();
		$saved    = $settings['post_types'] ?? [];

		if ( ! empty( $saved ) ) {
			return $saved;
		}

		// Default: posts + pages + any public CPT.
		$public = get_post_types( [ 'public' => true ], 'names' );
		unset( $public['attachment'] );
		return array_values( $public );
	}
}
