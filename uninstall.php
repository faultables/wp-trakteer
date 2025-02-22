<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' )) {
    exit();
}

delete_option( 'trakteer_username' );
delete_option( 'trakteer_api_key' );

delete_option( 'trakteer_tip_overlay_position' );
delete_option( 'trakteer_tip_overlay_visibility' );
delete_option( 'trakteer_tip_overlay_text' );

delete_transient( 'trakteer_supporter' );
delete_transient( 'trakteer_supporter_time' );
