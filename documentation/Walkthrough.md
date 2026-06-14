# Requirements

- php 8.5.3
- Composer

```
# php.ini 

;;;;;;;;;;;;;;;;;;;;;;
; Dynamic Extensions ;
;;;;;;;;;;;;;;;;;;;;;;

extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=pdo_pgsql
extension=pdo_sqlite
extension=sqlite3
```


# initialize project

- Create and use working directory

- Create laravel project via composer: (Deprecated)
`composer create-project "laravel/laravel:^12.53.0" <application>`

- Install "Laravel Installer"
`composer require "laravel/installer^5.24"`

- Create the application
`php .\vendor\laravel\installer\bin\laravel new <application>`

Apply:
- svelte
- laravel built-in auth
- pest
 
# run server  

`composer run dev`

- Local:   [http://localhost:5173/](http://localhost:5173/)
- Network: use --host to expose
- APP_URL: [http://localhost:8000](http://localhost:8000)
