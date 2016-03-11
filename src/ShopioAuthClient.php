<?php namespace ShopioLabs\ShopioApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class ShopioAuthClient
 * @package ShopioLabs\ShopioApi
 */
class ShopioAuthClient extends AbstractShopioClient {

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
     * @param $redirectUrl
     * @return string
     * @throws ShopioClientException
     */
    public function getAccessToken($code, $scope, $redirectUrl){
        $apiKey = $this->getApiKey();
        $secret = $this->getSecret();
        $url = $this->getShopDomain()."/oauth/v2/token?client_id=$apiKey&client_secret=$secret&grant_type=authorization_code&&code=$code&redirect_uri=$redirectUrl";

        try {
            $client = new Client();
            $response = $client->get($url);
        } catch (RequestException $requestException) {
            throw new ShopioClientException($this->getPrettyErrorMessage($requestException));
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
}