<?php namespace ShopioLabs\ShopioApi;

use GuzzleHttp\Client;

/**
 * Class ShopioClient
 * @package ShopioLabs\ShopioApi
 */
class ShopioClient
{
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
    private $accessToken;

    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * @var string
     */
    private $shopSubdomain;

    /**
     * @var string
     */
    private $apiProtocol;

    /**
     * @param $shopSubdomain
     * @param $accessToken
     * @param string $apiProtocol
     */
    function __construct($shopSubdomain, $accessToken, $apiProtocol = self::PROTOCOL_HTTPS)
    {
        $this->setShopSubdomain($shopSubdomain);
        $this->setAccessToken($accessToken);
        $this->setApiProtocol($apiProtocol);
        $this->setGuzzleClient(
            new Client(
                [
                    "headers" => [
                        "Authorization" => "Bearer " . $this->getAccessToken()
                    ]
                ]
            )
        );
    }

    /**
     * @return Client
     */
    public function getGuzzleClient()
    {
        return $this->guzzleClient;
    }

    /**
     * @param Client $guzzleClient
     */
    public function setGuzzleClient($guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
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
    public static function getValidApiProtocols(){
        return [self::PROTOCOL_HTTP, self::PROTOCOL_HTTPS];
    }

    /**
     * Makes api calls
     *
     * create brand example:
     * $shopioClient->call("brands", "POST", ['body' => '{"title":"Test","meta_title":"U.S. Alteration","meta_description":null,"meta_keywords":null,"image":"about-us-logo.png","image_revision":1,"status":1,"default_sort_order":"label_asc","image_path":"//static.shopio.sg/storefront/aa/0000/b/about-us-logo.png?1"}']);
     *
     * List brands example:
     * $brands = $shopioClient->request("brands", "GET");
     *
     * @param string $entity
     * @param string $method
     * @param array $options
     * @throws InvalidGrantException
     * @throws \InvalidArgumentException
     * @return array
     */
    public function call($entity, $method, $options = []){

        $uri = $this->getApiProtocol() . $this->getShopSubdomain() . "/api/v2/$entity";

        switch (strtoupper($method)) {
            case "GET":
                $response = $this->getGuzzleClient()->get($uri, $options);
                break;
            case "POST":
                $response = $this->getGuzzleClient()->post($uri, $options);
                break;
            case "PUT":
                $response = $this->getGuzzleClient()->put($uri, $options);
                break;
            case "DELETE":
                $response = $this->getGuzzleClient()->delete($uri, $options);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported HTTP method");
        }

        $response = json_decode($response->getBody(), true);
        if(is_array($response) && isset($response['error']) && $response['error'] == 'invalid_grant'){
            throw new InvalidGrantException($response['error_description']);
        }
        return (is_array($response) and (count($response) > 0)) ? array_shift($response) : $response;
    }
}