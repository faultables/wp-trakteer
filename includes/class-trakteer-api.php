<?php

class Trakteer_Api
{
    private $api_key;
    private $base_url = 'https://api.trakteer.id/v1/public';

    public function __construct()
    {
        $this->api_key = get_option( 'trakteer_api_key' );

        if ( TRAKTEER_ENVIRONMENT === 'development' ) {
            $base_url = getenv( 'TRAKTEER_API_URL' );

            if ( $base_url ) {
                $this->base_url = $base_url;
            } else {
                wp_die(
                    sprintf(
                        '<h1>%s</h1><p>%s</p>',
                        __( 'TRAKTEER_API_URL env Required', 'trakteer' ),
                        __( 'TRAKTEER_ENVIRONMENT is development but TRAKTEER_API_URL env is undefined. Please check your Wordpress settings.', 'trakteer' )
                    ),
                    'Trakteer Plugin Error',
                    array( 'back_link' => true )
                );
            }
        }
    }

    public function get_supporters( $limit = 10, $page = 1 )
    {
        $cache_key = 'trakteer_supporter';
        $response = get_transient( $cache_key );

        if ( false === $response ) {
            $url = $this->base_url . "/transactions?limit={$limit}&page={$page}";

            $headers = [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
                'key' => $this->api_key,
            ];

            $response = wp_remote_get($url, [
                "headers" => $headers,
            ]);

            if ( ! is_wp_error( $response )) {
                set_transient( $cache_key, $response, 3600 );
                set_transient( $cache_key . '_time', current_time( 'mysql' ), 3600 );
            }
        }

        return $response;
    }
}
