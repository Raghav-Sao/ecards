<?php

namespace CardBundle\Services;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\DependencyInjection\ContainerAware;


class BaseService
{
	public function __construct(Doctrine $doctrine)
	{
		$this->doctrine = $doctrine;

	}
}