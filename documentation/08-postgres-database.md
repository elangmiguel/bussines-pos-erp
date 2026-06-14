## Server data

>port = 5432
>host = localhost


## 1. User creation

```sql
CREATE ROLE businesscmd NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT LOGIN NOREPLICATION NOBYPASSRLS PASSWORD '123456';
```

## 2. Database creation

```sql
CREATE DATABASE businesscmd OWNER businesscmd;
```

## 3. Replace current connection data on `application\.env`

From sqlite

```ini
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

to postgres connection
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=businesscmd
DB_USERNAME=businesscmd
DB_PASSWORD=123456
```

## 4. Migrate and seed