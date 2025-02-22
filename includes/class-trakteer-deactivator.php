<?php
class Trakteer_Deactivator
{
    public static function deactivate()
    {
        delete_transient( 'trakteer_supporter' );
        delete_transient( 'trakteer_supporter_time' );
    }
}
