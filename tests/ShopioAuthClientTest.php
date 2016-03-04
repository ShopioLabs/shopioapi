<?php

use ShopioLabs\ShopioApi\ShopioAuthClient;
use ShopioLabs\ShopioApi\ShopioClientException;

require_once 'TestCase.php';

class ShopioAuthClientTest extends TestCase
{
    public function testGetAuthorizeUrl(){
        $shopioAuthClient = new ShopioAuthClient('subdomain.myshopio.sg', '12345', 'abcdefg');
        $actualUrl = $shopioAuthClient->getAuthorizeUrl('product_write page_write article_write category_write brand_write', 'http://test.shopioapps.com/authorization_code');
        $this->assertEquals("https://subdomain.myshopio.sg/oauth/v2/auth?client_id=12345&redirect_uri=http://test.shopioapps.com/authorization_code&response_type=code&scope=product_write+page_write+article_write+category_write+brand_write", $actualUrl);
    }

    public function testGetAccessTokenException(){
        $shopioAuthClient = new ShopioAuthClient('subdomain.myshopio.sg', '12345', 'abcdefg', ShopioAuthClient::PROTOCOL_HTTP);
        try{
            $shopioAuthClient->getAccessToken('test', 'product_write page_write article_write category_write brand_write');
            $this->fail('Exception must be thrown');
        }catch (ShopioClientException $shopioClientException){
        	$this->assertEquals('The client credentials are invalid [invalid_client]', $shopioClientException->getMessage());
        }
    }
}