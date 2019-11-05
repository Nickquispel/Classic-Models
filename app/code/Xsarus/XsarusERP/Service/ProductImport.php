<?php

namespace Xsarus\XsarusERP\Service;

use GuzzleHttp\Client;

class ProductImport 
{
    protected $_token;
    protected $_client;
 
    public function __construct($url)
    {
        $this->_client = new Client(['base_uri' => $url]);
 
        try {
            $response = $this->_client->get('/api/products');
            $this->_token = str_replace('"', '', $response->getBody()->getContents());
        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) echo $e->getResponse();
        }
        
        return $this;
        
    }
 
    public function getToken()
    {
        return $this->_token;
    }
 
    public function getClient()
    {
        return $this->_client;
    }

}