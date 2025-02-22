<?php
/**
 * @link              https://github.com/faultables/wp-trakteer
 * @since             1.0.0
 * @package           Trakteer
 *
 * @wordpress-plugin
 * Plugin Name:       Unofficial Trakteer Plugin
 * Plugin URI:        https://github.com/faultables/wp-trakteer
 * Description:       Unofficial Trakteer Wordpress Plugin
 * Version:           1.0.0
 * Author:            faultables
 * Author URI:        https://github.com/faultables/
 * License:           GPL2+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       trakteer
 */

if ( ! defined( 'WPINC' )) {
    die();
}

define( 'TRAKTEER_VERSION', '1.0.0' );
define( 'TRAKTEER_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define( 'TRAKTEER_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define( 'TRAKTEER_ENVIRONMENT', getenv( 'TRAKTEER_ENV' ) ?: 'production' );

function activate_trakteer()
{
    require_once TRAKTEER_PLUGIN_DIR . 'includes/class-trakteer-activator.php';

    Trakteer_Activator::activate();
}

function deactivate_trakteer()
{
    require_once TRAKTEER_PLUGIN_DIR . 'includes/class-trakteer-deactivator.php';

    Trakteer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_trakteer' );
register_deactivation_hook( __FILE__, 'deactivate_trakteer' );

require TRAKTEER_PLUGIN_DIR . 'includes/class-trakteer.php';

function run_trakteer_plugin()
{
    $plugin = new Trakteer();

    $plugin->run();
}

run_trakteer_plugin();
