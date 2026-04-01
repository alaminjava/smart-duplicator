<?php
/**
 * Plugin Name:       Smart Duplicator
 * Plugin URI:        https://github.com/your-repo/smart-duplicator
 * Description:       Duplicate posts, pages, and custom post types instantly — with full control over meta, taxonomies, and status. Clean, fast, open source.
 * Version:           1.0.0
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://yoursite.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smart-duplicator
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SMART_DUP_VERSION',  '1.0.0' );
define( 'SMART_DUP_FILE',     __FILE__ );
define( 'SMART_DUP_DIR',      plugin_dir_path( __FILE__ ) );
define( 'SMART_DUP_URL',      plugin_dir_url( __FILE__ ) );
define( 'SMART_DUP_BASENAME', plugin_basename( __FILE__ ) );

require_once SMART_DUP_DIR . 'includes/class-duplicator.php';
require_once SMART_DUP_DIR . 'includes/class-row-actions.php';
require_once SMART_DUP_DIR . 'includes/class-bulk-actions.php';
require_once SMART_DUP_DIR . 'includes/class-settings.php';
require_once SMART_DUP_DIR . 'includes/class-admin.php';
require_once SMART_DUP_DIR . 'includes/class-rest-api.php';

function smart_duplicator_init() {
	Smart_Duplicator_Row_Actions::init();
	Smart_Duplicator_Bulk_Actions::init();
	Smart_Duplicator_Admin::init();
	Smart_Duplicator_REST_API::init();
}
add_action( 'plugins_loaded', 'smart_duplicator_init' );

register_activation_hook( __FILE__, 'smart_duplicator_activate' );
function smart_duplicator_activate() {
	Smart_Duplicator_Settings::set_defaults();
}
