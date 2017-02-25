<?php

namespace LoginBundle\Services;

use AppBundle\Services\BaseService;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints as Assert;

use JMS;

use LoginBundle\Entity\User;




class LoginService Extends BaseService
{
	protected $validator;
	public function __construct(Doctrine $doctrine, $validator)
	{
        parent::__construct($doctrine);
        $this->validator = $validator; 
	}

	public function Signin(Request $request)
	{
		// $em = $this->doctrine->getManager();

		// $tableName = $em->getClassMetadata('LoginBundle:User')->getFieldNames();
		// var_dump($tableName);die;
		$session = $request->getSession();

		$request = $request->getContent();
        $request = json_decode($request, true);
		
		$session->clear();

		$validationResult = self::validation($request,['email' => "string", "password" => "string"]);
		
		if(!$validationResult["success"]){
			return self::getResponse($validationResult);
		}

		$email    = $request['email'];
		$password = $request['password'];

		$user     = $this->doctrine->getRepository('LoginBundle:User');
		$user     = $user->findOneBy(array('email' => $email, 'password' => $password));

		if(!$user) {
			$result       = [
				'success' => false,
				'error'   => "Invalide UserId or Password"
			];
			self::Response($result);
		}

		$user   = new User();
		$user->setEmail($email);
		$user->setPassword($password);

		$session->set('user', $user);
		
		$result       = [
			'success' => true,
			'msg'     => "Successfully Signed in",
			'email'   => $email,
		];

		return self::getResponse($result);
	}

	public function Signup(Request $request)
	{

		$requestMethod = $request->getMethod();
		$request       = $request->getContent();
        $request       = json_decode($request, true);

        $validationResult = self::validation(
        	$request,
        	[
        		'email'         => 'string',
        		'password'      => 'string',
        		'first_name'    => 'string',
        		'last_name'     => 'string',
        		'mobile_number' => 'string'
        	]
        );
		
		if(!$validationResult["success"]){
			return self::getResponse($validationResult);
		}

		if($requestMethod == 'POST') {

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
					'error'   => "Already User Exist"
				];
				return self::getResponse($result);
			}

			$user   = new User();
			$user->setEmail($email);
			$user->setPassword($password);
			$user->setFirstName($first_name);
			$user->setLastName($last_name);
			$user->setMobileNumber($mobile_number);
			$em = $this->doctrine->getManager();
			$em->persist($user);
			$em->flush();
		
			$result       = [
				'success' => true,
				'msg'     => "Successfully Registered",
				'email'   => $user->getEmail(),
				'id'   => $user->getId(),
			];

			return self::getResponse($result);

		}
	}

	public function Signout($request)
	{
		$session = $request->getSession();
		$session->clear();

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
