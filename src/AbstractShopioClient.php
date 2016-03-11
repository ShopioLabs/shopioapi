<?php namespace ShopioLabs\ShopioApi;

use GuzzleHttp\Exception\RequestException;

/**
 * Class AbstractShopioClient
 * @package ShopioLabs\ShopioApi
 */
abstract class AbstractShopioClient {

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
    private $apiProtocol;

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
     * Gives human readable error message
     * @param RequestException $requestException
     * @return string
     */
    protected function getPrettyErrorMessage(RequestException $requestException){
        if(!$requestException->hasResponse()){
            throw $requestException;
        }

        $errorMessage = '';
        $response = json_decode($requestException->getResponse()->getBody()->getContents(), true);

        if(isset($response['message'])){
            $errorMessage .= $response['message'];
            $errorMessage .= $this->getDetailedErrorMessage($response);
        }
        if(isset($response['error_description'])){
            $errorMessage .= $response['error_description'];
        }
        if(isset($response['error'])){
            $errorMessage .= ' ['.$response['error'].']';
        }
        if(isset($response['code'])){
            $errorMessage .= ' code: '.$response['code'];
        }

        return $errorMessage;
    }


    /**
     * @param $response
     * @return null|string
     */
    private function getDetailedErrorMessage($response)
    {
        if (!isset($response["message"])) {
            return '';
        }
        $messages = [];
        if (isset($response["errors"]) && !empty($response["errors"])) {
            $errors = $response["errors"];
            foreach ($errors as $error) {
                if (isset($error["field"]) && isset($error["message"])) {
                    $messages[] = $error["field"] . ":" . $error["message"];
                }
            }
        }
        if(empty($messages)){
            return '';
        }

        return ' Details: '.implode(' ', $messages);
    }
}