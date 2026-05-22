# Desplegar isi-plaza-admin en Render + PostgreSQL

## Orden (importante)

1. Subir el repo a GitHub (`jagz223/isi-plaza-admin`).
2. Crear **PostgreSQL** en Render.
3. Crear **Web Service** con runtime **Docker** (no Node).
4. Vincular la base de datos y pegar variables de entorno.
5. Primer deploy → migraciones automáticas en `entrypoint.sh`.

---

## Paso 1 — Base de datos en Render

1. Dashboard → **New +** → **PostgreSQL**.
2. Name: `isi-plaza-db`
3. Database: `isi_plaza`
4. User: `isi_plaza`
5. Plan: **Free**
6. Crear y copiar **Internal Database URL** (empieza con `postgresql://`).

---

## Paso 2 — Web Service (formulario)

| Campo | Valor |
|-------|--------|
| **Source** | Repo `jagz223/isi-plaza-admin` |
| **Name** | `isi-plaza-admin` |
| **Language** | **Docker** (no Node) |
| **Branch** | `main` |
| **Region** | Oregon (o la más cercana) |
| **Root Directory** | *(vacío)* |
| **Instance type** | Free |

Con **Docker**, Render usa el `Dockerfile` del repo. **No** uses:

- Build: `npm install; npm run build` (eso es para Node puro)
- Start: `yarn start`

El build y el arranque los hace el `Dockerfile` + `docker/entrypoint.sh`.

**Health Check Path:** `/up`

---

## Paso 3 — Vincular PostgreSQL

En el Web Service → **Environment** → **Add from database** → elige `isi-plaza-db`.

Render inyecta `DATABASE_URL`. En Laravel configura:

| Variable | Valor |
|----------|--------|
| `DB_CONNECTION` | `pgsql` |

Laravel lee `DATABASE_URL` vía `config/database.php` (fallback en conexión `pgsql`).

---

## Paso 4 — Variables de entorno (manual)

Genera `APP_KEY` en local: `php artisan key:generate --show` o deja que Render la genere si usas Blueprint.

| Key | Value |
|-----|--------|
| `APP_NAME` | `ISI Plaza` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://isi-plaza-admin.onrender.com` *(tu URL real)* |
| `APP_KEY` | `base64:...` |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `pgsql` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `FIREBASE_PROJECT_ID` | `isi-plaza-bf7f0` |
| `FIRESTORE_ACCESS_COLLECTION` | `platform` |
| `FIRESTORE_ACCESS_DOCUMENT` | `access` |
| `FIRESTORE_ACCESS_FIELD` | `app_enabled` |
| `PLATFORM_ACCESS_FAIL_OPEN` | `true` |
| `PLATFORM_ACCESS_CACHE_SECONDS` | `5` |
| `FIREBASE_SERVICE_ACCOUNT_JSON` | *(pegar JSON completo del service account, una línea)* |

**Firebase en Render:** abre `storage/app/firebase/....json`, copia **todo** el contenido y pégalo en `FIREBASE_SERVICE_ACCOUNT_JSON` (tipo Secret). No subas el archivo al repo.

Opcional tras el primer deploy:

```bash
php artisan db:seed
```

(ejecutar en Render Shell si tienes seeders.)

---

## Paso 5 — Apps móviles (buyer / seller)

En `.env` de cada app:

```env
EXPO_PUBLIC_API_URL=https://isi-plaza-admin.onrender.com
```

Firestore sigue en Firebase (mismo proyecto).

---

## Notas

- El plan **Free** duerme tras inactividad (~50 s al despertar).
- Archivos subidos (banners) en disco local **no persisten** en Free; para producción usa S3/R2 más adelante.
- Firestore no va en Render; sigue en Firebase Console.
