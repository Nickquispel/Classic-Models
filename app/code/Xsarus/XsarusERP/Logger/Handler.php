<?php

namespace Xsarus\XsarusERP\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * XsarusERP
     * @var string
     */
    protected $XsarusERP = '/var/log/XsarusERP.log';
}