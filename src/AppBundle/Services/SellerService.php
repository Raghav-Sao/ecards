<?php

namespace AppBundle\Services;

use AppBundle\Entity\Seller;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
// use AppBundle\Exception\NotFoundHttpException;
use AppBundle\Exception\NotFoundException;
// use Symfony\Component\Validator\Constraints\DateTime;


// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;



class SellerService Extends BaseService
{
	protected $validator;
	public function __construct(Doctrine  $doctrine, $validator)
	{
        parent::__construct($doctrine);
        $this->validator = $validator; 
	}

	/**
	 *@return Selle/Sellers
	 */
	public function getSeller($id = null)
	{
		if($id) {
			$seller = $this->doctrine->getRepository('AppBundle:Seller')->find($id);
        	
		} else{
        	$seller       = $this->doctrine->getRepository('AppBundle:Seller')->findAll();
		}
		
		if(!$seller) {
			$seller = [
				"success" => false,
				"result"  => []
			];
		} else {
			$seller = [
				"success" => true,
				"result"  => $seller
			];
		}

        $serializer  = JMS\Serializer\SerializerBuilder::create()->build();

        $data        = $serializer->serialize($seller, 'json');

        $response    = new Response($data);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
	}

	public function newSeller($request)
	{
		$request           = $request->getContent();

        $request           = json_decode($request, true);

		$validationResult =  self::validateSeller($request);
		if(!$validationResult["success"]){
			return self::getResponse($validationResult);
		}

		$seller = new Seller();
        $seller->setName($request["name"]);
        $seller->setEmail($request["email"]);
        $seller->setMobileNumber($request["mobile_number"]);
        $seller->setState($request["state"]);
        $seller->setCity($request["city"]);
        $seller->setAddress($request["address"]);

        $em     = $this->doctrine->getManager();
		$em->persist($seller);

		$em->flush();

		$result = [
			'success' => true,
			'msg'     => 'Saved Seller Succesfully',
			'sellerId'  => $seller->getId()
		];

		return self::getResponse($result);
	}

	public function editSeller($id, $request)
	{
		$request           = $request->getContent();

        $request           = json_decode($request, true);

		$validationResult =  self::validateSeller($request);



		if(!$validationResult["success"]){
			return self::getResponse($validationResult);
		}
		
		$em     = $this->doctrine->getManager();
        $seller = $this->doctrine->getRepository('AppBundle:Seller')->find($id);

        if(!$seller) {
			$result = [
				"success" => false,
				"msg"     => "invalid seller id"
			];
			return self::getResponse($result);
		}

		$seller->setName($request["name"]);
        $seller->setEmail($request["email"]);
        $seller->setMobileNumber($request["mobile_number"]);
        $seller->setState($request["state"]);
        $seller->setCity($request["city"]);
        $seller->setAddress($request["address"]);
		
		$em->persist($seller);
		$em->flush();
		
		$result = [
			'success'              => true,
			'msg'                  => 'Saved product',
			'sellerId' => $seller->getId()
		];

		return self::getResponse($result);
	}

	public function getResponse($data) {
		$serializer  = JMS\Serializer\SerializerBuilder::create()->build();

        $data        = $serializer->serialize($data, 'json');

        $response    = new Response($data);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
	}

	public function validateSeller($data)
	{
		$validated                    = true;
		$sellerParams                 = array();
		$sellerParams['name']         = gettype('abcd');
		$sellerParams['mobile_number'] = gettype('abcd');
		$sellerParams['state']        = gettype('abcd');
		$sellerParams['city']         = gettype('abcd');
		$sellerParams['address']      = gettype('abcd');

		$validationResult = array();
        foreach ($sellerParams as $key => $value) {
        	if(!isset($data[$key]) || $sellerParams[$key] != getType($data[$key])){
        		$validationResult[$key] = $sellerParams[$key];
        	}
        }
        $result = "";
        foreach ($validationResult as $key => $value) {
        	$result .= "$key should be $value,";
        }

        if(!isset($data['email'])){
        	$result .= "email is mandatory";
        }
        else {
			$email 			 = $data["email"];
			$emailConstraint = new Assert\Email();
		    $emailConstraint->message = 'Invalid email address';
		    $errorList = $this->validator->validate(
		        $email,
		        $emailConstraint
		    );
		   
		    
		    if (count($errorList)) {
		    	$errorMessage = $errorList[0]->getMessage();
		        $errorMessage = $errorList[0]->getMessage();
		        $result .= "email is invalid";
		    }
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


