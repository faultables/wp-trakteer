<?php
if ( ! defined( 'WPINC' )) {
    die();
}
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php if ( $data && $data['status'] === 'success' ): ?>
        <div>
            <table class="trakteer-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Pesan</th>
                        <th>Unit</th>
                        <th>Total</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $data['result']['data'] as $item ): ?>
                        <tr>
                            <td>
                                <?php echo esc_html( $item['supporter_name'] ?? 'Seseorang' ); ?>
                            </td>
                            <td>
                                <?php echo esc_html( $item['support_message'] ?? '-' ); ?>
                            </td>
                            <td>
                                <?php echo esc_html( $item['quantity'] ); ?>
                            </td>
                            <td>
                                <?php echo number_format( $item['amount'] ); ?> IDR
                                (<?php echo number_format(
                                    $item['amount'] / $item['quantity']
                                ); ?> IDR/unit)
                            </td>
                            <td>
                                <?php
                                $updated_at = strtotime( $item['updated_at'] );

                                echo date_i18n(
                                    get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
                                    $updated_at
                                );
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p>Untuk saat ini hanya 10 data trakteer-an terakhir yang ditampilkan</p>

            <p>
                <em>Terakhir diperbarui: <?php echo esc_html( $last_update ); ?></em>
            </p>
        </div>
    <?php else: ?>
        <div class="error">
            <p>Gagal memuat data dari API. Silakan periksa API key anda atau cek situs Trakteer lalu coba lagi.</p>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="submit" name="invalidate_cache" value="Muat ulang data" class="button-primary" />
    </form>
</div>
