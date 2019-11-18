<?php

namespace Xsarus\XsarusERP\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Magento\Framework\MessageQueue\Publisher;
use Xsarus\XsarusERP\Api\StockImportInterface;


class StockImport implements StockImportInterface
{
    /**
     * @var Client $api
     */
    protected $api;

    /**
     * @var Publisher $publisher
     */
    protected $publisher;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;
  

    public function __construct(
        Client $api,
        LoggerInterface $logger,
        Publisher $publisher

    ) {

        $this->api = $api;
        $this->logger = $logger;
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
