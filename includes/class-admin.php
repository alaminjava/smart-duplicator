<?php
/**
 * Admin UI: settings page + enqueue assets.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Smart_Duplicator_Admin {

	public static function init() {
		add_action( 'admin_menu',              [ __CLASS__, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts',   [ __CLASS__, 'enqueue' ] );
		add_action( 'admin_post_smart_dup_save_settings', [ __CLASS__, 'save_settings' ] );
		add_filter( 'plugin_action_links_' . SMART_DUP_BASENAME, [ __CLASS__, 'plugin_links' ] );
	}

	public static function add_menu() {
		add_options_page(
			__( 'Smart Duplicator Settings', 'smart-duplicator' ),
			__( 'Smart Duplicator', 'smart-duplicator' ),
			'manage_options',
			'smart-duplicator',
			[ __CLASS__, 'render_settings_page' ]
		);
	}

	public static function enqueue( string $hook ) {
		if ( $hook !== 'settings_page_smart-duplicator' ) return;

		wp_enqueue_style(
			'smart-duplicator-admin',
			SMART_DUP_URL . 'admin/css/admin.css',
			[],
			SMART_DUP_VERSION
		);
		wp_enqueue_script(
			'smart-duplicator-admin',
			SMART_DUP_URL . 'admin/js/admin.js',
			[ 'jquery' ],
			SMART_DUP_VERSION,
			true
		);
	}

	public static function save_settings() {
		check_admin_referer( 'smart_dup_settings_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-duplicator' ) );
		}

		Smart_Duplicator_Settings::save( $_POST );

		wp_safe_redirect( add_query_arg( [
			'page'    => 'smart-duplicator',
			'updated' => '1',
		], admin_url( 'options-general.php' ) ) );
		exit;
	}

	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$s           = Smart_Duplicator_Settings::get_all();
		$post_types  = get_post_types( [ 'public' => true ], 'objects' );
		unset( $post_types['attachment'] );
		$updated     = isset( $_GET['updated'] );
		?>
		<div class="wrap smart-dup-wrap">
			<div class="smart-dup-header">
				<div class="smart-dup-logo">
					<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
						<rect x="2" y="8" width="20" height="22" rx="3" fill="none" stroke="currentColor" stroke-width="2"/>
						<rect x="10" y="2" width="20" height="22" rx="3" fill="currentColor" opacity=".15" stroke="currentColor" stroke-width="2"/>
						<path d="M7 17h10M7 21h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</div>
				<div>
					<h1><?php esc_html_e( 'Smart Duplicator', 'smart-duplicator' ); ?></h1>
					<p class="smart-dup-tagline"><?php esc_html_e( 'Duplicate posts, pages, and custom post types with one click.', 'smart-duplicator' ); ?></p>
				</div>
			</div>

			<?php if ( $updated ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'smart-duplicator' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'smart_dup_settings_nonce' ); ?>
				<input type="hidden" name="action" value="smart_dup_save_settings">

				<div class="smart-dup-card">
					<h2><?php esc_html_e( 'Supported Post Types', 'smart-duplicator' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Choose which post types show the Duplicate action. Leave all unchecked to auto-detect all public post types.', 'smart-duplicator' ); ?></p>
					<div class="smart-dup-checkboxes">
						<?php foreach ( $post_types as $slug => $obj ) :
							$checked = in_array( $slug, $s['post_types'], true );
						?>
							<label class="smart-dup-checkbox-label">
								<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $checked ); ?>>
								<span><?php echo esc_html( $obj->labels->singular_name ); ?></span>
								<code><?php echo esc_html( $slug ); ?></code>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="smart-dup-card">
					<h2><?php esc_html_e( 'Duplication Behaviour', 'smart-duplicator' ); ?></h2>

					<table class="form-table smart-dup-table">
						<tr>
							<th><?php esc_html_e( 'Duplicate Status', 'smart-duplicator' ); ?></th>
							<td>
								<select name="default_status">
									<?php
									$statuses = [
										'draft'   => __( 'Draft', 'smart-duplicator' ),
										'publish' => __( 'Published', 'smart-duplicator' ),
										'pending' => __( 'Pending Review', 'smart-duplicator' ),
										'private' => __( 'Private', 'smart-duplicator' ),
									];
									foreach ( $statuses as $val => $label ) :
									?>
										<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $s['default_status'], $val ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Status assigned to the duplicated post.', 'smart-duplicator' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Title Suffix', 'smart-duplicator' ); ?></th>
							<td>
								<input type="text" name="title_suffix" value="<?php echo esc_attr( $s['title_suffix'] ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'Appended to the title of the duplicated post. Leave blank for no suffix.', 'smart-duplicator' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'After Duplicating', 'smart-duplicator' ); ?></th>
							<td>
								<select name="redirect_to">
									<option value="edit" <?php selected( $s['redirect_to'], 'edit' ); ?>><?php esc_html_e( 'Open the new post editor', 'smart-duplicator' ); ?></option>
									<option value="list" <?php selected( $s['redirect_to'], 'list' ); ?>><?php esc_html_e( 'Stay on the posts list', 'smart-duplicator' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</div>

				<div class="smart-dup-card">
					<h2><?php esc_html_e( 'What to Copy', 'smart-duplicator' ); ?></h2>
					<div class="smart-dup-toggles">
						<label class="smart-dup-toggle">
							<input type="checkbox" name="copy_meta" value="1" <?php checked( $s['copy_meta'] ); ?>>
							<span class="toggle-switch"></span>
							<span><?php esc_html_e( 'Custom Fields (post meta)', 'smart-duplicator' ); ?></span>
						</label>
						<label class="smart-dup-toggle">
							<input type="checkbox" name="copy_terms" value="1" <?php checked( $s['copy_terms'] ); ?>>
							<span class="toggle-switch"></span>
							<span><?php esc_html_e( 'Categories, Tags & Taxonomies', 'smart-duplicator' ); ?></span>
						</label>
						<label class="smart-dup-toggle">
							<input type="checkbox" name="copy_thumbnail" value="1" <?php checked( $s['copy_thumbnail'] ); ?>>
							<span class="toggle-switch"></span>
							<span><?php esc_html_e( 'Featured Image', 'smart-duplicator' ); ?></span>
						</label>
					</div>
				</div>

				<div class="smart-dup-actions">
					<?php submit_button( __( 'Save Settings', 'smart-duplicator' ), 'primary smart-dup-save', 'submit', false ); ?>
				</div>
			</form>

			<div class="smart-dup-footer">
				<p>
					<?php printf(
						/* translators: %s: link to GitHub */
						esc_html__( 'Smart Duplicator is free and open source. %s', 'smart-duplicator' ),
						'<a href="https://github.com/your-repo/smart-duplicator" target="_blank">' . esc_html__( 'View on GitHub →', 'smart-duplicator' ) . '</a>'
					); ?>
				</p>
			</div>
		</div>
		<?php
	}

	public static function plugin_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=smart-duplicator' ) ),
			esc_html__( 'Settings', 'smart-duplicator' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}
