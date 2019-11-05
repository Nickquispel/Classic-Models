<?php

namespace Xsarus\XsarusERP\Service;

use GuzzleHttp\Client;

class ProductImport 
{
    protected $_token;
    protected $_client;
    protected $data;
 
    public function __construct($url, $url2)
    {
        $username = 'admin'; $password = 'Heineken!!1';
        $this->_client = new Client(['base_uri' => $url, 'verify' => false]);   
        
               
        try {
            $response = $this->_client->get('/api/products');
            $content = $response->getBody()->getContents();
                        
            $this->data = json_decode($content,true);
            
            foreach($this->data['hydra:member'] as $productcode)
            {   
            echo $productcode['productcode']. PHP_EOL;
            }
            
            

        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest() . PHP_EOL . PHP_EOL;
            if ($e->hasResponse()) echo $e->getResponse();
        }
        
        $this->_client = new Client(['base_uri' => $url2, 'verify' => false]);   
        
        try {
            $response = $this->_client->request('POST','/rest/V1/integration/admin/token', [
        'json' => [
            'username' => $username,
            'password' => $password,
            ]
        ]);

            $this->_token = str_replace('"', '', $response->getBody()->getContents());

            foreach($this->data['hydra:member'] as $productcode)
            {   

            $testProduct = array(
                'sku' => $productcode['productcode'],
                'name' => $productcode['productname'],
                'price' => $productcode['msrp'],
                'status' => '1',
                'visibility' => '4',
                'attribute_set_id' => '4',
                'type_id' => 'simple',);


                $response = $this->getClient()->request('POST', '/rest/V1/products', [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->getToken()
                    ],
                    'json' => [
                        'product' => $testProduct
                    ]
                ]);    
                echo($response->getBody());
            }

        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) echo $e->getResponse();
        }
       
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