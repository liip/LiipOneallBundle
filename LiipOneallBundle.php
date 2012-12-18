<?php

namespace Liip\OneallBundle;

use Liip\OneallBundle\DependencyInjection\Security\Factory\OneallFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LiipOneallBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new OneallFactory());
    }
}
