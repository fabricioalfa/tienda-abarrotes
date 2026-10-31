# Guía de despliegue a Hostinger (hPanel)

Paso a paso para publicar el POS. Cada bloque tiene un comando o una acción concreta,
y al final dice qué verificar antes de dar por bueno el despliegue.

Requisitos previos:

- Plan de Hostinger con **PHP 8.4** (obligatorio, ver "Por qué PHP 8.4" abajo).
- Acceso SSH o Terminal de hPanel.
- Una base de datos MySQL vacía creada en hPanel → Bases de datos.

---

## 0. Resumen rápido

Si ya sabes lo que haces, el orden es:

1. Crear BD y usuario MySQL en hPanel.
2. Subir el código con `/public` como document root.
3. `composer install --no-dev --optimize-autoloader`
4. `npm ci && npm run build`
5. `cp .env.production.example .env` y rellenarlo.
6. `php artisan key:generate`
7. `php artisan migrate --force`
8. `php artisan db:seed --force` (solo la primera vez)
9. `php artisan optimize` + permisos.
10. Verificación final.

Lo importante: **el orden importa**. Si haces `migrate` antes de tener el `.env` bien
configurado, vas a migrar contra la base equivocada.

---

## 1. Crear la base de datos

En hPanel → **Bases de datos → Gestor de bases de datos MySQL**:

- Nombre de la BD: `tienda_abarrotes` (o el que prefieras)
- Usuario: `tienda_user`
- Contraseña: generada automáticamente por hPanel

Anota los tres valores. Los necesitas en el paso 5.

Hostinger suele pedir que el usuario tenga permisos sobre la BD. Asegúrate de que
`tienda_user` esté asociado a `tienda_abarrotes`.

---

## 2. Subir el código

### Opción A: Git (recomendada, repetible)

```bash
git clone <tu-repo> /home/usuario/domains/tudominio.com/tienda
cd /home/usuario/domains/tudominio.com/tienda
```

### Opción B: Subir por FTP/archivos

Sube el contenido del proyecto **completo**, incluyendo `composer.json`,
`composer.lock`, `package.json` y `package-lock.json`. No subas `node_modules`
ni `vendor` (se regeneran aquí).

### Configurar el document root

En hPanel → **Dominios → tu dominio → Configuración del dominio → Document root**:

```
/home/usuario/domains/tudominio.com/tienda/public
```

**Este es el paso que más se olvida y el más grave si se salta.** Si el document
root se queda en la raíz del proyecto, entonces `.env`, `storage/`, `vendor/` y
`database/` quedan accesibles por HTTP. Cualquiera podría descargar tu `.env` y
leer las credenciales de la base de datos.

Comprueba que `public/.htaccess` está presente: contiene el rewrite a `index.php`.

---

## 3. Instalar dependencias de PHP

```bash
composer install --no-dev --optimize-autoloader
```

`--no-dev` deja fuera Pest, Pint y Faker. No hacen falta en producción y ocupan espacio.

Si hPanel ofrece Composer desde la interfaz web, también funciona; por SSH es más rápido.

---

## 4. Instalar y compilar los assets del front

```bash
npm ci
npm run build
```

Esto genera `public/build/`. Es obligatorio: si no existe, el login se verá sin
estilos porque `@vite` no encuentra el manifiesto.

Detalle importante: `public/build` está en `.gitignore`. Si despliegas con `git clone`,
el build **no** viene en el repositorio, por eso el paso 4 es obligatorio y no opcional.

Si `npm` no está disponible en el plan, compila en tu máquina y sube `public/build/`
por FTP. Solo funciona si el servidor no cambia la arquitectura de CPU.

---

## 5. Configurar el entorno

```bash
cp .env.production.example .env
nano .env
```

Valores que **tienes que** cambiar:

| Variable | Qué poner |
|---|---|
| `APP_URL` | `https://tudominio.com` (sin barra final) |
| `DB_DATABASE` | el nombre de la BD del paso 1 |
| `DB_USERNAME` | el usuario del paso 1 |
| `DB_PASSWORD` | la contraseña del paso 1 |
| `ADMIN_EMAIL` | el correo del primer administrador |
| `ADMIN_PASSWORD` | la contraseña, mínimo 8 caracteres |
| `MAIL_*` | solo si vas a enviar correo (ver abajo) |

Sobre el correo: la app tiene activo el flujo de "olvidé mi contraseña", que sí
envía correo. Si dejas `MAIL_MAILER=smtp` sin credenciales reales, ese flujo falla.
Dos opciones válidas:

- Configura el SMTP de Hostinger en `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`.
- Deja `MAIL_MAILER=log` durante las primeras pruebas. Los correos se escriben en
  `storage/logs/laravel.log` en vez de enviarse. Útil para verificar que todo entra
  bien antes de depender del correo.

---

## 6. Generar la llave de la aplicación

```bash
php artisan key:generate
```

Sin esto, todas las cookies de sesión y la cifrado de datos fallan, y verás errores
de tipo `MissingAppKeyException`.

---

## 7. Ejecutar las migraciones

```bash
php artisan migrate --force
```

Crea 16 tablas. Confirma que reporta `DONE` en cada migración y que no hay errores.

Para verificar el estado cuando sospeches algo:

```bash
php artisan migrate:status
```

---

## 8. Crear el primer administrador

```bash
php artisan db:seed --force
```

Solo la primera vez. Crea el administrador y las 2 categorías base
(Carnicería, Abarrotes).

El seeder tiene guardas de seguridad a propósito:

- En producción **falla** si falta `ADMIN_PASSWORD`.
- **Falla** si la contraseña tiene menos de 8 caracteres.
- **Falla** si la contraseña es literalmente `password`.
- El usuario de caja es **opcional**: si dejas `CASHIER_EMAIL` vacío, no se crea.

Verifica qué se creó:

```sql
SELECT id, email, role FROM users;
SELECT COUNT(*) FROM categories;
```

Cuando confirmes que puedes entrar con tu cuenta, borra las contraseñas del `.env`:

```
ADMIN_PASSWORD=
CASHIER_PASSWORD=
```

No hacen falta para nada más. Los usuarios de caja se dan de alta desde la app en
`/usuarios`, no por el seeder.

---

## 9. Optimizar y permisos

```bash
php artisan optimize
```

Cachea configuración, rutas, vistas y eventos. `APP_DEBUG=false` y `config:cache`
son la razón por la que el seeder ahora lee `config()` y no `env()`: con la
configuración cacheada, Laravel deja de cargar el `.env`, y `env()` devolvería `null`.

Permisos:

```bash
chmod -R 775 storage bootstrap/cache
```

Si sigue fallando al escribir logs:

```bash
chown -R usuario:usuario storage bootstrap/cache
```

No necesitas `php artisan storage:link`: la app no guarda archivos de usuario.

---

## 10. Verificación final

Recorre esta lista antes de decir que está listo.

**El Document root está en `/public`:**

```bash
curl -sI https://tudominio.com/.env | head -1
```

Si devuelve `200` o `404` en lugar de `403` o un redirect, el `.env` puede estar
expuesto. Para de inmediato y vuelve a revisar el paso 2.

**El sitio carga con estilos:**

```bash
curl -s https://tudominio.com/login | grep -o 'build/assets/[^"]*'
```

Debe listar un `.css` y un `.js`. Si sale vacío, falta el paso 4.

**El login funciona:**

1. Abre `https://tudominio.com/login`
2. Entra con las credenciales del paso 8
3. Confirma que `/dashboard` carga con estilos
4. Cierra sesión y vuelve a entrar

**Los 52 tests pasan contra el código desplegado** (opcional pero recomendado):

```bash
composer install
vendor/bin/pest
php artisan migrate:fresh --force
```

Esto **borra los datos**. Solo hazlo si la base está vacía o si es un entorno de pruebas.

**Revisa el log de errores:**

```bash
tail -f storage/logs/laravel.log
```

Debe estar limpio. Si ves `APP_KEY` missing, repite el paso 6.

---

## Por qué PHP 8.4 y no 8.3

`composer.json` declara `"php": "^8.4"`. Antes declaraba `^8.3`, pero era incorrecto:
las versiones reales de Symfony instaladas (`8.1.x`) exigen PHP `>=8.4`. Con PHP 8.3
el `composer install` falla en el paso 3.

Si hPanel te ofrece 8.2, 8.3 y 8.4, elige 8.4. Si tu plan solo llega a 8.3, hay que
cambiar el plan o bajar las dependencias; no es un ajuste de una línea.

---

## Problemas frecuentes

**"The script /public/index.php cannot be accessed directly" o pantalla en blanco**
Document root mal configurado. Revisa el paso 2.

**Estilos rotos, login sin CSS**
Falta `npm run build`. Revisa el paso 4.

**`MissingAppKeyException`**
Falta `php artisan key:generate`. Revisa el paso 6.

**`Access denied for user` al migrar**
El usuario MySQL no tiene permisos sobre esa BD, o el nombre/contraseña no coinciden.
Verifica en el panel de hPanel.

**El seeder dice "Falta la variable ADMIN_PASSWORD"**
No cambiaste el `.env`, o estás en un archivo distinto al que crees. Comprueba con:

```bash
grep ADMIN .env
```

**`vendor/autoload.php not found`**
Falta el paso 3.

**Los permisos de `storage`**
Revisa el paso 9. En Hostinger el usuario web suele ser el mismo, pero a veces hay
que usar el selector de versiones de PHP para ejecutar el comando.

---

## Cosas que este despliegue NO resuelve

Son hallazgos de la auditoría que siguen abiertos. No bloquean el arranque, pero
conviene atenderlos:

- Autorización de cierre de caja inconsistente con la de apertura. **Es el más serio.**
- `console.log` en `resources/js/app.js:9`, sobrante de depuración.
- CSV con posible formula injection en el reporte de ventas.
- Consultas sin paginar en listados.
- CSP con `'unsafe-inline'` y `'unsafe-eval'` en
  `app/Http/Middleware/PreventBackHistory.php:37`. Lo necesita Alpine.js, pero
  conviene mover Alpine a CSP con nonce.
- Rollback destructivo: varias migraciones hacen `dropColumn` sobre columnas ya
  renombradas por migraciones posteriores (`2026_04_03_060000`,
  `2026_04_03_070000`, `2026_04_04_000200`). Con `migrate` hacia adelante no hay
  problema, pero un `migrate:rollback` pierde datos.

Sobre el CSV: si algún día exportas a Excel, una celda que empiece con `=`, `+`, `-`
o `@` puede ejecutarse como fórmula. Hoy el seeder no genera esos datos, pero un
producto con un nombre así sí podría colarse.

---

## Datos que necesitas tener a mano

Antes de empezar, anota:

- [ ] Dominio completo
- [ ] Nombre de la BD, usuario y contraseña MySQL
- [ ] Correo y contraseña del primer administrador
- [ ] ¿Vas a configurar SMTP ahora o dejas `MAIL_MAILER=log`?

## Sobre el repositorio

El proyecto tiene remote en GitHub (`fabricioalfa/tienda-abarrotes`). Si despliegas
con `git clone`, tienes que subir primero los cambios de esta sesión:

```bash
git add -A
git commit -m "fix: guardas de borrado de usuario, seeder seguro y deps actualizadas"
git push
```

Ojo: `public/build`, `vendor/`, `node_modules` y `.env` están en `.gitignore`, así
que no viajan por git. En el servidor se generan con los pasos 3 y 4.

## Estado actual del repositorio

- 52 tests pasan (183 assertions).
- `composer audit`: 0 avisos de seguridad.
- PHP requerido: 8.4.
- `guzzle`, `symfony` y `laravel/framework` actualizados a versiones sin CVE conocidos.