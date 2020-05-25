# Usersnap
Integration of
https://app.usersnap.com/

## Installation
Require this package with composer.

```shell
composer require dazlab/laravel-usersnap-plugin
```

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

Set `USERSNAP_APP_KEY` in `.env`.
`USERSNAP_ENABLED` is true bu default.

### Laravel < 5.5:

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
DazLab\Usersnap\ServiceProvider::class,
```

#### Copy the package config to your local config with the publish command:

```shell
php artisan vendor:publish --provider="DazLab\Usersnap\ServiceProvider"
```
