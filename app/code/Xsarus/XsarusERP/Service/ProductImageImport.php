<?php

namespace Xsarus\XsarusERP\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Xsarus\XsarusERP\Api\ProductImageImportInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Exception;

class ProductImageImport implements ProductImageImportInterface
{
    protected $_token;

    /**
     * @var Client $import
     */
    protected $import;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;
    /**
     * @var DeploymentConfig $deploymentConfig
     */
    protected $deploymentConfig;

    public function __construct(
        Client $import,
        LoggerInterface $logger,
        DeploymentConfig $deploymentConfig
    ) {

        $this->import = $import;
        $this->logger = $logger;
        $this->deploymentConfig = $deploymentConfig;
    }

    public function getToken()
    {
        return $this->_token;
    }

    public function getClient()
    {
        return $this->import;
    }

    public function getData()
    {
        try {
            $response = $this->import->request('POST', '/rest/V1/integration/admin/token', [
                'json' => [
                    'username' => $this->deploymentConfig->get('admin/username'),
                    'password' => $this->deploymentConfig->get('admin/password')
                ]
            ]);

            $this->_token = str_replace('"', '', $response->getBody()->getContents());

            $response = $this->getClient()->request('GET', '/rest/V1/products?searchCriteria[page_size]=110', [
                'headers' => [
                    'Authorization' => "Bearer " . $this->getToken()
                ]
            ]);

            $json = json_decode($response->getbody(), true);

            $array = [];

            foreach ($json['items'] as $json) {
                $array[] =  $json['sku'];
            }

            return $array;
        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) echo $e->getResponse();
            $this->logger->critical('Error message', ['exception' => $e]);
        };
    }

    public function checkDatawithFiles($data)
    {
        $array = [];

        $_FILES = scandir(__DIR__ . '/../../../../../pub/media/import/');
        foreach ($_FILES as $picture) {
            $x = explode('-', $picture);
            if (in_array($x[0], $data)) {
                $array[] = $x;
            }
        }

        return $array;
    }

    public function exportImage1($prod)
    {

        $products = [];

        $response = $this->import->request('POST', '/rest/V1/integration/admin/token', [
            'json' => [
                'username' => $this->deploymentConfig->get('admin/username'),
                'password' => $this->deploymentConfig->get('admin/password')
            ]
        ]);

        $this->_token = str_replace('"', '', $response->getBody()->getContents());

        foreach ($prod as $image) {

            $sku = $image[0];

            $mediaGalleryEntry = [
                [
                    'media_type' => 'image',
                    'label' => $image[0] . '-1.jpg',
                    'disabled' => false,
                    'types' => [
                        'small_image',
                        'thumbnail',
                    ],
                    'content' => [
                        "base64_encoded_data" => base64_encode(file_get_contents(__DIR__ . '/../../../../../pub/media/import/' . $image[0] . '-1.jpg', true)),
                        // "base64_encoded_data" => $image[0] . '-' . $image[1],
                        'type' => 'image/jpeg',
                        'name' => $image[0] . '-1.jpg'
                    ]
                ],
                [
                    'media_type' => 'image',
                    'label' => $image[0] . '-2.jpg',
                    'disabled' => false,
                    'types' => [
                        'image'
                    ],
                    'content' => [
                        "base64_encoded_data" => base64_encode(file_get_contents(__DIR__ . '/../../../../../pub/media/import/' . $image[0] . '-2.jpg', true)),
                        // "base64_encoded_data" => $image[0] . '-' . $image[1],
                        'type' => 'image/jpeg',
                        'name' => $image[0] . '-1.jpg'
                    ]
                ]
            ];

            $products[] = [
                'sku' => $sku,
                'media_gallery_entries' => $mediaGalleryEntry
            ];

            $this->logger->info(" Afbeelding van artikel " . $image[0] . '-' . $image[1] . " succesvol geimporteerd" . PHP_EOL);
        }


        try {
            foreach ($products as $product) {

                $request = [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->getToken()
                    ],
                    'json' => [
                        'product' => $product
                    ]
                ];

                $response = $this->getClient()->request('POST', '/rest/V1/products', $request);
            }
        } catch (Exception $e) {
            echo $e->getRequest();
            if ($e->hasResponse()) echo $e->getResponse();
            $this->logger->critical('Error message', ['exception' => $e]);
        }
    }

    // public function exportImage2($prod)
    // {

    //     foreach ($prod as $image) {
    //         try {
    //             $response = $this->import->request('POST', '/rest/V1/integration/admin/token', [
    //                 'json' => [
    //                     'username' => $this->deploymentConfig->get('admin/username'),
    //                     'password' => $this->deploymentConfig->get('admin/password')
    //                 ]
    //             ]);

    //             $this->_token = str_replace('"', '', $response->getBody()->getContents());

    //             $product = [
    //                 'sku' => $image[0],
    //                 'media_gallery_entries' => [[
    //                     'media_type' => 'image',
    //                     'label' => $image[0].'-'.$image[1],
    //                     'position' => 1,
    //                     'disabled' => false,
    //                     'types' => [
    //                         'small_image',
    //                         'thumbnail',
    //                     ],
    //                     'content' => [
    //                         "base64_encoded_data" => base64_encode(file_get_contents(__DIR__ . '/../../../../../pub/media/import/' . $image[0] . '-2.jpg', true)),
    //                         "type" => "image/jpeg",
    //                         'name' => $image[0] .'-2.jpg'
    //                     ]

    //                 ]]

    //             ];

    //             $request = [
    //                 'headers' => [
    //                     'Authorization' => "Bearer " . $this->getToken()
    //                 ],
    //                 'json' => [
    //                     'product' => $product
    //                 ]
    //             ];

    //             $response = $this->getClient()->request('POST', '/rest/V1/products', $request);
    //             $this->logger->info(" Afbeelding van artikel " . $image[0].'-'.$image[1] . " succesvol geimporteerd" . PHP_EOL);
    //             echo ("succesfully imported image from article " . $image[0].'-'.$image[1] . PHP_EOL);
    //         } catch (Exception $e) {
    //             echo $e->getRequest();
    //             if ($e->hasResponse()) echo $e->getResponse();
    //             $this->logger->critical('Error message', ['exception' => $e]);
    //         }
    //     }
    // }

    public function execute()
    {
        $data = $this->getData();
        $array = $this->checkDatawithFiles($data);
        $this->exportImage1($array);
        // $this->exportImage2($array);
    }
}
