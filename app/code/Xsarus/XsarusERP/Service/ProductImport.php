<?php

namespace Xsarus\XsarusERP\Service;

use GuzzleHttp\Client;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface;
use Xsarus\XsarusERP\Api\ProductImportInterface;

class ProductImport implements ProductImportInterface
{
    protected $_token;

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
    protected $logger;
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
            $response = $this->api->get("/api/products");
            $content = $response->getBody()->getContents();

            $data = json_decode($content, true);
            
            return $data;
        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest() . PHP_EOL . PHP_EOL;
            if ($e->hasResponse()) {
                echo $e->getResponse();
            }
        }
    }

    public function addCategoryId($data)
    {
        try {
            $response = $this->import->request('POST', '/rest/V1/integration/admin/token', [
                'json' => [
                    'username' => $this->deploymentConfig->get('admin/username'),
                    'password' => $this->deploymentConfig->get('admin/password')
                ]
            ]);

            $this->_token = str_replace('"', '', $response->getBody()->getContents());

            $response = $this->getClient()->request('GET', '/rest/V1/categories/list?searchCriteria[pageSize]=20', [
                'headers' => [
                    'Authorization' => "Bearer " . $this->getToken()
                ]
            ]);

            $result = json_decode($response->getbody(), true);
            $categories = [];
            foreach ($result as $allCategories) {
                if (is_array($allCategories)) {
                    foreach ($allCategories as $key => $value) {
                        if (!empty($value['parent_id']) && (int) $value['parent_id'] === 2) {
                            $categories[$value['name']] = $value['id'];
                        }
                    }
                }
            }

            $products = [];

            foreach ($data['hydra:member'] as $product) {
                $explode = explode('/api/productlines/', $product['productline']);
                $categoryName = str_replace('%2520', ' ', $explode[1]);

                if (!empty($categories[$categoryName])) {
                    $product['category_id'] = (array) $categories[$categoryName];
                    $products[] = $product;
                }
            }
            return $products;
        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) {
                echo $e->getResponse();
            }
            $this->logger->critical('Error message', ['exception' => $e]);
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

            foreach ($dataToExport as $productcode) {
                $product = [
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
                        ],
                        [
                            'attribute_code' => 'category_ids',
                            'value' => $productcode['category_id']
                        ]
                    ],

                ];

                $response = $this->getClient()->request('POST', '/rest/V1/products', [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->getToken()
                    ],
                    'json' => [
                        'product' => $product
                    ]
                ]);
                echo("Artikel " . $productcode['productcode'] . " succesvol geimporteerd" . PHP_EOL);
                // echo($response->getBody());
                $this->logger->info("Artikel " . $productcode['productcode'] . " succesvol geimporteerd" . PHP_EOL);
            }
        } catch (Exception $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) {
                echo $e->getResponse();
            }
            $this->logger->critical('Error message', ['exception' => $e]);
        }
    }

    public function execute()
    {
        $data = $this->getData();
        $category = $this->addCategoryId($data);
        $this->exportData($category);
    }
}
