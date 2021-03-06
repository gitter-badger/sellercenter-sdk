<?php

namespace SellerCenter\Test\SDK\Common\Api\Serializer;

use GuzzleHttp\Client;
use GuzzleHttp\Command\CommandTransaction;
use SellerCenter\SDK\Common\Api\Service;
use SellerCenter\SDK\Common\ClientFactory;
use SellerCenter\SDK\Common\Credentials\NullCredentials;
use SellerCenter\SDK\Common\SdkClient;
use SellerCenter\Test\SDK\SdkTestCase;
use SellerCenter\Test\SDK\UsesServiceTrait;

/**
 * Class ComplianceTest
 *
 * @package SellerCenter\SDK\Common\Api\Serializer
 * @author  Daniel Costa
 */
class ComplianceTest extends SdkTestCase
{
    use UsesServiceTrait;

    public function testCaseProvider()
    {
        $cases = [];

        $files = glob(__DIR__ . '/../test_cases/protocols/input/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            foreach ($data as $suite) {
                $suite['metadata']['type'] = $suite['metadata']['protocol'];
                foreach ($suite['cases'] as $case) {
                    $description = new Service(function () use ($suite, $case) {
                        return [
                            'metadata' => $suite['metadata'],
                            'operations' => [
                                $case['given']['name'] => $case['given']
                            ]
                        ];
                    }, 'service', 'store', 'environment');
                    $cases[] = [
                        $file . ': ' . $suite['description'],
                        $description,
                        $case['given']['name'],
                        $case['params'],
                        $case['serialized']
                    ];
                }
            }
        }

        return $cases;
    }

    /**
     * @dataProvider testCaseProvider
     */
    public function testPassesComplianceTest(
        $about,
        Service $service,
        $name,
        array $args,
        $serialized
    ) {
        $this->markTestSkipped();

        $ep = 'https://api-staging.sellercenter.net';
        $client = new SdkClient([
            'api' => $service,
            'credentials' => new NullCredentials(),
            'client' => new Client(),
            'signature' => $this->getMock('SellerCenter\SDK\Commom\Signature\SignatureInterface'),
            'store' => 'mobly',
            'environment' => 'staging',
            'endpoint' => $ep,
            'error_parser' => Service::createErrorParser($service->getProtocol()),
            'serializer'   => Service::createSerializer($service, $ep),
            'version'      => 'latest'
        ]);

        $clientFactory = new ClientFactory();
        /* @var \SellerCenter\SDK\Common\ClientFactory $reflectionClass */
        /* @var \ReflectionClass $reflectionClass */
        $reflectionClass = new \ReflectionClass($clientFactory);
        $reflectionMethod = $reflectionClass->getMethod('applyParser');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invoke($clientFactory, $client, 'http://foo.com');
        $command = $client->getCommand($name, $args);
        $trans = new CommandTransaction($client, $command);
        /** @var callable $serializer */
        $serializer = $this->readAttribute($client, 'serializer');
        $request = $serializer($trans);
        $this->assertEquals($serialized['uri'], $request->getResource());

        $body = (string) $request->getBody();
        switch ($service->getMetadata('type')) {
            case 'rest-xml':
                // Normalize XML data.
                if ($serialized['body'] && strpos($serialized['body'], '</')) {
                    $serialized['body'] = str_replace(
                        ' />',
                        '/>',
                        '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                        . $serialized['body']
                    );
                    $body = trim($body);
                }
                break;
        }

        $this->assertEquals($serialized['body'], $body);

        if (isset($serialized['headers'])) {
            foreach ($serialized['headers'] as $key => $value) {
                $this->assertSame($value, $request->getHeader($key));
            }
        }
    }
}
