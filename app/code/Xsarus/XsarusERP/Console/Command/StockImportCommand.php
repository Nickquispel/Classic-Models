<?php

namespace Xsarus\XsarusERP\Console\Command;

use Psr\Log\LoggerInterface;
use Xsarus\XsarusERP\Api\StockImportInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Xsarus\XsarusERP\Logger\Loggers;

/**
 * Class StockImportCommand
 */
class StockImportCommand extends Command
{
    /**
     * @var StockImportInterface $productImport
     */
    protected $stockImport;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    public function __construct(StockImportInterface $stockImport, LoggerInterface $logger)
    {
        $this->stockImport = $stockImport;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('import:stock');
        $this->setDescription('Stock Import from the example database');

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
            $this->stockImport->execute();
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
