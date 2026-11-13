# Copayapu, Sabores de Atacama · Tienda WooCommerce

Tienda de referencia construida en **WordPress + WooCommerce** para la Evaluación N.º 3 de la asignatura
E-Commerce (TIEL25) de INACAP. Copayapu es un **negocio ficticio** creado con fines académicos.

## Ver el proyecto

- **Portada del proyecto:** https://mataes3010.github.io/copayapu-tienda/
- **Prototipo navegable (mockup):** https://mataes3010.github.io/copayapu-tienda/mockup/
- **[Abrir la tienda en WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/Mataes3010/copayapu-tienda/main/blueprint.json)**

WordPress Playground es un servicio oficial de WordPress.org que ejecuta el sitio completo en el navegador.
Al abrir el enlace, la tienda se instala automáticamente; puede tardar alrededor de un minuto.
La sesión inicia como administrador, por lo que también se puede revisar el panel en `/wp-admin`.
Los cambios no se guardan entre visitas. En esta demostración el pago de prueba se hace por transferencia,
porque Playground no permite la conexión directa con Transbank; el pago con Webpay Plus se probó en la instalación local.

## Qué incluye

- WooCommerce con el tema Storefront, en español de Chile, precios en pesos chilenos con IVA de 19 % incluido.
- 15 productos regionales en 7 categorías, con stock, SKU, filtros por origen, ocasión y precio, y reseñas verificadas.
- Despacho por zonas: retiro en Copiapó, reparto local, Starken, Blue Express y envío gratis desde $45.000.
- Webpay Plus de Transbank en ambiente de integración, Mercado Pago, transferencia para empresas y FACTO para boletas.
- Ventas cruzadas y dirigidas como recomendaciones base, cupones de campaña y referidos.
- Plugin propio con la declaración de mayoría de edad y el consentimiento de ofertas en el checkout.
- Páginas de términos, privacidad (Ley N.° 21.719) y derecho a retracto.

La versión completa del entorno, con Meta for WooCommerce, Google for WooCommerce, WCPOS, UpdraftPlus y Wordfence,
se ejecuta en local con Docker (`docker-compose.yml` y scripts de `setup/`). En Playground esos plugins se omiten
porque dependen de servicios externos o de HTTPS.

## Estructura

- `blueprint.json`: receta que usa WordPress Playground para armar la tienda.
- `setup/`: scripts PHP de configuración (tienda, catálogo, operación, ajustes visuales y pagos).
- `assets/`: imágenes de productos, logo y banner de campaña.
- `docker-compose.yml`: entorno local con WordPress, MySQL y WP-CLI.
- `generar_blueprint.py`: genera `blueprint.json` con las direcciones de este repositorio.
- `index.html` y `mockup/`: portada y prototipo navegable publicados con GitHub Pages.
