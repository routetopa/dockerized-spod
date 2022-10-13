# Blade Materialize

Blade Materialize extends Blade (The Laravel templating engine) with macros and helpers that simplify the use of the Materialize framework.

## Installation

### Get the package
Require using Composer:
```
composer require lucavicidomini/blade-materialize
```

### Register the service provider
If you want to use the `@` macros, you need to extend Blade in `app\Providers\AppServiceProvider.php`: 
```php
public function boot()
{
    LucaVicidomini\BladeMaterialize\BladeExtender::extend();
}
```

Add the provider in `config/app.php`:
```php
'providers' => [
    // ...
    LucaVicidomini\BladeMaterialize\MaterializeServiceProvider::class,
    // ...
```

Add the facades in `config/app.php`:
```php
'aliases' => [
    // ...
    'MHtml' => LucaVicidomini\BladeMaterialize\Facades\HtmlFacade::class,
    'MForm' => LucaVicidomini\BladeMaterialize\Facades\FormFacade::class,
    // ...
```

## Usage

_... soon ..._
