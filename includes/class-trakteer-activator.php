<?php

class Trakteer_Activator
{
    public static function activate()
    {
        if ( ! get_option( 'trakteer_tip_overlay_position' )) {
            add_option( 'trakteer_tip_overlay_position', 'none' );
        }

        if ( ! get_option( 'trakteer_tip_overlay_text' )) {
            add_option( 'trakteer_tip_overlay_text', 'Dukung Saya di Trakteer' );
        }
    }
}
