<?php

namespace Beast\Framework\Support;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
	public function setContainer(ContainerInterface $container);

	public function getContainer(): ContainerInterface;
}
