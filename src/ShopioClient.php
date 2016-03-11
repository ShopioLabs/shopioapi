<?php namespace ShopioLabs\ShopioApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class ShopioClient
 * @package ShopioLabs\ShopioApi
 */
class ShopioClient extends AbstractShopioClient
{
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
     * @param array $data
     * @param array $options
     * @return array
     * @throws InvalidGrantException
     * @throws ShopioClientException
     */
    public function call($entity, $method, $data = [], $options = []){

        $uri = $this->getApiProtocol() . $this->getShopSubdomain() . "/api/v2/$entity";

        if(!empty($data)){
            $options['body'] = json_encode($data);
        }

        try{
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
        }catch (RequestException $requestException){
            throw new ShopioClientException($this->getPrettyErrorMessage($requestException));
        }

        $response = json_decode($response->getBody(), true);
        if(is_array($response) && isset($response['error']) && $response['error'] == 'invalid_grant'){
            throw new InvalidGrantException($response['error_description']);
        }
        return (is_array($response) and (count($response) > 0)) ? array_shift($response) : $response;
    }
}