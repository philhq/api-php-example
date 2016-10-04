Example for interacting with NationalNet's REST API (PHP)
=========================================================

First, clone this repository.

```
git clone https://github.com/NationalNet/api-php-example.git
```

Next, install composer

```
composer install
```

Next, edit the following lines and enter your mynatnet username and api key.

```php
// Load composer
require __DIR__ . "/vendor/autoload.php";

$user = ''; // your myNatNet username
$api_key = ''; // api-key string found in myNatNet user profile

$api = new NationalNet\API($user, $api_key);
$graphs = $api->graphs();

print_r($graphs);
```

Now, you should be able to run the script.
