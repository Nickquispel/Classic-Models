<?php

namespace Xsarus\XsarusERP\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\MessageQueue\Publisher;
use Xsarus\XsarusERP\Api\StockImportInterface;


class StockImport implements StockImportInterface
{
    protected $_token;


    /**
     * @var Client $api
     */
    protected $api;

    /**
     * @var Publisher $publisher
     */
    protected $publisher;

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
        DeploymentConfig $deploymentConfig,
        Publisher $publisher

    ) {

        $this->api = $api;
        $this->import = $import;
        $this->logger = $logger;
        $this->deploymentConfig = $deploymentConfig;
        $this->publisher = $publisher;
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
            if ($e->hasResponse()) echo $e->getResponse();
        }
    }

    public function publish($data)
    {
        foreach ($data['hydra:member'] as $stock) {

            $product = array(
                'sku' => $stock['productcode'],
                'qty' => $stock['quantityinstock'],
            );
            $this->publisher->publish('product.stock', json_encode($product));
        }
    }


    public function execute()
    {
        $data = $this->getData();
        $this->publish($data);
    }
}
