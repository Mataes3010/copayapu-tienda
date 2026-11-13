<?php
/**
 * Copayapu - cupones, clientes y pedidos de ejemplo, menús y reglas del checkout.
 * Ejecutar con: wp eval-file /setup/03-operacion.php
 */
$sku = fn( $k ) => wc_get_product_id_by_sku( 'CPY-' . strtoupper( $k ) );

// ---------- Plugin propio con reglas del checkout (RF17, RF20) ----------
if ( ! defined( 'CPY_ASSETS' ) ) { define( 'CPY_ASSETS', '/assets/' ); }
if ( ! defined( 'CPY_SETUP' ) ) { define( 'CPY_SETUP', '/setup/' ); }
wp_mkdir_p( WP_CONTENT_DIR . '/mu-plugins' );
if ( ! file_exists( WP_CONTENT_DIR . '/mu-plugins/copayapu-checkout.php' ) ) {
	copy( CPY_SETUP . 'copayapu-checkout.php', WP_CONTENT_DIR . '/mu-plugins/copayapu-checkout.php' );
}

// ---------- Cupones de campañas y referidos (RF19) ----------
function cpy_coupon( $code, $args ) {
	$id = wc_get_coupon_id_by_code( $code );
	$c = new WC_Coupon( $id ?: 0 );
	$c->set_code( $code );
	foreach ( $args as $k => $v ) { $c->{"set_$k"}( $v ); }
	return $c->save();
}
$canastas = get_term_by( 'name', 'Canastas de regalo', 'product_cat' )->term_id;
cpy_coupon( 'FLORIDO15', [ 'discount_type' => 'percent', 'amount' => 15, 'product_categories' => [ $canastas ],
	'description' => 'Campaña de temporada Desierto Florido (táctica T3 con microinfluencers).', 'date_expires' => '2026-12-31' ] );
cpy_coupon( 'CAMILA10', [ 'discount_type' => 'percent', 'amount' => 10, 'usage_limit' => 20, 'individual_use' => true,
	'description' => 'Código de referido entregado en la confirmación de compra (táctica T6).' ] );
cpy_coupon( 'RESENA10', [ 'discount_type' => 'percent', 'amount' => 10, 'usage_limit_per_user' => 1,
	'description' => 'Descuento por reseña con foto #SaboresDeAtacama (táctica T2).' ] );

// ---------- Clientes de ejemplo ----------
function cpy_customer( $email, $first, $last, $city, $state, $addr, $phone, $company = '' ) {
	$uid = email_exists( $email );
	if ( ! $uid ) { $uid = wc_create_new_customer( $email, sanitize_user( strstr( $email, '@', true ) ), wp_generate_password() ); }
	$c = new WC_Customer( $uid );
	$c->set_first_name( $first ); $c->set_last_name( $last ); $c->set_display_name( "$first $last" );
	foreach ( [ 'billing', 'shipping' ] as $t ) {
		$c->{"set_{$t}_first_name"}( $first ); $c->{"set_{$t}_last_name"}( $last ); $c->{"set_{$t}_company"}( $company );
		$c->{"set_{$t}_address_1"}( $addr ); $c->{"set_{$t}_city"}( $city ); $c->{"set_{$t}_state"}( $state ); $c->{"set_{$t}_country"}( 'CL' );
	}
	$c->set_billing_email( $email ); $c->set_billing_phone( $phone );
	$c->save();
	return $uid;
}
$cust = [
	'camila' => cpy_customer( 'camila.rojas@correo.local', 'Camila', 'Rojas', 'Providencia', 'CL-RM', 'Av. Los Leones 1234, depto. 52', '+56981234567' ),
	'jorge'  => cpy_customer( 'jorge.perez@correo.local', 'Jorge', 'Pérez', 'Antofagasta', 'CL-AN', 'Av. Grecia 2100', '+56992345678' ),
	'rosa'   => cpy_customer( 'rosa.diaz@correo.local', 'Rosa', 'Díaz', 'Copiapó', 'CL-AT', 'Chacabuco 450', '+56973456789' ),
	'empresa'=> cpy_customer( 'personas@contratista.local', 'Daniela', 'Muñoz', 'Copiapó', 'CL-AT', 'Ruta C-17 km 3', '+56964567890', 'Contratista Minero Atacama Ltda. (ejemplo)' ),
];

// ---------- Pedidos de ejemplo (RF07) ----------
function cpy_order( $uid, $items, $ship_title, $ship_cost, $method, $method_title, $status, $date, $coupon = '' ) {
	$o = wc_create_order( [ 'customer_id' => $uid ] );
	$c = new WC_Customer( $uid );
	foreach ( $items as [ $pid, $q ] ) { $o->add_product( wc_get_product( $pid ), $q ); }
	$o->set_address( $c->get_billing(), 'billing' );
	$o->set_address( $c->get_shipping(), 'shipping' );
	$s = new WC_Order_Item_Shipping();
	$s->set_method_title( $ship_title ); $s->set_total( $ship_cost );
	$o->add_item( $s );
	if ( $coupon ) { $o->apply_coupon( $coupon ); }
	$o->set_payment_method( $method ); $o->set_payment_method_title( $method_title );
	$o->set_date_created( $date );
	$o->calculate_totals();
	$o->set_status( $status, 'Pedido de ejemplo creado para el informe.' );
	$o->save();
	return $o->get_id();
}
if ( ! wc_get_orders( [ 'limit' => 1, 'return' => 'ids' ] ) ) {
	cpy_order( $cust['jorge'], [ [ $sku( 'pasasm' ), 1 ], [ $sku( 'nueces' ), 1 ], [ $sku( 'miel' ), 1 ] ], 'Despacho a domicilio Blue Express', 3353, 'transbank_webpay_plus_rest', 'Webpay Plus', 'completed', '2026-11-02 10:15:00' );
	cpy_order( $cust['rosa'], [ [ $sku( 'aceite1l' ), 1 ], [ $sku( 'pasas' ), 2 ] ], 'Retiro en tienda Copiapó', 0, 'transbank_webpay_plus_rest', 'Webpay Plus', 'completed', '2026-11-05 17:40:00' );
	cpy_order( $cust['empresa'], [ [ $sku( 'cfaena' ), 40 ] ], 'Despacho corporativo (a cotizar)', 0, 'bacs', 'Transferencia bancaria (empresas)', 'on-hold', '2026-11-09 09:05:00' );
	cpy_order( $cust['camila'], [ [ $sku( 'cflorido' ), 1 ] ], 'Despacho a domicilio Starken', 3605, 'woo-mercado-pago-basic', 'Mercado Pago', 'processing', '2026-11-11 21:30:00' );
}
// ---------- Menús ----------
$menu = wp_get_nav_menu_object( 'Principal' );
$mid = $menu ? $menu->term_id : wp_create_nav_menu( 'Principal' );
if ( ! wp_get_nav_menu_items( $mid ) ) {
	foreach ( [ 'Aceites y aceitunas', 'Pasas y frutos secos', 'Dulces y conservas', 'Pajarete y licores', 'Canastas de regalo', 'Artesanía' ] as $n ) {
		$t = get_term_by( 'name', $n, 'product_cat' );
		wp_update_nav_menu_item( $mid, 0, [ 'menu-item-title' => $n, 'menu-item-object' => 'product_cat', 'menu-item-object-id' => $t->term_id, 'menu-item-type' => 'taxonomy', 'menu-item-status' => 'publish' ] );
	}
	$pg = get_page_by_path( 'canastas-corporativas' );
	wp_update_nav_menu_item( $mid, 0, [ 'menu-item-title' => 'Canastas corporativas', 'menu-item-object' => 'page', 'menu-item-object-id' => $pg->ID, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ] );
}
$sec = wp_get_nav_menu_object( 'Secundario' );
$sid = $sec ? $sec->term_id : wp_create_nav_menu( 'Secundario' );
if ( ! wp_get_nav_menu_items( $sid ) ) {
	foreach ( [ wc_get_page_id( 'myaccount' ), get_page_by_path( 'preguntas-frecuentes' )->ID, get_page_by_path( 'cambios-devoluciones-y-retracto' )->ID ] as $pid ) {
		wp_update_nav_menu_item( $sid, 0, [ 'menu-item-object' => 'page', 'menu-item-object-id' => $pid, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ] );
	}
}
set_theme_mod( 'nav_menu_locations', [ 'primary' => $mid, 'handheld' => $mid, 'secondary' => $sid ] );
echo "Cupones, clientes, pedidos y menús listos.\n";
