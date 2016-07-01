# Laravel-Error-Mailer
Error Mailer for Laravel 5.2

[![Latest Stable Version](https://poser.pugx.org/alverated/laravel-error-mailer/v/stable.png)](https://packagist.org/packages/alverated/laravel-error-mailer) [![Total Downloads](https://poser.pugx.org/alverated/laravel-error-mailer/downloads.png)](https://packagist.org/packages/alverated/laravel-error-mailer)

## Installation
Laravel Error Mailer can be installed via [Composer](http://getcomposer.org) by requiring the `alverated/laravel-error-mailer` package in your project's `composer.json`.
~~~json
{
    "require": {
        "alverated/laravel-error-mailer": "dev-master"
    }
}
~~~

Register the service provider with the application. Open up `config/app.php` and find the `providers` key.
~~~php
'providers' => [
    // ...
    Alverated\LaravelErrorMailer\ErrorMailerServiceProvider::class,
],
~~~

Publish the configurations
Run this on the command line from the root of your project.
~~~
$ php artisan vendor:publish
~~~
A configuration and blade file will be publish to `config/laravel-error-mailer.php` and `views/vendor/mailer.blade.php`.
Update your settings in the generated configuration file.

##Usage
Open `app/Exceptions/Handler.php` and add these two lines of codes to `public function report(Exception $e)` below `parent::report($e);`

~~~php
public function report(Exception $e)
{
    parent::report($e);

    // add this code
    $err = new ErrorMailer($e);
    $err->sendError();
}
~~~

###Note
Add this to your composer.json if you're using any of these drivers
~~~json
{
    "require": {
        "guzzlehttp/guzzle": "~5.3|~6.0",
        "aws/aws-sdk-php": "~3.0"
    }
}
~~~