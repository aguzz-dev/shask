# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Comandos comunes

```bash
# Servidor de desarrollo
php artisan serve

# Frontend (Vite)
npm run dev
npm run build

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Tests (Pest)
php artisan test
php artisan test --filter NombreDelTest

# Cola de trabajos
php artisan queue:work
```

## Arquitectura

**Shask** es el backend de una aplicación móvil de preguntas anónimas (disponible en Google Play). Cualquier persona puede enviar preguntas anónimas a un usuario a través de una URL pública; el usuario las responde desde la app.

### Patrón central: Database en lugar de Eloquent

**Todos los modelos extienden `App\Database`** (`app/Database.php`), no el `Model` de Eloquent. `Database` abre directamente una conexión `mysqli` y expone `$this->query($sql)` para SQL en bruto. Esto significa:

- No hay `Model::find()`, `->where()`, ni query builder de Laravel en los modelos.
- Todos los modelos ejecutan SQL crudo con interpolación de strings directa.
- `$this->dbConnection` da acceso directo al objeto `mysqli`.
- En algunos lugares se mezcla `DB::table()` de Laravel (ej. `Question::store`); esto es una inconsistencia existente.

### Autenticación personalizada

**No se usa Laravel Sanctum** a pesar de que `config/sanctum.php` existe. En su lugar, `App\Models\PersonalAccessToken` gestiona sesiones con tokens de 150 caracteres aleatorios almacenados en la tabla `personal_access_tokens`. La validación se hace manualmente llamando `(new PersonalAccessToken)->validateToken($token, $userId)` dentro de los métodos del modelo o controlador.

### Validación de requests personalizada

En vez de Form Requests de Laravel, existe `App\Request\Request` (interfaz) con clases concretas como `LoginRequest` y `RegisterUserRequest` que implementan `validate($request)` como método estático.

### Dominio

- **Posts**: cada usuario tiene posts que expiran a los 3 días; al crear un post se genera automáticamente un `PublicPost` con URL única.
- **Questions**: preguntas anónimas enviadas a un `PublicPost`. Pueden llegar desde la app móvil (API) o desde la web (`/{url}`).
- **Assets**: ítems cosméticos (iconos, colores) que los usuarios compran con `hype`. IDs > 10000 son assets subidos por usuarios (`assets` table); IDs ≤ 10000 son `public_assets`.
- **Hype**: moneda interna; el dueño del post gana 1 hype cada vez que alguien le envía una pregunta.
- **Blacklist**: sistema de bloqueo por IP usando un `random_user` anónimo para no exponer IPs a los usuarios.

### Rutas web vs API

- **`routes/web.php`**: páginas estáticas (download, política de privacidad, términos), y `/{url}` que muestra el formulario público de preguntas (vista Blade `Index`).
- **`routes/api.php`**: todos los endpoints de la app móvil (auth, posts, questions, users, assets, notificaciones FCM).

### Vistas Blade

Solo se usan para páginas estáticas y el formulario web de preguntas (`resources/views/Index.blade.php`). El frontend de la app es nativo (móvil).

### Base de datos

MySQL con conexión `mysqli` directa. Las migraciones de Laravel solo crean las tablas estándar del framework; el esquema de negocio (users, posts, questions, etc.) fue creado manualmente o via migraciones no incluidas en este repo.
