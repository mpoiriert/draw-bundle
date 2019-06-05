<?php namespace Draw\SwaggerBundle\Tests;

use Draw\DrawBundle\Security\Voter\OwnVoter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DrawDrawBundleTest extends KernelTestCase
{
    public function setUp() :void
    {
        if(is_null(self::$container)) {
            self::bootKernel();
        }
    }

    public function provideTestGetService()
    {
        return [
            [OwnVoter::class]
        ];
    }

    /**
     * @dataProvider provideTestGetService
     *
     * @param $serviceId
     * @param null $class
     */
    public function testGetService($serviceId, $class = null)
    {
        $class = $class ?: $serviceId;
        $service = self::$container->get($serviceId);

        $this->assertInstanceOf($class, $service);
    }
}