<?php
class Trakteer_Admin
{
    private $plugin_name;
    private $version;
    private $api;

    private $base_url = 'https://trakteer.id';

    public function __construct( $plugin_name, $version, $api )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->api = $api;

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

    public function enqueue_styles($hook)
    {
        if ( $hook === 'toplevel_page_trakteer' ) {
            wp_enqueue_style(
                $this->plugin_name . '-public',
                TRAKTEER_PLUGIN_URL . 'public/css/trakteer-public.css',
                [],
                $this->version,
                'all'
            );
        }

        wp_enqueue_style(
            $this->plugin_name,
            TRAKTEER_PLUGIN_URL . 'admin/css/trakteer-admin.css',
            [],
            $this->version,
            'all'
        );
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

    public function enqueue_scripts($hook)
    {
        if ( $hook === 'toplevel_page_trakteer' ) {
            if ( $this->should_load_trakteer_script() ) {
                wp_enqueue_script(
                    'trakteer-overlay',
                    'https://edge-cdn.trakteer.id/js/trbtn-overlay.min.js',
                    [],
                    '24-01-2025',
                    true
                );

                wp_add_inline_script( 'trakteer-overlay', $this->get_trakteer_initialization_script() );
            }
        }
    }

    public function add_plugin_admin_menu()
    {
        add_menu_page(
            'Trakteer Plugin Settings',
            'Trakteer',
            'manage_options',
            $this->plugin_name,
            [ $this, 'display_plugin_settings_page' ],
            TRAKTEER_PLUGIN_URL . 'assets/images/trakteer-logo.png'
        );

        add_submenu_page(
            $this->plugin_name,
            'Pengaturan',
            'Pengaturan',
            'manage_options',
            $this->plugin_name,
            [ $this, 'display_plugin_settings_page' ]
        );

        add_submenu_page(
            $this->plugin_name,
            'Supporter',
            'Supporter',
            'manage_options',
            'trakteer-supporters',
            [ $this, 'display_supporters_page' ]
        );

        add_submenu_page(
            $this->plugin_name,
            'Tentang',
            'Tentang',
            'manage_options',
            'trakteer-about',
            [ $this, 'display_about_page' ]
        );
    }

    public function display_plugin_settings_page()
    {
        include_once TRAKTEER_PLUGIN_DIR . 'admin/partials/trakteer-admin-settings.php';
    }

    public function display_supporters_page()
    {
        $cache_key = 'trakteer_supporter';
        $last_update = get_transient( $cache_key . '_time' );

        if ( ! $last_update ) {
            $last_update = 'Tidak pernah';
        } else {
            $updated_at = strtotime( $last_update );
            $last_update = date_i18n(
                get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
                $updated_at
            );
        }

        if ( isset( $_POST[ 'invalidate_cache' ] )) {
            delete_transient( $cache_key );
            delete_transient( $cache_key . '_time' );
        }

        $response = $this->api->get_supporters();

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        include_once TRAKTEER_PLUGIN_DIR . 'admin/partials/trakteer-admin-supporters.php';
    }

    public function display_about_page()
    {
        include_once TRAKTEER_PLUGIN_DIR . 'admin/partials/trakteer-admin-about.php';
    }

    public function register_settings()
    {
        register_setting( 'trakteer_settings_group', 'trakteer_username', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_setting( 'trakteer_settings_group', 'trakteer_api_key', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_setting( 'trakteer_settings_group', 'trakteer_tip_overlay_position', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_setting( 'trakteer_settings_group', 'trakteer_tip_overlay_visibility', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_setting( 'trakteer_settings_group', 'trakteer_tip_overlay_text', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        add_settings_section(
            'trakteer_settings_section',
            '',
            [ $this, 'settings_section_callback' ],
            'trakteer_settings'
        );

        add_settings_field(
            'trakteer_username',
            'Username',
            [ $this, 'username_field_callback' ],
            'trakteer_settings',
            'trakteer_settings_section'
        );

        add_settings_field(
            'trakteer_api_key',
            'API Key',
            [ $this, 'api_key_field_callback' ],
            'trakteer_settings',
            'trakteer_settings_section'
        );

        add_settings_field(
            'trakteer_tip_overlay_position',
            'Posisi tombol Tip Overlay',
            [ $this, 'tip_overlay_position_field_callback' ],
            'trakteer_settings',
            'trakteer_settings_section'
        );

        add_settings_field(
            'trakteer_tip_overlay_visibility',
            'Pengaturan tombol Tip Overlay',
            [ $this, 'tip_overlay_visibility_field_callback' ],
            'trakteer_settings',
            'trakteer_settings_section'
        );

        add_settings_field(
            'trakteer_tip_overlay_text',
            'Teks tombol Tip Overlay',
            [ $this, 'tip_overlay_text_field_callback' ],
            'trakteer_settings',
            'trakteer_settings_section'
        );
    }

    public function settings_section_callback()
    {
        echo 'Sesuaikan pengaturan berikut sesuai dengan kebutuhan';
    }

    public function username_field_callback()
    {
        $username = get_option( 'trakteer_username' ); ?>

        <input type="text" name="trakteer_username" value="<?php echo esc_attr(
            $username
        ); ?>" class="regular-text"/>
        <?php if ( $username ): ?>
            <p class="description">Pratinjau: <a rel="noopener noreferrer" target="_blank" href="<?php echo esc_attr( $this->base_url ); ?>/<?php echo esc_attr( $username ); ?>"><?php echo esc_attr( $this->base_url ); ?>/<?php echo esc_attr( $username ); ?></a></p>
        <?php endif; ?>
        <p class="description trakteer-form__description">
            Kunjungi <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $this->base_url ); ?>/manage/my-page">halaman berikut</a> untuk melihat username Anda.
        </p>
        <?php
    }

    function api_key_field_callback()
    {
        $api_key = get_option( 'trakteer_api_key' ); ?>

        <input type="text"
               name="trakteer_api_key"
               value="<?php echo esc_attr( $api_key ); ?>"
               class="regular-text"
        />

        <p class="description trakteer-form__description">
            Kunjungi <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $this->base_url ); ?>/manage/api-trakteer">halaman berikut</a> untuk melihat API Key Anda (Integrations > Public API). Informasi
            ini digunakan untuk menampilkan data riwayat trakteer-an dan jumlah unit trakteer-an yang telah diberikan oleh supporter (berdasarkan email) selama 30 hari terakhir.
        </p>
        <?php
    }

    function tip_overlay_text_field_callback()
    {
        $tip_overlay_text = get_option( 'trakteer_tip_overlay_text', 'Dukung Saya di Trakteer' ); ?>

        <input type="text"
               name="trakteer_tip_overlay_text"
               value="<?php echo esc_attr( $tip_overlay_text ); ?>"
               class="regular-text"
        />

        <p class="description trakteer-form__description">
            Untuk saat ini, hanya teks yang dapat disesuaikan.
        </p>
        <?php
    }

    function tip_overlay_position_field_callback()
    {
        $username = get_option( 'trakteer_username' );
        $display_type = get_option( 'trakteer_tip_overlay_position', 'none' );

        $options = [
            'none' => 'Sembunyikan',
            'floating-center' => 'Floating Center',
            'floating-left' => 'Floating Left',
            'floating-right' => 'Floating Right',
        ];

        ?>
        <select name="trakteer_tip_overlay_position" class="regular-text" <?php echo empty( $username )
            ? "disabled"
            : ""; ?>>>
            <?php foreach ( $options as $value => $label ): ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected(
                $display_type,
                $value
                ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description trakteer-form__description">
            Opsi ini bisa diubah jika bagian Username tidak kosong.
        </p>
        <script class="troverlay"></script>
        <?php
    }

    function tip_overlay_visibility_field_callback()
    {
        $username = get_option( 'trakteer_username' );
        $show_overlay = get_option( 'trakteer_tip_overlay_visibility', '0' );
        $display_type = get_option( 'trakteer_tip_overlay_position', 'none' );

        ?>
        <label>
            <input type="checkbox"
                   name="trakteer_tip_overlay_visibility"
                   value="1"
                   checked
                   disabled
                   <?php checked( $show_overlay, '1' ); ?>
                   <?php echo empty( $username ) ? 'disabled' : ''; ?>>
                       Tampilkan di halaman utama
        </label>
        <br />
        <label>
            <input type="checkbox"
                   name="trakteer_tip_overlay_visibility"
                   value="1"
                   checked
                   disabled
                   <?php checked( $show_overlay, '1' ); ?>
                   <?php echo empty( $username ) ? 'disabled' : ''; ?>>
                       Tampilkan di halaman statis
        </label>
        <?php if ( empty( $username )): ?>
            <p class="description trakteer-form__description">
                Opsi ini bisa diubah jika bagian Username tidak kosong.
            </p>
        <?php else: ?>
            <p class="description trakteer-form__description">
                Tombol Tip Overlay di halaman Post akan selalu ditampilkan (jika posisi tidak disembunyikan). Untuk saat ini, opsi ini tidak bisa diubah.
            </p>
        <?php endif; ?>
        <?php
    }
}
