<?php

namespace LoginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Login Controller
 *
 **/
Class LoginController Extends Controller
{
	public function SigninAction(Request $request)
	{
		$user = $this->container->get('login_bundle.login_service')->Signin($request);
		return $user;
	}

	/**
	 * Signup
	 *
	 */
	public function SignupAction(Request $request)
	{
        $response = $this->container->get('login_bundle.login_service')->Signup($request);
        return $response;
	}

	/**
	 *
	 * Signout
	 */
	public function SignoutAction(Request $request)
	{
		$response = $this->container->get('login_bundle.login_service')->Signout($request);
        return $response;	
	}

}

