<?php
class Trakteer_Public
{
    private $plugin_name;
    private $version;
    private $base_url = 'https://trakteer.id';

    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        if ( TRAKTEER_ENVIRONMENT === 'development' ) {
            $base_url = getenv( 'TRAKTEER_BASE_URL' );

            if ( $base_url ) {
                $this->base_url = $base_url;
            } else {
                wp_die(
                    sprintf(
                        '<h1>%s</h1><p>%s</p>',
                        __( 'TRAKTEER_BASE_URL env Required', 'trakteer' ),
                        __( 'TRAKTEER_ENVIRONMENT is development but TRAKTEER_BASE_URL env is undefined. Please check your Wordpress settings.', 'trakteer' )
                    ),
                    'Trakteer Plugin Error',
                    array( 'back_link' => true )
                );
            }
        }
    }

    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            TRAKTEER_PLUGIN_URL . 'public/css/trakteer-public.css',
            [],
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts()
    {
        // TODO: normally we do wp_add_inline_script and doing widget thing
        // but for now let's just do this
        if ( $this->should_load_trakteer_script() ) {
            add_action( 'wp_footer', array( $this, 'render_trakteer_script' ), 99 );
        }
    }

    public function render_trakteer_script()
    {
        ?>
        <script class="troverlay" src="https://edge-cdn.trakteer.id/js/trbtn-overlay.min.js"></script>
        <script>
            <?php echo $this->get_trakteer_initialization_script(); ?>
        </script>
        <?php
    }

    private function should_load_trakteer_script()
    {
        $username = get_option( 'trakteer_username' );
        $display_type = get_option( 'trakteer_tip_overlay_position' );

        if ( empty( $username ) || empty( $display_type )) {
            return false;
        }

        return $display_type !== "none";
    }

    private function get_trakteer_initialization_script()
    {
        $username = get_option( 'trakteer_username' );
        $text = get_option( 'trakteer_tip_overlay_text', 'Dukung Saya di Trakteer' );
        $display_type = get_option( 'trakteer_tip_overlay_position', 'none' );

        $button_color = '#be1e2d';
        $button_size = 40;

        return "
        document.addEventListener('DOMContentLoaded', function() {
            const btn = trbtnOverlay.init(
                '$text',
                '$button_color',
                '$this->base_url/$username/tip/embed/modal',
                'https://edge-cdn.trakteer.id/images/embed/trbtn-icon.png?v=24-01-2025',
                '$button_size',
                '$display_type'
            );

            trbtnOverlay.draw(btn);
        });
        ";
    }
}
