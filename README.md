# GastroHub

Prototipo Laravel para restaurantes con pagina publica, menu digital, mesas por QR, pedidos compartidos por mesa y pagos simulados.

## Stack

- Laravel 13
- Blade
- Tailwind CSS con Vite
- JavaScript ligero en la vista de mesa
- Eloquent ORM
- PHPUnit
- endroid/qr-code para generar QR en SVG

## Instalacion

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
php artisan migrate:fresh --seed
npm run build
```

Para desarrollo local:

```bash
composer run dev
```

En Windows, el script de desarrollo usa `scripts/dev.mjs` y evita iniciar Laravel Pail cuando la extension `pcntl` no esta disponible.

## Restablecer Demo

El comando oficial para dejar el sistema listo para presentar es:

```bash
php artisan migrate:fresh --seed
```

Esto crea:

- Restaurante configurado: GastroHub Bistro.
- Administrador de demostracion.
- 4 categorias.
- 12 productos.
- 6 mesas activas con QR.
- Sesiones y pedidos historicos cerrados.
- Ninguna sesion activa por defecto.

## Credenciales

- URL: `/login`
- Email: `admin@restaurante.test`
- Contrasena: `password`

## Rutas Importantes

- `/` pagina publica del restaurante.
- `/menu` menu digital publico.
- `/login` acceso administrativo.
- `/admin` dashboard.
- `/admin/products` productos.
- `/admin/categories` categorias.
- `/admin/tables` mesas, enlaces y QR.
- `/admin/orders` pedidos agrupados por mesa.
- `/admin/settings` configuracion del restaurante.
- `/table/{qr_token}` entrada publica de una mesa por QR.

## Recorrido De Demostracion

1. Ejecuta `php artisan migrate:fresh --seed`.
2. Entra a `/login` con `admin@restaurante.test` y `password`.
3. Abre `/admin/products` para mostrar categorias, platos, precios, fotos de respaldo, disponibilidad y destacados.
4. Abre `/admin/tables` y usa `Ver enlace` en una mesa disponible.
5. En la mesa, elige `Cuentas separadas`.
6. Entra como primer cliente, por ejemplo `Daniel`, agrega productos y marca `Enviar mi pedido y agregar otra persona`.
7. Abre el mismo enlace en una ventana de incognito y entra como segundo cliente, por ejemplo `Ana`.
8. Agrega productos para Ana y marca su seleccion como lista.
9. Vuelve al navegador del encargado y confirma el pedido general.
10. En `/admin/orders`, cambia el pedido general de `Nuevo` a `Preparando` y luego a `Entregado`.
11. Vuelve a la mesa y revisa que aparezca la cuenta cuando todos los pedidos esten entregados.
12. Prueba `Pagar lo mio` en cuentas separadas o crea otra mesa con `Pago en conjunto` para ver solo `Pagar cuenta`.
13. En `/admin/tables`, cierra la mesa cuando la cuenta quede pagada.

## Decisiones Tecnicas

- Los precios se guardan como enteros en pesos colombianos.
- Los pedidos guardan copia historica de nombre y precio del producto.
- Las rutas publicas de mesa usan `qr_token`; los participantes usan `guest_token`.
- La cuenta se calcula en backend mediante `TableBillingService`.
- Las transiciones de pedidos viven en `OrderStatusService`.
- El panel de pedidos agrupa por sesion de mesa y separa pedido general de adicionales.
- Los pagos son simulados y se marcan como pagados inmediatamente.
- La mesa no se cierra automaticamente al pagar; el administrador confirma el cierre.

## Comandos De Calidad

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

## Limitaciones Del Prototipo

- No hay pagos reales ni pasarela.
- No hay facturacion electronica.
- No hay inventario.
- No hay reservas, domicilios ni multiples sedes.
- No hay roles complejos; existe un unico tipo de administrador.
- La actualizacion de pedidos es manual, sin WebSockets.
- Las imagenes de demo usan respaldo local cuando no se carga una imagen propia.

## Futuras Funcionalidades Posibles

- Notificaciones en tiempo real para cocina.
- Impresion de comandas.
- Variantes o modificadores de productos.
- Propinas y descuentos.
- Reportes comerciales por rango de fechas.
- Multiusuario administrativo con permisos.
- Integracion con pagos reales.
