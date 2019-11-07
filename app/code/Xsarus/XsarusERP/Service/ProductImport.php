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
     * @var Client $api
     */
    protected $api;

    /**
     * @var Client $import
     */
    protected $import;

    /**
     * @var LoggerInterface $logger
     */

    /**
     * @var DeploymentConfig $deploymentConfig
     */
    protected $deploymentConfig;

    public function __construct(
        Client $api,
        Client $import,
        LoggerInterface $logger,
        DeploymentConfig $deploymentConfig
    ) {

        $this->api = $api;
        $this->import = $import;
        $this->logger = $logger;
        $this->deploymentConfig = $deploymentConfig;
    }

    public function getToken()
    {
        return $this->_token;
    }

    public function getClient()
    {
        return $this->import;
    }

    public function getData()
    {
        try {
            $response = $this->api->get('/api/products');
            $content = $response->getBody()->getContents();

            $this->data = json_decode($content, true);
            return $this->data;
        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest() . PHP_EOL . PHP_EOL;
            if ($e->hasResponse()) echo $e->getResponse();
        }
    }


    public function exportData($dataToExport)
    {
        try {
            $response = $this->import->request('POST', '/rest/V1/integration/admin/token', [
                'json' => [
                    'username' => $this->deploymentConfig->get('admin/username'),
                    'password' => $this->deploymentConfig->get('admin/password')
                ]
            ]);

            $this->_token = str_replace('"', '', $response->getBody()->getContents());

            foreach ($this->data['hydra:member'] as $productcode) {

                $product = array(
                    'sku' => $productcode['productcode'],
                    'name' => $productcode['productname'],
                    'price' => $productcode['msrp'],
                    'status' => '1',
                    'visibility' => '4',
                    'attribute_set_id' => '4',
                    'type_id' => 'simple',
                    'custom_attributes' => [
                        [
                            'attribute_code' => 'product_scale',
                            'value' => $productcode['productscale']
                        ],
                        [
                            'attribute_code' => 'product_vendor',
                            'value' => $productcode['productvendor']
                        ]
                    ]
                );

                $response = $this->getClient()->request('POST', '/rest/V1/products', [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->getToken()
                    ],
                    'json' => [
                        'product' => $product
                    ]
                ]);
                echo ("Artikel " . $productcode['productcode'] . " succesvol geimporteerd" . PHP_EOL);
                // echo($response->getBody());
                $this->logger->info("Artikel " . $productcode['productcode'] . " succesvol geimporteerd" . PHP_EOL);
            }
        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) echo $e->getResponse();
            $this->logger->critical('Error message', ['exception' => $e]);
        }
    }

    public function execute()
    {
        $data = $this->getData();
        $this->exportData($data);
    }
}
