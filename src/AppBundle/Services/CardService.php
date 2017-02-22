<?php

namespace AppBundle\Services;

use AppBundle\Entity\Card;
use AppBundle\Entity\CardImage;
use AppBundle\Entity\SellerCardRelation;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
// use AppBundle\Exception\NotFoundHttpException;
use AppBundle\Exception\NotFoundException;
// use Symfony\Component\Validator\Constraints\DateTime;


// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;



class CardService Extends BaseService
{
	protected $validator;
	public function __construct(Doctrine  $doctrine, $validator)
	{
        parent::__construct($doctrine);
        $this->validator = $validator; 
	}

	/**
	 *@return Card/Cards
	 */
	public function getCard($id = null)
	{
		if($id) {
			$cards = $this->doctrine->getRepository('AppBundle:Card')->find($id);
        	// $cards       = $this->doctrine->getRepository('AppBundle:Card')->find(1);
        	
		} else{
        	$cards       = $this->doctrine->getRepository('AppBundle:Card')->findAll();
		}
		
		if(!$cards) {
			$cards = [
				"success" => false,
				"result"  => []
			];
		} else {
			$cards = [
				"success" => true,
				"result"  => $cards
			];
		}

        $serializer  = JMS\Serializer\SerializerBuilder::create()->build();

        $data        = $serializer->serialize($cards, 'json');

        $response    = new Response($data);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
	}

	public function newCard($request)
	{
		$request           = $request->getContent();

        $request           = json_decode($request, true);

		$validationResult =  self::validateCard($request);
		if(!$validationResult["success"]){
			return self::getResponse($validationResult);
		}

		$card = new Card();
		$card->setName($request['name']);
		$card->setShape($request['shape']);
		$card->setSize($request['size']);
		$card->setEventType($request['event_type']);
		$card->setColor($request['color']);
		$card->setLanguage($request['language']);
		$card->setReligion($request['religion']);
		$card->setTheme($request['theme']);

		$created_by = $this->doctrine->getRepository('AppBundle:Seller')->find($request['created_by']);
		if(!$created_by) {
			$result = [
				"success" => false,
				"result"  => "Invalid loged in User"
			];
			return self::getResponse($result);
		}

		$card->setCreatedBy($created_by);
		$em = $this->doctrine->getManager();
		$em->persist($card);
		

		$cardImg = new CardImage();
		$cardImg->setUrl($request['url']);
		$cardImg->setCard($card);
		$cardImg->setIsActive(true);
		$em->persist($cardImg);

		$sellerCardRelation = new SellerCardRelation();
		$sellerCardRelation->setCard($card);
		$sellerCardRelation->setSeller($created_by);
		$sellerCardRelation->setQuantity($request['quantity']);
		$sellerCardRelation->setPrice($request['price']);
		$sellerCardRelation->setPrintAvailable($request['print_available']);
		$em->persist($sellerCardRelation);

		$em->flush();

		$result = [
			'success'                 => true,
			'msg'                     => 'Saved new product',
			'cardId'                 => $card->getId(),
			'imgId'                  => $cardImg->getId(),
			'sellerCardRelationId'   => $sellerCardRelation->getId()
		];

		return self::getResponse($result);
	}

	public function editCard($id, $request)
	{
		$request           = $request->getContent();

        $request           = json_decode($request, true);

		$validationResult =  self::validateCard($request);



		if(!$validationResult["success"]){
			return self::getResponse($validationResult);
		}
		
		$em      = $this->doctrine->getManager();
		// $tableName = $em->getClassMetadata('AppBundle:Card')->getColumnName();

        $card    = $this->doctrine->getRepository('AppBundle:Card')->find($id);

        if(!$card) {
			$result = [
				"success" => false,
				"msg"     => "invalid card id"
			];
			return self::getResponse($result);
		}
		$card->setName($request['name']);
		$card->setShape($request['shape']);
		$card->setSize($request['size']);
		$card->setEventType($request['event_type']);
		$card->setColor($request['color']);
		$card->setLanguage($request['language']);
		$card->setReligion($request['religion']);
		$card->setTheme($request['theme']);

		$created_by = $this->doctrine->getRepository('AppBundle:Seller')->find($request['created_by']);
		if(!$created_by) {
			$result = [
				"success" => false,
				"result"  => "Invalid Passed  User"
			];
			return self::getResponse($result);
		}

		$card->setCreatedBy($created_by);
		$em = $this->doctrine->getManager();
		$em->persist($card);
		
        $cardImage = $this->doctrine->getRepository('AppBundle:CardImage')->findOneByCard($card);
        if(!$cardImage) {
			$result = [
				"success" => false,
				"result"  => "Invalid Image id"
			];
			return self::getResponse($result);
		}
		$cardImage->setUrl($request['url']);
		$cardImage->setCard($card);
		$cardImage->setIsActive(true);
		$em->persist($cardImage);

        $sellerCardRelation = $this->doctrine->getRepository('AppBundle:SellerCardRelation')->findOneByCard($card);
		$sellerCardRelation->setSeller($created_by);
		$sellerCardRelation->setCard($card);
		$sellerCardRelation->setQuantity($request['quantity']);
		$sellerCardRelation->setPrice($request['price']);
		$sellerCardRelation->setPrintAvailable($request['print_available']);
		$em->persist($sellerCardRelation);

		$em->flush();

		$result = [
			'success'              => true,
			'msg'                  => 'Saved product',
			'cardId'               => $card->getId(),
			'imgId'                => $cardImage->getId(),
			'sellerCardRelationId' => $sellerCardRelation->getId()
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

	public function validateCard($data)
	{
		$validated                      = true;
		$cardParams                    = array();
		$cardParams['name']            = gettype('abcd');
		$cardParams['price']           = getType(20.0);
		$cardParams['shape'] 	        = getType('abcd');
		$cardParams['size']  	        = getType('abcd');
		$cardParams['event_type']      = getType('abcd');
		$cardParams['color']           = getType('abcd');
		$cardParams['language']        = getType('abcd');
		$cardParams['religion']        = getType('abcd');
		$cardParams['theme']           = getType('abcd');
		$cardParams['created_by']      = getType(1);
		$cardParams['quantity']        = getType(1);
		$cardParams['price']           = getType(20);
		$cardParams['print_available'] = getType(true);
		$cardParams['url']             = getType([]);

		$validationResult = array();
        foreach ($cardParams as $key => $value) {
        	if(!isset($data[$key]) || $cardParams[$key] != getType($data[$key])){
        		$validationResult[$key] = $cardParams[$key];
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

	public function validateEditCard($data)
	{
		$validated                    = true;
		$cardParams                   = array();
		$cardParams['name']           = gettype('abcd');
		$cardParams['price']          = getType(20.0);
		$cardParams['shape'] 	      = getType('abcd');
		$cardParams['size']  	      = getType('abcd');
		$cardParams['event_type']      = getType('abcd');
		$cardParams['color']          = getType('abcd');
		$cardParams['language']       = getType('abcd');
		$cardParams['religion']       = getType('abcd');
		$cardParams['theme']          = getType('abcd');
		$cardParams['created_by']      = getType(1);
		$cardParams['quantity']       = getType(1);
		$cardParams['price']          = getType(20);
		$cardParams['print_available'] = getType(true);
		$cardParams['url']            = getType([]);

		$validationResult     = array();
		$is_atleast_one_param = false;
        foreach ($cardParams as $key => $value) {

        	if(isset($data[$key])){ 
        		if($cardParams[$key] != getType($data[$key])){
        			$validationResult[$key] = $cardParams[$key];
        		}
        		$is_atleast_one_param = true;
        	} 
        }
        $result = "";
        foreach ($validationResult as $key => $value) {
        	$result .= "$key should be $value,";
        }

        if (!$is_atleast_one_param) {
        	$validated = false;
        	$result .= "at least one param is mandatory";
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


