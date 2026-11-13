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

update_option( 'woocommerce_demo_store', 'yes' );
update_option( 'woocommerce_demo_store_notice', 'Tienda de demostración en WordPress Playground: los pedidos son de prueba y se borran al cerrar la pestaña. Para pagar usa «Transferencia bancaria»; el pago con Webpay Plus se probó en la instalación local del proyecto.' );
echo "Ajustes de demostración aplicados.\n";
