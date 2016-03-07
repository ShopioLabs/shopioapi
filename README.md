# Shopio Rest Api Client

## Installation
```
php composer require "shopiolabs/shopioapi":"dev-master"
```

## Getting Started

### Authentication
```php
<?php

use ShopioLabs\ShopioApi\ShopioAuthClient;
use ShopioLabs\ShopioApi\ShopioClient;

require_once '../vendor/autoload.php';

$apiKey = 'YOUR_API_KEY_HERE';
$secret = 'YOUR_SECRET_HERE';
$scope = "brand_write product_read";

session_start();

if (isset($_GET['code'])) {
    $shopioAuthClient = new ShopioAuthClient($_SESSION['shop'], $apiKey, $secret, ShopioClient::PROTOCOL_HTTP);
    $accessToken = $shopioAuthClient->getAccessToken($_GET['code'], $scope, $_SESSION['page_url']);
    $shopioClient = new ShopioClient($_SESSION['shop'], $accessToken, ShopioClient::PROTOCOL_HTTP);
    session_unset();

    //List all brands
    $brands = $shopioClient->call('brands', 'GET');
    echo '<pre>';
    print_r($brands);
    exit;
} elseif (isset($_POST['shop'])) {
    // get the URL to the current page
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["SCRIPT_NAME"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["SCRIPT_NAME"];
    }
    $_SESSION['shop'] = $_POST['shop'];
    $_SESSION['page_url'] = $pageURL;

    $shopioAuthClient = new ShopioAuthClient($_SESSION['shop'], $apiKey, $secret, ShopioClient::PROTOCOL_HTTP);
    $authorizeUrl = $shopioAuthClient->getAuthorizeUrl($scope, $pageURL);
    header("Location: $authorizeUrl");
    exit;
}
?>

<form action="" method="post">
    <label for='shop'><strong>Shop Subdomain</strong></label>
    <p>
        <input id="shop" name="shop" size="45" type="text" value="" placeholder="example.myshopio.com"/>
        <input name="commit" type="submit" value="Install"/>
    </p>
</form>
```

### Client Usage
```php
$accessToken = 'youraccesstokenhere';
$shopioClient = new ShopioClient('example.myshopio.com', $accessToken);

//List brands
$brands = $shopioClient->call('brands', 'GET');

//Get single brand
$brand = $shopioClient->call('brands/'.$brands[0]['id'], 'GET');

//Create new brand
$data = [
    'title' => 'Test',
    'status' => '1'
];
$newBrand = $shopioClient->call('brands', 'POST', $data);

//Update Brand
$data = [
    'title' => 'test2',
];
$updatedBrand = $shopioClient->call('brands/'.$newBrand['id'], 'PUT', $data);

//Delete a brand
$shopioClient->call('brands/'.$updatedBrand['id'], 'DELETE');
````

## Run Tests

```
./vendor/bin/phpunit
```