<?php

namespace Xsarus\XsarusERP\Console\Command;

use Psr\Log\LoggerInterface;
use Xsarus\XsarusERP\Api\ProductImageImportInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Xsarus\XsarusERP\Logger\Loggers;

/**
 * Class ProductImportCommand
 */
class ProductImageImportCommand extends Command
{
    /**
     * @var ProductImageImportInterface $ImageImport
     */
    protected $ImageImport;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    public function __construct(ProductImageImportInterface $ImageImport, LoggerInterface $logger)
    {
        $this->ImageImport = $ImageImport;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('import:images');
        $this->setDescription('Import of the product images from pub/media/import');

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
            $this->ImageImport->execute();
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
