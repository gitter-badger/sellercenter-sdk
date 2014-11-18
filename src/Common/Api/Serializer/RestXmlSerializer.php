<?php

namespace SellerCenter\SDK\Common\Api\Serializer;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Stream\Stream;
use SellerCenter\SDK\Common\Api\Service;

/**
 * Class RestXmlSerializer
 *
 * @package SellerCenter\SDK\Common\Api\Serializer
 * @author Daniel Costa
 */
class RestXmlSerializer extends RestSerializer
{
    /** @var XmlBody */
    private $xmlBody;

    /**
     * @param Service $api      Service API description
     * @param string  $endpoint Endpoint to connect to
     * @param XmlBody $xmlBody  Optional XML formatter to use
     */
    public function __construct(
        Service $api,
        $endpoint,
        XmlBody $xmlBody = null
    ) {
        parent::__construct($api, $endpoint);
        $this->xmlBody = $xmlBody ?: new XmlBody($api);
    }

    protected function payload(
        RequestInterface $request,
        $name,
        array $args
    ) {
        $request->setHeader('Content-Type', 'application/xml');
        $request->setBody(Stream::factory($this->xmlBody->build($name, $args)));
    }
}
