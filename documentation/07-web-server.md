# Web Server Setup

Production-style web serving for BusinessCmd uses **nginx + php-cgi (FastCGI)** on Windows. The `php artisan serve` workflow is preserved separately for hot-reload development.

```
Browser  →  nginx :80   →   php-cgi.exe :9000  →   application/public/index.php
                static files (build/, images, css, js)  served directly by nginx
```

---

## Directory Layout

```
businesscmd/
├── application/                  # Laravel app (docroot is application/public)
├── bin/                          # Top-level composer/artisan/nginx wrappers
│   ├── artisan.bat
│   ├── composer.bat
│   ├── nginx-start.bat
│   ├── nginx-stop.bat
│   ├── nginx-reload.bat
│   ├── php-fcgi-start.bat
│   ├── php-fcgi-stop.bat
│   ├── server-start.bat
│   └── server-stop.bat
├── scripts/                      # Workflow scripts, organized by area
│   ├── artisan/                  # Laravel/dev wrappers
│   │   ├── build.bat
│   │   ├── dev.bat
│   │   ├── fresh.bat
│   │   ├── migrate.bat
│   │   └── serve.bat
│   └── nginx/                    # Web server stack control
│       ├── nginx-start.bat
│       ├── nginx-stop.bat
│       ├── nginx-reload.bat
│       ├── php-fcgi-start.bat
│       ├── php-fcgi-stop.bat
│       ├── server-start.bat      # nginx + php-cgi together
│       └── server-stop.bat
└── web server/                   # nginx distribution
    ├── nginx.exe
    ├── conf/
    │   ├── nginx.conf            # Main config — includes businesscmd.conf
    │   └── businesscmd.conf      # Laravel server block (managed by us)
    └── logs/
        ├── businesscmd.access.log
        └── businesscmd.error.log
```

---

## Components

| Component | Binary | Listens on | Role |
|---|---|---|---|
| nginx | `web server/nginx.exe` | `:80` | HTTP server, static files, FastCGI client |
| php-cgi | `C:\Program Files\Php\php 8.5.3\php-cgi.exe` | `127.0.0.1:9000` | PHP FastCGI handler |
| Laravel | `application/public/index.php` | (via php-cgi) | App entry point |

PHP must be on `PATH` (the installer at `C:\Program Files\Php\php 8.5.3\` adds it).

---

## nginx Configuration

### `web server/conf/nginx.conf`

Stock nginx scaffold, with the placeholder `server { listen 80; root html; }` block replaced by:

```nginx
http {
    include       mime.types;
    default_type  application/octet-stream;
    sendfile      on;
    keepalive_timeout 65;

    # BusinessCmd Laravel app — served from application/public via php-cgi FastCGI
    include businesscmd.conf;
}
```

Keeping the app config in a separate include file means `nginx.conf` itself rarely needs touching, and the Laravel server block can be edited / version-controlled without reflowing the surrounding scaffolding.

### `web server/conf/businesscmd.conf`

```nginx
server {
    listen       80;
    server_name  localhost;

    root         "C:/Users/elang/Desktop/businesscmd/application/public";
    index        index.php index.html;

    charset      utf-8;
    access_log   logs/businesscmd.access.log;
    error_log    logs/businesscmd.error.log;
    client_max_body_size 64M;

    # Laravel front controller
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Pass PHP requests to php-cgi.exe FastCGI listener
    location ~ \.php$ {
        try_files                 $uri =404;
        fastcgi_split_path_info   ^(.+\.php)(/.+)$;
        fastcgi_pass              127.0.0.1:9000;
        fastcgi_index             index.php;
        fastcgi_param             SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        fastcgi_param             PATH_INFO        $fastcgi_path_info;
        include                   fastcgi_params;

        # Long-running operations (PDF generation, DIAN sync, etc.)
        fastcgi_read_timeout      300;
        fastcgi_buffers           16 16k;
        fastcgi_buffer_size       32k;
    }

    # Block dotfiles except .well-known/
    location ~ /\.(?!well-known).* {
        deny all;
    }

    error_page   500 502 503 504  /50x.html;
    location = /50x.html { root html; }
}
```

### Key decisions

| Setting | Value | Reason |
|---|---|---|
| `root` | `application/public` | Laravel's public docroot — never expose `application/` itself |
| `try_files … /index.php?$query_string` | — | Rewrite all clean URLs through Laravel's front controller |
| `client_max_body_size` | 64M | Product imports / Excel uploads / DIAN attachments |
| `fastcgi_read_timeout` | 300s | PDF rendering and DIAN sync can exceed default 60s |
| `fastcgi_buffers` / `fastcgi_buffer_size` | 16 × 16k / 32k | Inertia HTML payloads with shared props can be large |
| `location ~ /\.(?!well-known).*` | deny | Blocks `.env`, `.git`, etc. while leaving Let's Encrypt path open |

### Why nginx on Windows?

- Single static binary (`nginx.exe`) — no service install needed.
- Same config language as the eventual Linux production target — config files port over verbatim.
- Reverse-proxy / static-file separation gives the cashier UI snappy asset loading even when PHP is busy with a transaction.

---

## php-cgi (FastCGI listener)

Started by `bin/php-fcgi-start.bat`:

```cmd
php-cgi.exe -b 127.0.0.1:9000
```

### Important caveat — single-process on Windows

Windows `php-cgi.exe` ignores `PHP_FCGI_CHILDREN` and runs **one PHP request at a time**. Acceptable for a single cashier station. For multi-cashier deployments, run additional listeners and add an upstream pool:

```cmd
start "" /B php-cgi.exe -b 127.0.0.1:9000
start "" /B php-cgi.exe -b 127.0.0.1:9001
start "" /B php-cgi.exe -b 127.0.0.1:9002
start "" /B php-cgi.exe -b 127.0.0.1:9003
```

```nginx
upstream php_pool {
    server 127.0.0.1:9000;
    server 127.0.0.1:9001;
    server 127.0.0.1:9002;
    server 127.0.0.1:9003;
}

# then in the server block:
fastcgi_pass php_pool;
```

Production on Linux uses **php-fpm** instead, which manages a worker pool natively.

---

## Operations — Scripts

The same set of operations is available from two locations — pick whichever fits your workflow.

| Operation | `scripts/` (organized by area) | `bin/` (flat) |
|---|---|---|
| Run artisan | `scripts\artisan\…` (`dev.bat`, `serve.bat`, `migrate.bat`, …) | `bin\artisan.bat <args>` |
| Run composer | (use composer directly in `application/`) | `bin\composer.bat <args>` |
| Start nginx | `scripts\nginx\nginx-start.bat` | `bin\nginx-start.bat` |
| Stop nginx | `scripts\nginx\nginx-stop.bat` | `bin\nginx-stop.bat` |
| Reload nginx | `scripts\nginx\nginx-reload.bat` | `bin\nginx-reload.bat` |
| Start php-cgi | `scripts\nginx\php-fcgi-start.bat` | `bin\php-fcgi-start.bat` |
| Stop php-cgi | `scripts\nginx\php-fcgi-stop.bat` | `bin\php-fcgi-stop.bat` |
| Start full stack | `scripts\nginx\server-start.bat` | `bin\server-start.bat` |
| Stop full stack | `scripts\nginx\server-stop.bat` | `bin\server-stop.bat` |

### Behavior

| Script | What it does |
|---|---|
| `*nginx-start.bat` | Starts nginx; refuses if already running |
| `*nginx-stop.bat` | `nginx -s quit`, falls back to `taskkill` if the master is gone |
| `*nginx-reload.bat` | `nginx -t` first, then `nginx -s reload` — never reloads with broken config |
| `*php-fcgi-start.bat` | Starts `php-cgi.exe -b 127.0.0.1:9000`; refuses if already running |
| `*php-fcgi-stop.bat` | `taskkill /F /IM php-cgi.exe` |
| `*server-start.bat` | php-cgi → brief pause → nginx (in that order) |
| `*server-stop.bat` | nginx → php-cgi (reverse order) |
| `bin\artisan.bat <args>` | `pushd application/ && php artisan <args>` — e.g. `bin\artisan.bat migrate --force` |
| `bin\composer.bat <args>` | `pushd application/ && composer <args>` — e.g. `bin\composer.bat install` |

### Bring the stack up

```cmd
scripts\nginx\server-start.bat
```

App is now at **http://localhost/**.

### Bring it down

```cmd
scripts\nginx\server-stop.bat
```

### Apply config changes

Edit `web server/conf/businesscmd.conf`, then:

```cmd
scripts\nginx\nginx-reload.bat
```

---

## Coexistence with the Dev Workflow

Both setups can run side by side without conflict:

| Workflow | Entry | Use for |
|---|---|---|
| **nginx + php-cgi** (this doc) | http://localhost/ (`:80`) | Production-style smoke tests, real cashier sessions, full pre-deploy validation |
| **`scripts/dev.bat`** (`composer run dev`) | http://localhost:8000 | Hot-reload development — Vite HMR, queue listener, Reverb websocket |

`php artisan serve` and nginx target different ports and the same `application/` codebase, so switching is just a matter of which entry URL you open.

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| `nginx: [emerg] bind() to 0.0.0.0:80 failed` | Port 80 held by IIS / http.sys / Skype | `netstat -ano | findstr :80`, stop the offender or change `listen 80` → `listen 8080` in `businesscmd.conf` |
| 502 Bad Gateway | php-cgi not running or crashed | `bin\php-fcgi-start.bat` (after stopping if needed) |
| 404 on every page | `root` path wrong or file ACLs | Check `application/public/index.php` is readable; verify `root` in `businesscmd.conf` |
| 419 PAGE EXPIRED | CSRF — XSRF-TOKEN cookie not sent | See [CSRF setup](#csrf--sessions) below |
| Static assets 404 (`/build/...`) | `npm run build` not yet run | `bin\composer.bat run build` or `cd application && npm run build` |
| Session not persisting | Database session driver, but DB unreachable | Check `application/.env` `DB_*` and `SESSION_DRIVER` — see `application/config/session.php` |
| Slow first request | php-cgi cold-start | Expected; subsequent requests are fast (single-process — sequential, not parallel) |

### CSRF / sessions

The Laravel app relies on the standard CSRF flow:

- Session driver: `database` (per `config/session.php`).
- `XSRF-TOKEN` cookie is not HttpOnly (so JavaScript can read it).
- Inertia's axios reads `XSRF-TOKEN` and sends `X-XSRF-TOKEN` automatically.
- Raw `fetch()` calls go through `resources/js/lib/api.ts → apiFetch()`, which adds the header.
- `<meta name="csrf-token">` is rendered in `resources/views/app.blade.php` for third-party widgets.

No nginx-side configuration is needed for CSRF — the cookie is set by Laravel and forwarded by nginx like any other.

---

## Future Work

- **HTTPS / TLS** — uncomment the `server { listen 443 ssl; }` template in `nginx.conf` and point `ssl_certificate` / `ssl_certificate_key` at the cert files. For self-signed dev certs, generate with `openssl` and trust locally.
- **WebSocket proxy for Reverb** — add a `location /app { proxy_pass http://127.0.0.1:8080; … }` block so cashier real-time updates share the `:80` origin (currently Reverb is reached directly on `:8080`).
- **php-cgi pool** — see the upstream snippet above when concurrent cashier load justifies it.
- **gzip** — uncomment `gzip on;` in `nginx.conf` and add `gzip_types text/plain application/json text/css application/javascript;` for smaller Inertia payloads on slow networks.
- **Linux production target** — translate `businesscmd.conf` (paths) and swap `php-cgi` for `php-fpm` (`fastcgi_pass unix:/run/php/php-fpm.sock;`).
