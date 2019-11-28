<?php

namespace Xsarus\XsarusERP\Console\Command;

use Psr\Log\LoggerInterface;
use Xsarus\XsarusERP\Api\OrderExportInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Xsarus\XsarusERP\Logger\Loggers;

/**
 * Class OrderExportCommand
 */
class OrderExportCommand extends Command
{
    /**
     * @var OrderExportInterface $orderExport
     */
    protected $orderExport;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    public function __construct(OrderExportInterface $orderExport, LoggerInterface $logger)
    {
        $this->orderExport = $orderExport;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('export:orders');
        $this->setDescription('Export of the orders to the example database');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->orderExport->execute();
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
