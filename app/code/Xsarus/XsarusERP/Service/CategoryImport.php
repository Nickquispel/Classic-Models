<?php

namespace Xsarus\XsarusERP\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\DeploymentConfig;
use Xsarus\XsarusERP\Api\CategoryImportInterface;

class CategoryImport implements CategoryImportInterface
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
            $response = $this->api->get('/api/productlines');
            $content = $response->getBody()->getContents();

            $this->data = json_decode($content, true);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest() . PHP_EOL . PHP_EOL;
            if ($e->hasResponse()) echo $e->getResponse();
        }
    }

    public function checkForExistingCategory($data)
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

            $array = [];

            $json = json_decode($response->getbody(), true);
            foreach ($json['items'] as $json) {
                $array[] =  $json['name'];
            }

            $this->data['hydra:member'][] = [
                'productline' => 'TEST XSARRUS'
            ];

            foreach ($this->data['hydra:member'] as $productline) {
                if (in_array($productline['productline'], $array)) {
                    echo($productline['productline'] . ' gevonden' . PHP_EOL);
                    unset($productline['productline']);
                } else {
                    echo($productline['productline'] . ' niet gevonden' . PHP_EOL);
                }
            }
            var_dump($this->data['hydra:member']);
            die;
        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) echo $e->getResponse();
            $this->logger->critical('Error message', ['exception' => $e]);
        };
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

            foreach ($this->data['hydra:member'] as $productline) {

                $category = array(
                    'name' => $productline['productline'],
                    'is_active' => true,
                    'include_in_menu' => true,
                    'level' => 2,
                    'custom_attributes' => [
                        [
                            'attribute_code' => 'description',
                            'value' => $productline['textdescription']
                        ]
                    ],
                );

                $response = $this->getClient()->request('POST', '/rest/V1/categories', [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->getToken()
                    ],
                    'json' => [
                        'category' => $category
                    ]
                ]);
                echo ("Artikel " . $productline['productline'] . " succesvol geimporteerd" . PHP_EOL);
                // echo($response->getBody());
                $this->logger->info("Artikel " . $productline['productline'] . " succesvol geimporteerd" . PHP_EOL);
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
        $dataToExport = $this->checkforExistingCategory($data);
        $this->exportData($dataToExport);
    }
}
