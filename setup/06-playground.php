<?php
/**
 * Copayapu - ajustes exclusivos de la demostración en WordPress Playground.
 * Playground envía las conexiones externas a través de un proxy que la API de Transbank rechaza,
 * por lo que en esta versión el pago de prueba se hace por transferencia. El pago con Webpay
 * Plus se probó en la instalación local con Docker (ver setup/05-pagos.php).
 * No ejecutar en la instalación local.
 */
$webpay = get_option( 'woocommerce_transbank_webpay_plus_rest_settings', [] );
$webpay = is_array( $webpay ) ? $webpay : [];
$webpay['enabled'] = 'no';
update_option( 'woocommerce_transbank_webpay_plus_rest_settings', $webpay );

$bacs = get_option( 'woocommerce_bacs_settings', [] );
$bacs = is_array( $bacs ) ? $bacs : [];
$bacs['enabled']     = 'yes';
$bacs['title']       = 'Transferencia bancaria';
$bacs['description'] = 'Medio de pago habilitado en esta demostración. El pedido queda «En espera» hasta confirmar la transferencia.';
update_option( 'woocommerce_bacs_settings', $bacs );

// Aviso de demostración en la parte superior de la tienda (no lo tapa la barra de Playground)
update_option( 'woocommerce_demo_store', 'no' );
wp_mkdir_p( WP_CONTENT_DIR . '/mu-plugins' );
file_put_contents( WP_CONTENT_DIR . '/mu-plugins/copayapu-aviso-demo.php', <<<'PHP'
<?php
/**
 * Plugin Name: Copayapu - Aviso de demostración
 * Description: Franja superior que explica que la tienda en WordPress Playground es una demostración.
 */
add_action( 'wp_body_open', function () {
	if ( is_admin() ) { return; }
	echo '<div class="cpy-demo" role="note"><strong>Tienda de demostración en WordPress Playground.</strong> Los pedidos son de prueba y se borran al cerrar la pestaña. Para pagar usa «Transferencia bancaria»; el pago con Webpay Plus se probó en la instalación local del proyecto.</div>';
} );
add_action( 'wp_head', function () {
	echo '<style>.cpy-demo{background:#2F3D14;color:#EEF2E2;font-size:14px;line-height:1.45;padding:10px 16px;text-align:center}.cpy-demo strong{color:#fff}</style>';
} );
PHP
);
echo "Ajustes de demostración aplicados.\n";
