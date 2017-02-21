<?php

namespace AppBundle\Services;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;


class BaseService
{
	public function __construct(Doctrine $doctrine)
	{
		$this->doctrine = $doctrine;

	}
}