<?php

namespace Xsarus\XsarusERP\MessageQueue;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use Magento\Framework\App\DeploymentConfig;


/**
 * Class ProductStockHandler
 * @package Xsarus\XsarusERP\MessageQueue
 */
class ProductStockHandler
{

    protected $_token;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var Client $import
     */
    protected $import;
    /**
     * @var DeploymentConfig $deploymentConfig
     */
    protected $deploymentConfig;



    public function __construct(
        LoggerInterface $logger,
        Client $import,
        DeploymentConfig $deploymentConfig
    ) {
        $this->logger = $logger;
        $this->import = $import;
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


    /**
     * @param $message
     * @throws \Exception
     */
    public function process($message)
    {
        try {
            $response = $this->import->request('POST', '/rest/V1/integration/admin/token', [
                'json' => [
                    'username' => $this->deploymentConfig->get('admin/username'),
                    'password' => $this->deploymentConfig->get('admin/password')
                ]
            ]);

            $this->_token = str_replace('"', '', $response->getBody()->getContents());

            $stock = json_decode($message, true);

            $product = array(
                'sku' => $stock['sku'],
                'extension_attributes' => [
                    'stock_item' => [
                        'qty' => $stock['qty'],
                        'is_in_stock' => true
                    ]
                ],

            );

            $response = $this->getClient()->request('POST', '/rest/V1/products', [
                'headers' => [
                    'Authorization' => "Bearer " . $this->getToken()
                ],
                'json' => [
                    'product' => $product
                ]
            ]);
            echo ("Voorraad van artikel " . $stock['sku'] . " succesvol geimporteerd" . PHP_EOL);
            // echo($response->getBody());
            $this->logger->info("Voorraad van artikel " . $stock['sku'] . " succesvol geimporteerd" . PHP_EOL);
        } catch (Exception $e) {
            echo $e->getMessage();
            if ($e->hasResponse()) echo $e->getResponse();
            $this->logger->critical('Error message', ['exception' => $e]);
        }
    }
}
