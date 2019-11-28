<?php

namespace Xsarus\XsarusERP\Service;

use GuzzleHttp\Client;
use Magento\Framework\App\DeploymentConfig;
use Psr\Log\LoggerInterface;
use Xsarus\XsarusERP\Api\OrderExportInterface;

class OrderExport implements OrderExportInterface
{
    protected $_token;

    /**
     * @var Client $api
     */
    protected $api;

    /**
     * @var Client $export
     */
    protected $export;

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
        Client $export,
        LoggerInterface $logger,
        DeploymentConfig $deploymentConfig
    ) {
        $this->api = $api;
        $this->export = $export;
        $this->logger = $logger;
        $this->deploymentConfig = $deploymentConfig;
    }

    public function getToken()
    {
        return $this->_token;
    }

    public function getClient()
    {
        return $this->export;
    }

    public function getOrderData()
    {
        try {
            $response = $this->export->request('POST', '/rest/V1/integration/admin/token', [
                'json' => [
                    'username' => $this->deploymentConfig->get('admin/username'),
                    'password' => $this->deploymentConfig->get('admin/password')
                ]
            ]);

            $this->_token = str_replace('"', '', $response->getBody()->getContents());

            $response = $this->getClient()->request('GET', '/rest/V1/orders?searchCriteria[pageSize]=1', [
                'headers' => [
                    'Authorization' => "Bearer " . $this->getToken()
                ]
            ]);
            $orders = json_decode($response->getbody(), true);

            return $orders;
        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest() . PHP_EOL . PHP_EOL;
            if ($e->hasResponse()) {
                echo $e->getResponse();
            }
        }
    }

    public function exportOrderData($orders)
    {
        try {
            $request = $this->api->request('POST', '/api/orderimport', [
                     'json' => $orders
                ]);
            $response =  $request->getbody();
            return $response;
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) {
                echo $e->getResponse();
            }
            $this->logger->critical('Error message', ['exception' => $e]);
        }
    }

    public function execute()
    {
        $data = $this->getOrderData();
        $this->exportOrderData($data);
    }
}
