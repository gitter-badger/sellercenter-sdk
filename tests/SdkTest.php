<?php
namespace SellerCenter\SDK;

use GuzzleHttp\Event\EmitterInterface;
//use JmesPath\Env as JmesPath;

class SdkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check if the given emitter has the provided event listener
     *
     * @param EmitterInterface $emitter Emitter to search
     * @param string|object    $value   Can be a class name or listener object
     * @param null             $event   Specific event to search (optional)
     *
     * @return bool
     */
//    public static function hasListener(
//        EmitterInterface $emitter,
//        $value,
//        $event = null
//    ) {
//        $expression = $event
//            ? '[*][0]'
//            : '*[*][0]';
//
//        $listeners = $event
//            ? $emitter->listeners($event)
//            : $emitter->listeners();
//
//        $result = JmesPath::search($expression, $listeners) ?: [];
//
//        if (!is_object($value)) {
//            $result = array_map(function($o) {
//                return get_class($o);
//            }, $result);
//        }
//
//        return in_array($value, $result, true);
//    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testEnsuresMissingMethodThrowsException()
    {
        (new Sdk())->foo();
    }

    public function testHasMagicMethods()
    {
        $sdk = $this->getMockBuilder('SellerCenter\SDK\Sdk')
            ->setMethods(['getClient'])
            ->getMock();
        $sdk->expects($this->once())
            ->method('getClient')
            ->with('Foo', ['bar' => 'baz']);
        $sdk->getFoo(['bar' => 'baz']);
    }

    public function testCreatesClients()
    {
        $this->assertInstanceOf(
            'SellerCenter\SDK\Common\SdkClientInterface',
            (new Sdk())->getProduct([
                'store'  => 'mobly',
                'environment' => 'staging',
                'credentials' => [
                    'id' => 'admin@sellercenter.com',
                    'key' => '1234567890123456789012345678901234567890',
                ]
            ])
        );
    }

    public function testMergesInstanceArgsWithStoredArgs()
    {
        $sdk = new Sdk([
            'a' => 'a1',
            'b' => 'b1',
            'c' => 'c1',
            'foo' => ['b' => 'b2']
        ]);

        $customFactories = (new \ReflectionObject($sdk))
            ->getProperty('factories');
        $customFactories->setAccessible(true);

        $current = $customFactories->getValue($sdk);
        $customFactories->setValue($sdk, [
            'foo' => __NAMESPACE__ . '\\FooFactory'
        ]);
        $args = $sdk->getFoo(['c' => 'c2', 'd' => 'd1']);

        $this->assertEquals('a1', $args['a']);
        $this->assertEquals('b2', $args['b']);
        $this->assertEquals('c2', $args['c']);
        $this->assertEquals('d1', $args['d']);
        $customFactories->setValue($sdk, $current);
    }
}

class FooFactory
{
    function create($args) {
        return $args;
    }
}
