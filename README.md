# Shopio Rest Api Client

## Installation
```
php composer.phar require "shopiolabs/shopioapi":"dev-master"
```

## Usage

```php
$accessToken = 'youraccesstokenhere';
$shopioClient = new ShopioClient('subdomain.myshopio.sg', $accessToken);

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
```

## Run Tests

```
./vendor/bin/phpunit
```