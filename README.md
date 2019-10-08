Checks environemnt contains all variables from dotenv
----

Compares application environment variables against `.env` file. It could be useful on `InitContainer` stage to validate cluster settings for PHP application deployment.
Based on [Symfony/Console](https://github.com/symfony/console) component, 

##### Installation:
```bash
composer requitre imunhatep/checkenv
```

##### Usage example:
```bash
cd ./bin
ln -s ../vendor/imunhatep/checkenv/bin/check-env 
cd ..

./bin/check-env c:c:e --dot-env=".env.dist"
```

**Note**: by default Symfony4 loads `.env` on every application invoke, check your project `config/bootstrap.php`:

Fix for `config/bootstrap.php`, to skip loading `.env` in case `APP_ENV` variable is defined.
```php
#...

// Load cached env vars if the .env.local.php file exists
// Run "composer dump-env prod" to create it (requires symfony/flex >=1.2)
if (is_array($env = @include dirname(__DIR__).'/.env.local.php')) {
    foreach ($env as $k => $v) {
        $_ENV[$k] = $_ENV[$k] ?? (isset($_SERVER[$k]) && 0 !== strpos($k, 'HTTP_') ? $_SERVER[$k] : $v);
    }
} elseif (!class_exists(Dotenv::class)) {
    throw new RuntimeException('Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
} elseif (getenv('APP_ENV') === false) {
    // load all the .env files
    (new Dotenv(false))->loadEnv(dirname(__DIR__).'/.env');
}

#...
```

Added check: `} elseif (getenv('APP_ENV') === false) {`