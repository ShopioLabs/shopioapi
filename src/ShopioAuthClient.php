<?php namespace ShopioLabs\ShopioApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class ShopioAuthClient
 * @package ShopioLabs\ShopioApi
 */
class ShopioAuthClient {

    /**
     * @var string http protocol
     */
    const PROTOCOL_HTTP = 'http://';

    /**
     * @var string https protocol
     */
    const PROTOCOL_HTTPS = 'https://';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $shopSubdomain;

    /**
     * @var string
     */
    private $apiProtocol;

    /**
     * @var array
     */
    private $tokenInfo;

    /**
     * @param $shopSubdomain
     * @param $apiKey
     * @param $secret
     * @param string $apiProtocol
     */
    function __construct($shopSubdomain, $apiKey, $secret, $apiProtocol = self::PROTOCOL_HTTPS)
    {
        $this->setApiKey($apiKey);
        $this->setSecret($secret);
        $this->setShopSubdomain($shopSubdomain);
        $this->setApiProtocol($apiProtocol);
    }

    /**
     * Get the URL required to request authorization
     * @param string $scope Example: brand_write menu_write option_write theme_write
     * @param string string $redirectUrl
     * @return string
     */
    public function getAuthorizeUrl($scope, $redirectUrl)
    {
        $apiKey = $this->getApiKey();
        return $this->getShopDomain()."/oauth/v2/auth?client_id=$apiKey&redirect_uri=$redirectUrl&response_type=code&scope=".urlencode($scope);
    }

    /**
     * Gives an access_token information
     * @param $code
     * @param $scope
     * @return string
     * @throws ShopioClientException
     * @throws RequestException
     */
    public function getAccessToken($code, $scope){
        $apiKey = $this->getApiKey();
        $secret = $this->getSecret();
        $url = $this->getShopDomain()."/oauth/v2/token?client_id=$apiKey&client_secret=$secret&grant_type=authorization_code&&code=$code";

        try {
            $client = new Client();
            $response = $client->get($url);
        } catch (RequestException $requestException) {
            throw new ShopioClientException($this->getHumanReadableErrorMessage($requestException));
        }

        $tokenInfo = json_decode($response->getBody(), true);

        if (!is_array($tokenInfo)) {
            throw new ShopioClientException("Token information expected to be an array. " . $response->getBody());
        }

        if(empty($tokenInfo["scope"])){
            throw new ShopioClientException("Scope information doesn't provided");
        }

        if($tokenInfo["scope"] !== $scope){
            throw new ShopioClientException("Invalid scope information provided");
        }

        if(empty($tokenInfo['access_token'])){
            throw new ShopioClientException("access_token information not found");
        }

        $this->setTokenInfo($tokenInfo);

        return $tokenInfo['access_token'];
    }

    /**
     * Gives subdomain with http protocol
     * @return string
     */
    public function getShopDomain(){
        return $this->getApiProtocol().$this->getShopSubdomain();
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getShopSubdomain()
    {
        return $this->shopSubdomain;
    }

    /**
     * @param string $shopSubdomain
     */
    public function setShopSubdomain($shopSubdomain)
    {
        $this->shopSubdomain = $shopSubdomain;
    }

    /**
     * @return string
     */
    public function getApiProtocol()
    {
        return $this->apiProtocol;
    }

    /**
     * @return array
     */
    public static function getValidApiProtocols(){
        return [self::PROTOCOL_HTTP, self::PROTOCOL_HTTPS];
    }

    /**
     * @param string $apiProtocol
     */
    public function setApiProtocol($apiProtocol)
    {
        if(!in_array($apiProtocol, static::getValidApiProtocols(), true)){
            throw new \InvalidArgumentException('Invalid api protocol');
        }

        $this->apiProtocol = $apiProtocol;
    }

    /**
     * @return array
     */
    public function getTokenInfo()
    {
        return $this->tokenInfo;
    }

    /**
     * @param array $tokenInfo
     */
    public function setTokenInfo($tokenInfo)
    {
        $this->tokenInfo = $tokenInfo;
    }

    /**
     * Gives human readable error message
     * @param RequestException $requestException
     * @return string
     */
    private function getHumanReadableErrorMessage(RequestException $requestException){
        if(!$requestException->hasResponse()){
            throw $requestException;
        }

        $errorMessage = '';
        $response = json_decode($requestException->getResponse()->getBody()->getContents(), true);
        if(isset($response['error_description'])){
            $errorMessage .= $response['error_description'];
        }
        if(isset($response['error'])){
            $errorMessage .= ' ['.$response['error'].']';
        }

        return $errorMessage;
    }
}