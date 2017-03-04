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

	/**
	 *
	 * Confirm-Signup
	 */
	public function ConfirmSignupAction($token)
	{
		$response = $this->container->get('login_bundle.login_service')->ConfirmSignup($token);
        return $response;	
	}

	/**
	 *
	 * Confirm-Signup
	 */
	public function ConfirmChangePasswordAction($token)
	{
		$response = $this->container->get('login_bundle.login_service')->ConfirmChangePassword($token);
        return $response;	
	}

	/**
	 *
	 * Reset confirmation
	 */
	public function ResendConfirmationTokenAction(Request $request)
	{
		$response = $this->container->get('login_bundle.login_service')->ResendConfirmationToken($request);
        return $response;
	}



}

