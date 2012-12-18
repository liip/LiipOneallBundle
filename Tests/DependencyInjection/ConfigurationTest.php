<?php

namespace Liip\OneallBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Liip\OneallBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testThatCanGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $configuration->getConfigTreeBuilder());
    }
}
