<?php

namespace Xsarus\XsarusERP\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Xsarus\XsarusERP\Api\ProductImportInterface;
use Magento\Framework\App\DeploymentConfig;

class ProductImport implements ProductImportInterface
{
    protected $_token;
    
    protected $data;

    /**
     * @var Client $client1
     */
    protected $client1;

    /**
     * @var Client $client2
     */
    protected $client2;

    /**
     * @var LoggerInterface $logger
     */


     /**
      * @var DeploymentConfig $deploymentConfig
      */
    protected $deploymentConfig;

    public function __construct(
        Client $client1,
        Client $client2,
        LoggerInterface $logger,
        DeploymentConfig $deploymentConfig
        )
    {
        
        $this->client1 = $client1;
        $this->client2 = $client2;
        $this->logger = $logger;
        $this->deploymentConfig = $deploymentConfig;
    
    }
 
    public function getToken()
    {
        return $this->_token;
    }
 
    public function getClient()
    {
        return $this->client2;
    }

    public function execute()
    {

        try {
            $response = $this->client1->get('/api/products');
            $content = $response->getBody()->getContents();
                        
            $this->data = json_decode($content,true);

        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest() . PHP_EOL . PHP_EOL;
            if ($e->hasResponse()) echo $e->getResponse();
        }

        try {
            $response = $this->client2->request('POST','/rest/V1/integration/admin/token', [
        'json' => [
            'username' => $this->deploymentConfig->get('admin/username'),
            'password' => $this->deploymentConfig->get('admin/password')
            ]
        ]);

            $this->_token = str_replace('"', '', $response->getBody()->getContents());

            foreach($this->data['hydra:member'] as $productcode)
            {   

            $product = array(
                'sku' => $productcode['productcode'],
                'name' => $productcode['productname'],
                'price' => $productcode['msrp'],
                'status' => '1',
                'visibility' => '4',
                'attribute_set_id' => '4',
                'type_id' => 'simple',
                'custom_attributes'=> [
                    [
                    'attribute_code' => 'product_scale',
                    'value' => $productcode['productscale']
                    ],
                    [
                    'attribute_code' => 'product_vendor',
                    'value' => $productcode['productvendor']
                    ]   
                ]);

                $response = $this->getClient()->request('POST', '/rest/V1/products', [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->getToken()
                    ],
                    'json' => [
                        'product' => $product
                    ]
                ]);    
                echo($response->getBody());
                $this->logger->info("Artikel". $productcode['productcode']."succesvol geimporteerd". PHP_EOL);
                
            }

        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) echo $e->getResponse();
        }



    }
}