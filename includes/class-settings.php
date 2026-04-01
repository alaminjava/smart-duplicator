<?php
/**
 * Plugin settings (stored in wp_options).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Smart_Duplicator_Settings {

	const OPTION_KEY = 'smart_duplicator_settings';

	public static function get_defaults(): array {
		return [
			'post_types'     => [],          // empty = auto-detect public post types
			'default_status' => 'draft',
			'title_suffix'   => __( ' (Copy)', 'smart-duplicator' ),
			'copy_meta'      => true,
			'copy_terms'     => true,
			'copy_thumbnail' => true,
			'redirect_to'    => 'edit',       // 'edit' | 'list'
		];
	}

	public static function get_all(): array {
		$saved = get_option( self::OPTION_KEY, [] );
		return wp_parse_args( $saved, self::get_defaults() );
	}

	public static function get( string $key ) {
		$all = self::get_all();
		return $all[ $key ] ?? null;
	}

	public static function save( array $data ): bool {
		$clean = self::sanitize( $data );
		return update_option( self::OPTION_KEY, $clean );
	}

	public static function set_defaults() {
		if ( false === get_option( self::OPTION_KEY ) ) {
			add_option( self::OPTION_KEY, self::get_defaults() );
		}
	}

	private static function sanitize( array $data ): array {
		$defaults = self::get_defaults();
		$clean    = [];

		// post_types: array of valid post type slugs
		$all_types = get_post_types( [ 'public' => true ], 'names' );
		if ( isset( $data['post_types'] ) && is_array( $data['post_types'] ) ) {
			$clean['post_types'] = array_values( array_intersect( $data['post_types'], array_keys( $all_types ) ) );
		} else {
			$clean['post_types'] = $defaults['post_types'];
		}

		$clean['default_status'] = in_array( $data['default_status'] ?? '', [ 'draft', 'publish', 'pending', 'private' ], true )
			? $data['default_status']
			: $defaults['default_status'];

		$clean['title_suffix']   = sanitize_text_field( $data['title_suffix'] ?? $defaults['title_suffix'] );

		$clean['copy_meta']      = ! empty( $data['copy_meta'] );
		$clean['copy_terms']     = ! empty( $data['copy_terms'] );
		$clean['copy_thumbnail'] = ! empty( $data['copy_thumbnail'] );

		$clean['redirect_to'] = in_array( $data['redirect_to'] ?? '', [ 'edit', 'list' ], true )
			? $data['redirect_to']
			: $defaults['redirect_to'];

		return $clean;
	}
}
