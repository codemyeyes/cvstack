### Step

###### REF :: https://docs.mongodb.com/php-library/v1.2/tutorial/crud/

- aplication/config/xxx.example.php -> xxx.php 
- aplication/helper/xxx.example.php -> xxx.php 

- database/migrate/default/.env.example -> .env

```shell
> cd docker_cvstack
> docker-compose up -d

> docker ps

> docker exec -it docker_cvstack-php_apache-1 bash
> compose install

> cd database/migrate/default
> composer install
```

#### DATABASE MIGRATE LARAVEL
##### REF :: https://laravel.com/docs/8.x/migrations
```shell
> php artisan make:migration <name_file> <--create|--table>=<table_name>
> php artisan migrate
> php artisan migrate:rollback
```
