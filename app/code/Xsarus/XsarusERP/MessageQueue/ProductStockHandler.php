<?php

namespace Xsarus\XsarusERP\MessageQueue;


/**
 * Class ProductStockHandler
 * @package Xsarus\XsarusERP\MessageQueue
 */
class ProductStockHandler
{

    public function __construct()
    { }

    /**
     * @param $message
     * @throws \Exception
     */
    public function process($message)
    { 
        foreach ($message as $message)
        $product = array(
            'sku' => $message['sku'],
            'stock_item' => [
                [
                    'attribute_code' => 'qty',
                    'value' => $message['qty']
                ],
            ]
            );
    }
}
