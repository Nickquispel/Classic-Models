<?php
    namespace Xsarus\XsarusERP\Console\Command;

    use Xsarus\XsarusERP\Service\ProductImport;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    /**
     * Class ConsoleCommand
     */
    class ConsoleCommand extends Command
    {
        
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


          try { $webapi = new ProductImport('http://localhost:8888');
                $response = $webapi->getClient()->request('GET', '/api/products');
                var_dump(json_decode($response->getBody()));
                
            } catch (GuzzleHttp\Exception\ClientException $e) {
                echo $e->getMessage();
            }

            $output->writeln('<info>Success Message.</info>');
            $output->writeln('<error>An error encountered.</error>');
            $output->writeln("Hello World");
            
        }
    }