# UberBackendTest

Este repositorio contiene el código fuente del backend para el proyecto UberBackendTest. Este proyecto utiliza Laravel y Laravel WebSockets para gestionar servicios de transporte.

## Requisitos

- PHP >= 7.3
- Composer
- MySQL
- Node.js
- Redis
- ...

## Instalación

1. Clona este repositorio en tu máquina local:

```bash
git clone https://github.com/juanpabloperez98/UberBackendTest.git
```


Instala las dependencias de PHP utilizando Composer:

```bash
composer install
```

Copia el archivo .env.example y renómbralo a .env. Luego, configura las variables de entorno necesarias, como las credenciales de la base de datos y la configuración de Laravel Passport.


```
php artisan key:generate
```

Ejecuta las migraciones para crear las tablas de la base de datos:

```
php artisan migrate
```

Inicia el servidor de desarrollo de Laravel:

```
php artisan serve
```
