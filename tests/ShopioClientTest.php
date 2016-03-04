<?php

use ShopioLabs\ShopioApi\ShopioClient;
use ShopioLabs\ShopioApi\ShopioClientException;

require_once 'TestCase.php';

class ShopioClientTest extends TestCase
{
    public function testCallException(){
        $shopioClient = new ShopioClient('subdomain.myshopio.sg', 'invalid_token', ShopioClient::PROTOCOL_HTTP);
        try{
            $shopioClient->call('brands', 'GET');
            $this->fail('Exception must be thrown');
        }catch (ShopioClientException $shopioClientException){
            $this->assertEquals('The access token provided is invalid. [invalid_grant]', $shopioClientException->getMessage());
        }
    }
}