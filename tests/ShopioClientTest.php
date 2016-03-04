<?php

use ShopioLabs\ShopioApi\ShopioClient;
use ShopioLabs\ShopioApi\ShopioClientException;

require_once 'TestCase.php';

class ShopioClientTest extends TestCase
{
    public function testCallException(){
        $shopioClient = new ShopioClient('movsumio.myshopio.com', 'invalid_token');
        try{
            $shopioClient->call('brands', 'GET');
            $this->fail('Exception must be thrown');
        }catch (ShopioClientException $shopioClientException){
            $this->assertEquals('The access token provided is invalid. [invalid_grant]', $shopioClientException->getMessage());
        }
    }
}