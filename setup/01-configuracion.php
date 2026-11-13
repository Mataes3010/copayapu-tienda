<?php
/**
 * Copayapu - configuración general de la tienda WooCommerce.
 * Ejecutar con: wp eval-file /setup/01-configuracion.php
 */
if ( ! defined( 'CPY_ASSETS' ) ) { define( 'CPY_ASSETS', '/assets/' ); }
if ( ! defined( 'CPY_SETUP' ) ) { define( 'CPY_SETUP', '/setup/' ); }
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function cpy_media( $file, $title ) {
	$existing = get_posts( [ 'post_type' => 'attachment', 'title' => $title, 'posts_per_page' => 1, 'fields' => 'ids' ] );
	if ( $existing ) { return $existing[0]; }
	$tmp = wp_tempnam( basename( $file ) );
	copy( CPY_ASSETS . $file, $tmp );
	$id = media_handle_sideload( [ 'name' => $file, 'tmp_name' => $tmp ], 0, $title );
	return is_wp_error( $id ) ? 0 : $id;
}
function cpy_page( $title, $content, $slug, $template = '' ) {
	$p = get_page_by_path( $slug );
	$data = [ 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content, 'post_status' => 'publish', 'post_type' => 'page' ];
	if ( $p ) { $data['ID'] = $p->ID; $id = wp_update_post( $data ); } else { $id = wp_insert_post( $data ); }
	if ( $template ) { update_post_meta( $id, '_wp_page_template', $template ); }
	return $id;
}

// ---------- Tienda, moneda e impuestos (RF06, RF11) ----------
$opts = [
	'woocommerce_store_address' => 'Calle Atacama 520', 'woocommerce_store_address_2' => 'Centro',
	'woocommerce_store_city' => 'Copiapó', 'woocommerce_default_country' => 'CL:CL-AT', 'woocommerce_store_postcode' => '1530000',
	'woocommerce_currency' => 'CLP', 'woocommerce_currency_pos' => 'left', 'woocommerce_price_thousand_sep' => '.',
	'woocommerce_price_decimal_sep' => ',', 'woocommerce_price_num_decimals' => '0',
	'woocommerce_allowed_countries' => 'specific', 'woocommerce_specific_allowed_countries' => [ 'CL' ], 'woocommerce_ship_to_countries' => '',
	'woocommerce_calc_taxes' => 'yes', 'woocommerce_prices_include_tax' => 'yes', 'woocommerce_tax_display_shop' => 'incl',
	'woocommerce_tax_display_cart' => 'incl', 'woocommerce_tax_total_display' => 'single', 'woocommerce_tax_based_on' => 'base',
	'woocommerce_weight_unit' => 'kg', 'woocommerce_dimension_unit' => 'cm',
	// Cuentas e invitados (RF01)
	'woocommerce_enable_guest_checkout' => 'yes', 'woocommerce_enable_checkout_login_reminder' => 'yes',
	'woocommerce_enable_signup_and_login_from_checkout' => 'yes', 'woocommerce_enable_myaccount_registration' => 'yes',
	// Reseñas verificadas (RF13)
	'woocommerce_enable_reviews' => 'yes', 'woocommerce_enable_review_rating' => 'yes', 'woocommerce_review_rating_required' => 'yes',
	'woocommerce_review_rating_verification_label' => 'yes', 'woocommerce_review_rating_verification_required' => 'yes',
	// Inventario (RF08)
	'woocommerce_manage_stock' => 'yes', 'woocommerce_notify_low_stock_amount' => 5, 'woocommerce_notify_no_stock_amount' => 0,
	'woocommerce_hold_stock_minutes' => 60, 'woocommerce_stock_format' => '', 'woocommerce_hide_out_of_stock_items' => 'no',
	// Tienda publicada y sin asistentes
	'woocommerce_coming_soon' => 'no', 'woocommerce_store_pages_only' => 'no', 'woocommerce_task_list_hidden' => 'yes',
	'woocommerce_task_list_complete' => 'yes', 'woocommerce_show_marketplace_suggestions' => 'no', 'woocommerce_allow_tracking' => 'no',
	'woocommerce_email_from_name' => 'Copayapu, Sabores de Atacama', 'woocommerce_email_from_address' => 'pedidos@copayapu.local',
	'woocommerce_cart_redirect_after_add' => 'no', 'woocommerce_enable_ajax_add_to_cart' => 'yes',
];
foreach ( $opts as $k => $v ) { update_option( $k, $v ); }
update_option( 'woocommerce_onboarding_profile', [ 'skipped' => true ] );

global $wpdb;
if ( ! $wpdb->get_var( "SELECT tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_name='IVA'" ) ) {
	WC_Tax::_insert_tax_rate( [ 'tax_rate_country' => 'CL', 'tax_rate_state' => '', 'tax_rate' => '19.0000', 'tax_rate_name' => 'IVA',
		'tax_rate_priority' => 1, 'tax_rate_compound' => 0, 'tax_rate_shipping' => 1, 'tax_rate_order' => 0, 'tax_rate_class' => '' ] );
}

// ---------- Zonas de despacho (RF09) ----------
// Los costos se ingresan netos; con IVA quedan en $2.990, $4.290 y $3.990.
foreach ( WC_Shipping_Zones::get_zones() as $z ) { WC_Shipping_Zones::delete_zone( $z['id'] ); }
$z1 = new WC_Shipping_Zone();
$z1->set_zone_name( 'Región de Atacama (retiro y reparto local)' );
$z1->set_zone_order( 1 );
$z1->add_location( 'CL:CL-AT', 'state' );
$z1->save();
$i = $z1->add_shipping_method( 'local_pickup' );
update_option( "woocommerce_local_pickup_{$i}_settings", [ 'title' => 'Retiro en tienda Copiapó (gratis)', 'tax_status' => 'none', 'cost' => '0' ] );
$i = $z1->add_shipping_method( 'flat_rate' );
update_option( "woocommerce_flat_rate_{$i}_settings", [ 'title' => 'Reparto local en Copiapó (mismo día)', 'tax_status' => 'taxable', 'cost' => '2513' ] );
$i = $z1->add_shipping_method( 'free_shipping' );
update_option( "woocommerce_free_shipping_{$i}_settings", [ 'title' => 'Envío gratis sobre $45.000', 'requires' => 'min_amount', 'min_amount' => '45000', 'ignore_discounts' => 'no' ] );

$z2 = new WC_Shipping_Zone();
$z2->set_zone_name( 'Resto de Chile (courier)' );
$z2->set_zone_order( 2 );
$z2->add_location( 'CL', 'country' );
$z2->save();
$i = $z2->add_shipping_method( 'flat_rate' );
update_option( "woocommerce_flat_rate_{$i}_settings", [ 'title' => 'Despacho a domicilio Starken (2 a 3 días hábiles)', 'tax_status' => 'taxable', 'cost' => '3605' ] );
$i = $z2->add_shipping_method( 'flat_rate' );
update_option( "woocommerce_flat_rate_{$i}_settings", [ 'title' => 'Despacho a domicilio Blue Express (2 a 3 días hábiles)', 'tax_status' => 'taxable', 'cost' => '3353' ] );
$i = $z2->add_shipping_method( 'free_shipping' );
update_option( "woocommerce_free_shipping_{$i}_settings", [ 'title' => 'Envío gratis sobre $45.000', 'requires' => 'min_amount', 'min_amount' => '45000', 'ignore_discounts' => 'no' ] );

// ---------- Medios de pago (RF06) ----------
update_option( 'woocommerce_bacs_settings', [ 'enabled' => 'yes', 'title' => 'Transferencia bancaria (empresas)',
	'description' => 'Para canastas corporativas. Te enviaremos la factura y los datos de la cuenta.',
	'instructions' => 'Transfiere el total indicando el número de pedido. Despachamos al confirmar el pago.' ] );
update_option( 'woocommerce_bacs_accounts', [ [ 'account_name' => 'Copayapu SpA (ejemplo)', 'account_number' => '00-000-00000-00',
	'bank_name' => 'Banco de ejemplo', 'sort_code' => '', 'iban' => '', 'bic' => '' ] ] );
update_option( 'woocommerce_cod_settings', [ 'enabled' => 'no' ] );
update_option( 'woocommerce_cheque_settings', [ 'enabled' => 'no' ] );

// ---------- Páginas y políticas (RF10, RF20) ----------
$terms = cpy_page( 'Términos y condiciones', "<!-- wp:paragraph --><p>Copayapu (negocio ficticio con fines académicos) vende productos regionales de la Región de Atacama a todo Chile. Los precios incluyen IVA y se informan junto al costo y plazo de despacho antes del pago.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Los productos con alcohol (pajarete) solo se venden a mayores de 18 años (Ley N.° 19.925). El repartidor podrá solicitar la cédula al entregar.</p><!-- /wp:paragraph -->", 'terminos-y-condiciones' );
cpy_page( 'Cambios, devoluciones y derecho a retracto', "<!-- wp:paragraph --><p>Tienes <strong>derecho a retracto dentro de 10 días</strong> desde que recibes tu pedido (Ley N.° 19.496, art. 3 bis, modificada por la Ley N.° 21.398). Escríbenos por WhatsApp o correo y coordinamos el retiro.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><strong>Sin derecho a retracto:</strong> las canastas corporativas con tarjeta y logo de la empresa se arman a pedido, por lo que no admiten retracto. Esta condición se informa antes del pago.</p><!-- /wp:paragraph -->", 'cambios-devoluciones-y-retracto' );
$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
$privacy = cpy_page( 'Política de privacidad', "<!-- wp:paragraph --><p>Tratamos tus datos según la Ley N.° 21.719. Usamos tu historial de compras para recomendarte productos solo si lo autorizas, y puedes oponerte en cualquier momento desde <em>Mi cuenta</em>. Puedes ejercer tus derechos de acceso, rectificación, supresión, oposición, portabilidad y bloqueo escribiendo a privacidad@copayapu.local.</p><!-- /wp:paragraph -->", 'politica-de-privacidad' );
update_option( 'wp_page_for_privacy_policy', $privacy );
update_option( 'woocommerce_terms_page_id', $terms );
cpy_page( 'Canastas corporativas', "<!-- wp:heading --><h2>Canastas corporativas con sabores de Atacama</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Desde 30 unidades, con tarjeta personalizada, despacho a una o varias direcciones y factura electrónica. Descuento por volumen: 5 % (30 a 99), 10 % (100 a 299) y 15 % (300 o más).</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Solicita tu cotización a empresas@copayapu.local o por WhatsApp.</p><!-- /wp:paragraph -->", 'canastas-corporativas' );
cpy_page( 'Preguntas frecuentes', "<!-- wp:paragraph --><p><strong>¿Cuánto demora el despacho?</strong> De 24 a 72 horas hábiles a todo Chile. En Copiapó puedes retirar el mismo día.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><strong>¿Cómo pago?</strong> Con Webpay Plus (débito, crédito o prepago), Mercado Pago o transferencia para empresas.</p><!-- /wp:paragraph -->", 'preguntas-frecuentes' );

// Portada con la plantilla de inicio de Storefront y banner de campaña
$banner = cpy_media( 'banner.png', 'Campaña Desierto Florido' );
$home = cpy_page( 'Inicio', "<!-- wp:paragraph --><p>Aceite de oliva D. O. Valle del Huasco, pasas de Copiapó, chañar, miel y canastas de regalo de productores locales. Despachamos a todo Chile en 24 a 72 horas hábiles.</p><!-- /wp:paragraph -->", 'inicio', 'template-homepage.php' );
if ( $banner ) { set_post_thumbnail( $home, $banner ); }
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
wp_delete_post( 1, true ); // "Hola, mundo"
$sample = get_page_by_path( 'pagina-ejemplo' ); if ( $sample ) { wp_delete_post( $sample->ID, true ); }
$sample = get_page_by_path( 'sample-page' ); if ( $sample ) { wp_delete_post( $sample->ID, true ); }

// ---------- Identidad visual de Storefront ----------
$logo = cpy_media( 'logo.png', 'Logo Copayapu' );
if ( $logo ) { set_theme_mod( 'custom_logo', $logo ); }
$mods = [
	'storefront_header_background_color' => '#ffffff', 'storefront_header_text_color' => '#5A6351', 'storefront_header_link_color' => '#2F3D14',
	'storefront_heading_color' => '#1E2619', 'storefront_text_color' => '#3D4636', 'storefront_accent_color' => '#B4245C',
	'storefront_button_background_color' => '#46591F', 'storefront_button_text_color' => '#ffffff',
	'storefront_button_alt_background_color' => '#B4245C', 'storefront_button_alt_text_color' => '#ffffff',
	'storefront_footer_background_color' => '#2F3D14', 'storefront_footer_heading_color' => '#ffffff',
	'storefront_footer_text_color' => '#DCE3CB', 'storefront_footer_link_color' => '#ffffff',
	'background_color' => 'ffffff',
];
foreach ( $mods as $k => $v ) { set_theme_mod( $k, $v ); }
echo "Configuración lista. Inicio={$home} Términos={$terms} Privacidad={$privacy}\n";
