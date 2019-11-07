<?php

namespace Xsarus\XsarusERP\Console\Command;

use Psr\Log\LoggerInterface;
use Xsarus\XsarusERP\Api\CategoryImportInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Xsarus\XsarusERP\Logger\Loggers;

/**
 * Class CategoryImportCommand
 */
class CategoryImportCommand extends Command
{
    /**
     * @var CategoryImportInterface $CategoryImport
     */
    protected $categoryImport;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    public function __construct(CategoryImportInterface $categoryImport, LoggerInterface $logger)
    {
        $this->categoryImport = $categoryImport;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('import:category');
        $this->setDescription('Import of the categories from the example database');

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
            $this->categoryImport->execute();
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
