# Laravel Admin Panel

forked from [the-control-group/voyager](https://github.com/the-control-group/voyager)

Made with ❤️

Website & Documentation: http://laraveladminpanel.com

<hr>

Laravel Admin & CRUD System (Browse, Read, Edit, Add, & Delete), supporting Laravel 5.4 and newer!

## Installation Steps

### 1. Require the Package

After creating your new Laravel application you can include the Admin Panel package with the following command: 

```bash
composer require laraveladminpanel/admin
```

### 2. Add the DB Credentials & APP_URL

Next make sure to create a new database and add your database credentials to your .env file:

```
DB_HOST=localhost
DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret
```

You will also want to update your website URL inside of the `APP_URL` variable inside the .env file:

```
APP_URL=http://localhost:8000
```

> Only if you are on Laravel 5.4 will you need to [Add the Service Provider.](https://admin.readme.io/docs/adding-the-service-provider)

### 3. Run The Installer

Lastly, we can install admin. You can do this either with or without dummy data.
The dummy data will include 1 admin account (if no users already exists), 1 demo page, 4 demo posts, 2 categories and 7 settings.

To install Admin Panel without dummy simply run

```bash
php artisan admin:install
```

If you prefer installing it with dummy run

```bash
php artisan admin:install --with-dummy
```

> Troubleshooting: **Specified key was too long error**. If you see this error message you have an outdated version of MySQL, use the following solution: https://laravel-news.com/laravel-5-4-key-too-long-error

And we're all good to go!

Start up a local development server with `php artisan serve` And, visit [http://localhost:8000/admin](http://localhost:8000/admin).

## Creating an Admin User

If you did go ahead with the dummy data, a user should have been created for you with the following login credentials:

>**email:** `admin@admin.com`   
>**password:** `password`

NOTE: Please note that a dummy user is **only** created if there are no current users in your database.

If you did not go with the dummy user, you may wish to assign admin privileges to an existing user.
This can easily be done by running this command:

```bash
php artisan admin:admin your@email.com
```

If you did not install the dummy data and you wish to create a new admin user you can pass the `--create` flag, like so:

```bash
php artisan admin:admin your@email.com --create
```

And you will be prompted for the user's name and password.


For phpunit, you need to run these commands in the root folder of the site:


```bash
composer require orchestra/database ^3.5 --dev
composer require orchestra/testbench-browser-kit ^3.5 --dev
```

And add line "LaravelAdminPanel\\Tests\\": "vendor/laraveladminpanel/admin/tests/" in a composer.json


```
"autoload-dev": {
    "psr-4": {
        "Tests\\": "tests/",
        "LaravelAdminPanel\\Tests\\": "vendor/laraveladminpanel/admin/tests/"
    }
},
```


And finally run this command:


```bash
composer dump-autoload
```
