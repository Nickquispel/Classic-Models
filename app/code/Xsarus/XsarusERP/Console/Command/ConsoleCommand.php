<?php
    namespace Xsarus\XsarusERP\Console\Command;

use Psr\Log\LoggerInterface;
use Xsarus\XsarusERP\Api\ProductImportInterface;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Xsarus\XsarusERP\Logger\Loggers;

    /**
     * Class ConsoleCommand
     */
    class ConsoleCommand extends Command
    {
        /**
         * @var ProductImportInterface $productImport
         */
        protected $productImport;

        /**
         * @var LoggerInterface $logger
         */
        protected $logger;

        public function __construct(ProductImportInterface $productImport, LoggerInterface $logger)
        {
            $this->productImport = $productImport;
            $this->logger = $logger;
            parent::__construct();
        }

        /**
         * @inheritDoc
         */
        protected function configure()
        {
            $this->setName('products:import');
            $this->setDescription('Import of the products from the example database');

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
                $this->productImport->execute();
            } catch (GuzzleHttp\Exception\ClientException $e) {
                   $this->logger->error($e->getMessage());
            }     
        }
}
