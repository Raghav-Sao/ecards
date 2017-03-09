<?php

namespace LoginBundle\Services;

use CardBundle\Services\BaseService;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use JMS;
use LoginBundle\Entity\User;

use Symfony\Component\DependencyInjection\ContainerInterface as ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;




class LoginService Extends BaseService
{
	protected $validator;
	public function __construct($authorization_checker, ContainerInterface $container, Doctrine $doctrine, $validator)
	{
        parent::__construct($doctrine);

        $this->authorization_checker = $authorization_checker;

        $this->container = $container;

        $this->validator = $validator; 
	}

	public function Signin(Request $request)
	{
		$session = $request->getSession();
		$newrequest = $request;
		if($request->getMethod() =='POST') {
            $session->clear();
			$usr= $this->container->get('security.token_storage')->getToken()->getUser();

			$request = $request->getContent();
	        $request = json_decode($request, true);

			$validationResult = self::validation($request,['email' => "string", "password" => "string"]);
			
			if(!$validationResult["success"]){
				return self::getResponse($validationResult);
			}

			$email    = $request['email'];
			$password = $request['password'];
		} 

		elseif ($session->has('user')) {
			$user     = $session->get('user');
			$email    = $user->getEmail();
			$password = $user->getPassword();
		}

		else {
			$result       = [
				'success' => false,
				'msg'     => "Invalid Request",
			];
			return self::getResponse($result);
		}

		$user     = $this->doctrine->getRepository('LoginBundle:User');
		$user     = $user->findOneBy(['email' => $email]);
		
		if(!$user) {
			$result       = [
				'success' => false,
				'error'   => "Email is not Registered",
				'email'   => $email,
			];
			return self::getResponse($result);
		}

		$isEnabled = $user->isEnabled();
		if(!$isEnabled) {
			$result       = [
				'success' => false,
				'error'   => "Email is Registered but not Activate",
				'email'   => $email,
			];
			return self::getResponse($result);
		}

		$isValid = $this->container->get('security.password_encoder')->isPasswordValid($user, $password);

		if (!$isValid) {
	        $result       = [
				'success' => false,
				'msg'     => "Wrong Password",
				'email'   => $email,
			];
			return self::getResponse($result);
		}



		$session->set('user', $user);

		$token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
		var_dump($token);
		$this->container->get("security.token_storage")->setToken($token);
	    $event = new InteractiveLoginEvent($newrequest, $token);
	    $this->container->get("event_dispatcher")->dispatch("security.interactive_login", $event);


		
		$result       = [
			'success' => true,
			'msg'     => "Successfully Signed in",
			'email'   => $email,
		];

		return self::getResponse($result);
	}

	public function Signup(Request $request)
	{
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

		$request       = $request->getContent();
        $request       = json_decode($request, true);

        $validationResult = self::validation(
        	$request,
        	[
        		'username'      => 'string',
        		'email'         => 'string',
        		'password'      => 'string',
        		'first_name'    => 'string',
        		'last_name'     => 'string',
        		'mobile_number' => 'string',
        	]
        );
		
		if(!$validationResult["success"]){
			return self::getResponse($validationResult);
		}

		$username      = $request['username'];
		$email         = $request['email'];
		$password      = $request['password'];
		$first_name    = $request['first_name'];
		$last_name     = $request['last_name'];
		$mobile_number = $request['mobile_number'];

		$user       = $this->doctrine->getRepository('LoginBundle:User');
		$user       = $user->findOneByEmail($email);

		if($user) {
			$result       = [
				'success' => false,
				'error'   => "Already User Exist with $email"
			];
			return self::getResponse($result);
		}

		$userManager = $this->container->get('fos_user.user_manager');
		$user        = $userManager->createUser();

		$user->setUsername($username);
		$user->setEmail($email);
		$user->setPlainPassword($password);
		$user->setFirstName($first_name);
		$user->setLastName($last_name);
		$user->setMobileNumber($mobile_number);
		$user->addRole('Admin');
       	$user->setEnabled(!$confirmationEnabled);

        $userManager->updateUser($user);
	
		if ($confirmationEnabled) {
			if (null === $user->getConfirmationToken()) {
	            $tokenGenerator = $this->container->get('fos_user.util.token_generator');

	            $user->setConfirmationToken($tokenGenerator->generateToken());
	        }

			$this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
        	$this->container->get('fos_user.mailer')->sendConfirmationEmailMessage($user);
			$this->container->get('fos_user.user_manager')->updateUser($user);
			
			$result       = [
				'success' => true,
				'msg'     => "Confirmation Email Send to $email",
				'email'   => $user->getEmail(),
				'id'      => $user->getId(),
			];

			return self::getResponse($result);
        }

		$result       = [
			'success' => true,
			'msg'     => "Successfully Registered",
			'email'   => $user->getEmail(),
			'id'      => $user->getId(),
		];

		return self::getResponse($result);
	}

	public function ConfirmSignup($token)
	{
		$user = $this->doctrine->getRepository('LoginBundle:User');
		$user = $user->findOneByConfirmationToken($token);

		if (!$user) {
			$result       = [
				'success' => false,
				'msg'     => "Invalid Token",
			];
			return self::getResponse($result);
		}

		$user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());

        $this->container->get('fos_user.user_manager')->updateUser($user);

        $result       = [
			'success' => True,
			'msg'     => "Account Verified Successfully",
		];
		return self::getResponse($result);

	}

	public function ConfirmChangePassword($token)
	{
		$user = $this->doctrine->getRepository('LoginBundle:User');
		$user = $user->findOneByConfirmationToken($token);

		if (!$user) {
			$result       = [
				'success' => false,
				'msg'     => "Invalid Token",
			];
			return getResponse($result);
		}

		$user->setConfirmationToken(null);
        $user->setPlainPassword("password");

        $this->container->get('fos_user.user_manager')->updateUser($user);

        $result       = [
			'success' => True,
			'msg'     => "Password Changed Successfully",
		];
		return self::getResponse($result);

	}

	public function ResendConfirmationToken($request){
		$request = $request->getContent();
		$request = json_decode($request, true);

        $validationResult = self::validation(
        	$request,
        	[
        		'email'             => 'string',
        		'confirmation_type' => 'string'
        	]
        );

		if(!$validationResult["success"]){
			return self::getResponse($validationResult);
		}

		$email            = $request['email'];
		$confirmationType = $request["confirmation_type"];

		$user  = $this->doctrine->getRepository('LoginBundle:User');
		$user  = $user->findOneByEmail($email);

		if(!$user) {
			$result       = [
				'success' => false,
				'error'   => "Invalid Email Address"
			];
			return self::getResponse($result);
		}

		if($confirmationType == 'account_activation' and $user->isEnabled()) {
			$result       = [
				'success' => false,
				'error'   => "Alredy Account Activated"
			];
			return self::getResponse($result);
		}

		if ($user->getConfirmationToken() === null) {
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');

            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());

        if($confirmationType == 'account_activation'){

        	$this->container->get('fos_user.mailer')->sendConfirmationEmailMessage($user);
        }

        elseif ($confirmationType == 'reset_password') {
        	$this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        }

        else {
        	$result       = [
				'success' => false,
				'error'   => "Invalid Request"
			];
			return self::getResponse($result);
        }

		$this->container->get('fos_user.user_manager')->updateUser($user);

		$result       = [
			'success' => true,
			'error'   => "Confirmation Email Send to $email"
		];
		return self::getResponse($result);
	} 

	public function Signout($request)
	{
		$session = $request->getSession();
		$session->clear();
		$this->container->get("security.token_storage")->setToken(null);

		$result = [
			"success" => true,
			"msg"     => "Successfully Signout"
		];

		return self::getResponse($result);
	}


	public function getResponse($data) {
		$serializer              = JMS\Serializer\SerializerBuilder::create()->build();

        $data                    = $serializer->serialize($data, 'json');

        $response                = new Response($data);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
	}

	public function validation($request, $requiredParam)
	{
		$validated = true;
		$validationResult = array();
		foreach ($requiredParam as $key => $value) {
			if(!isset($request[$key]) || $value != getType($request[$key])){
				$validationResult[$key] = "should be $value";
			}
		}
		if(isset($requiredParam['email']) && !isset($request['email'])){
        	$validationResult['email'] = "is mandatory";
        }
        else {
			$email 			 = $request["email"];
			$emailConstraint = new Assert\Email();
		    $emailConstraint->message = 'Invalid email address';
		    $errorList = $this->validator->validate(
		        $email,
		        $emailConstraint
		    );
		   
		    
		    if (count($errorList)) {
		    	$errorMessage = $errorList[0]->getMessage();
		        $errorMessage = $errorList[0]->getMessage();
		        $validationResult['email'] = "email is invalid";
		    }
	    }

	    $result = "";
        foreach ($validationResult as $key => $value) {
        	$result .= "$key $value,";
        }

	    if($result) {
	    	$validated = false;
	    }
	    $validationResult = [
				"success" => $validated,
				"result"  => $result
			];
		return $validationResult;
	}
}
