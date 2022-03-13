<?php

namespace Beast\Framework\Support;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container): void;

    public function getContainer(): ContainerInterface;
}
