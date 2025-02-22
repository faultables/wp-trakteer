<?php
class Trakteer {
    protected $plugin_name;
    protected $version;
    protected $loader;

    public function __construct() {
        $this->plugin_name = 'trakteer';
        $this->version = TRAKTEER_VERSION;

        $this->load_dependencies();

        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once TRAKTEER_PLUGIN_DIR . 'includes/class-trakteer-loader.php';
        require_once TRAKTEER_PLUGIN_DIR . 'includes/class-trakteer-api.php';

        require_once TRAKTEER_PLUGIN_DIR . 'admin/class-trakteer-admin.php';
        require_once TRAKTEER_PLUGIN_DIR . 'public/class-trakteer-public.php';

        $this->loader = new Trakteer_Loader();
    }

    private function define_admin_hooks() {
        $api = new Trakteer_Api();

        $plugin_admin = new Trakteer_Admin( $this->plugin_name, $this->version, $api );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
    }

    private function define_public_hooks() {
        $plugin_public = new Trakteer_Public( $this->plugin_name, $this->version );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
    }

    public function run() {
        $this->loader->run();
    }
}
