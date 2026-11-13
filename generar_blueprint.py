"""Genera blueprint.json para WordPress Playground a partir del usuario y repositorio de GitHub.

Uso:  python generar_blueprint.py <usuario-github> [repositorio] [rama]
"""
import json
import pathlib
import sys

user = sys.argv[1]
repo = sys.argv[2] if len(sys.argv) > 2 else 'copayapu-tienda'
branch = sys.argv[3] if len(sys.argv) > 3 else 'main'
base = f'https://raw.githubusercontent.com/{user}/{repo}/{branch}/'
root = pathlib.Path(__file__).parent
DEST = '/wordpress/wp-content/copayapu'

plugins = ['woocommerce', 'transbank-webpay-plus-rest', 'woocommerce-mercadopago',
           'bluex-for-woocommerce', 'calculo-de-despacho-via-starken-para-woocommerce',
           'facto-facturacioacuten-electroacutenica']

steps = [
    {'step': 'setSiteOptions', 'options': {
        'blogname': 'Copayapu, Sabores de Atacama',
        'blogdescription': 'Productos de los valles de Copiapó y del Huasco',
        'timezone_string': 'America/Santiago', 'date_format': 'd/m/Y',
        'permalink_structure': '/%postname%/'}},
]
for slug in plugins:
    steps.append({'step': 'installPlugin', 'pluginData': {'resource': 'wordpress.org/plugins', 'slug': slug},
                  'options': {'activate': True}})
steps.append({'step': 'installTheme', 'themeData': {'resource': 'wordpress.org/themes', 'slug': 'storefront'},
              'options': {'activate': True}})
steps.append({'step': 'setSiteLanguage', 'language': 'es_CL'})
steps += [{'step': 'mkdir', 'path': f'{DEST}/assets'}, {'step': 'mkdir', 'path': f'{DEST}/setup'}]
for png in sorted((root / 'assets').glob('*.png')):
    steps.append({'step': 'writeFile', 'path': f'{DEST}/assets/{png.name}',
                  'data': {'resource': 'url', 'url': f'{base}assets/{png.name}'}})
for php in sorted((root / 'setup').glob('*.php')):
    steps.append({'step': 'writeFile', 'path': f'{DEST}/setup/{php.name}',
                  'data': {'resource': 'url', 'url': f'{base}setup/{php.name}'}})
steps.append({'step': 'writeFile', 'path': '/wordpress/wp-content/mu-plugins/copayapu-checkout.php',
              'data': {'resource': 'url', 'url': f'{base}setup/copayapu-checkout.php'}})
for script in ['01-configuracion.php', '02-catalogo.php', '03-operacion.php', '04-ajustes-visuales.php', '05-pagos.php', '06-playground.php']:
    steps.append({'step': 'runPHP', 'code':
                  f"<?php define('CPY_ASSETS', '{DEST}/assets/'); define('CPY_SETUP', '{DEST}/setup/'); "
                  f"require_once '/wordpress/wp-load.php'; require_once ABSPATH . 'wp-admin/includes/plugin.php'; "
                  f"require '{DEST}/setup/{script}';"})
steps.append({'step': 'runPHP', 'code': "<?php require_once '/wordpress/wp-load.php'; flush_rewrite_rules( true );"})

blueprint = {
    '$schema': 'https://playground.wordpress.net/blueprint-schema.json',
    'meta': {
        'title': 'Copayapu, Sabores de Atacama',
        'description': 'Tienda WooCommerce de referencia para la Evaluación N.º 3 de E-Commerce (INACAP). Negocio ficticio.',
        'author': user,
        'categories': ['WooCommerce'],
    },
    'landingPage': '/',
    'preferredVersions': {'php': '8.3', 'wp': 'latest'},
    'features': {'networking': True},
    'login': True,
    'steps': steps,
}
out = root / 'blueprint.json'
out.write_text(json.dumps(blueprint, ensure_ascii=False, indent=2), encoding='utf-8')
link = 'https://playground.wordpress.net/?blueprint-url=' + base + 'blueprint.json'

readme = f"""# Copayapu, Sabores de Atacama · Tienda WooCommerce

Tienda de referencia construida en **WordPress + WooCommerce** para la Evaluación N.º 3 de la asignatura
E-Commerce (TIEL25) de INACAP. Copayapu es un **negocio ficticio** creado con fines académicos.

## Ver el proyecto

- **Portada del proyecto:** https://{user.lower()}.github.io/{repo}/
- **Prototipo navegable (mockup):** https://{user.lower()}.github.io/{repo}/mockup/
- **[Abrir la tienda en WordPress Playground]({link})**

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
"""
(root / 'README.md').write_text(readme, encoding='utf-8')
print(out, len(steps), 'pasos')
print('Enlace:', link)
