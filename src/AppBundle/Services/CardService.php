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
		$id = 1;
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

		$validation_result =  self::validateCard($request);
		if(!$validation_result["success"]){
			return self::getResponse($validation_result);
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
		

		$card_img = new CardImage();
		$card_img->setUrl($request['url']);
		$card_img->setCard($card);
		$card_img->setIsActive(true);
		$em->persist($card_img);

		$seller_card_relation = new SellerCardRelation();
		$seller_card_relation->setCard($card);
		$seller_card_relation->setSeller($created_by);
		$seller_card_relation->setQuantity($request['quantity']);
		$seller_card_relation->setPrice($request['price']);
		$seller_card_relation->setPrintAvailable($request['print_available']);
		$em->persist($seller_card_relation);

		$em->flush();

		$result = [
			'success'                 => true,
			'msg'                     => 'Saved new product',
			'card_id'                 => $card->getId(),
			'img_id'                  => $card_img->getId(),
			'seller_card_relation_id' => $seller_card_relation->getId()
		];

		return self::getResponse($result);
	}

	public function editCard($id, $request)
	{
		$request           = $request->getContent();

        $request           = json_decode($request, true);

		$validation_result =  self::validateCard($request);



		if(!$validation_result["success"]){
			return self::getResponse($validation_result);
		}
		
		$em        = $this->doctrine->getManager();
		$tableName = $em->getClassMetadata('AppBundle:Card')->getColumnName();

        $cards     = $this->doctrine->getRepository('AppBundle:Card')->find($id);

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
		
		$card_img->setUrl($request['url']);
		$card_img->setCard($card);
		$card_img->setIsActive(true);
		$em->persist($card_img);

		$seller_card_relation = new SellerCardRelation();
		$seller_card_relation->setCard($card);
		$seller_card_relation->setSeller($created_by);
		$seller_card_relation->setQuantity($request['quantity']);
		$seller_card_relation->setPrice($request['price']);
		$seller_card_relation->setPrintAvailable($request['print_available']);
		$em->persist($seller_card_relation);

		$em->flush();

		$result = [
			'success'                 => true,
			'msg'                     => 'Saved new product',
			'card_id'                 => $card->getId(),
			'img_id'                  => $card_img->getId(),
			'seller_card_relation_id' => $seller_card_relation->getId()
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
		$card_params                    = array();
		$card_params['name']            = gettype('abcd');
		$card_params['price']           = getType(20.0);
		$card_params['shape'] 	        = getType('abcd');
		$card_params['size']  	        = getType('abcd');
		$card_params['event_type']      = getType('abcd');
		$card_params['color']           = getType('abcd');
		$card_params['language']        = getType('abcd');
		$card_params['religion']        = getType('abcd');
		$card_params['theme']           = getType('abcd');
		$card_params['created_by']      = getType(1);
		$card_params['quantity']        = getType(1);
		$card_params['price']           = getType(20);
		$card_params['seller']          = getType(1);
		$card_params['print_available'] = getType(true);
		$card_params['url']             = getType([]);

		$validation_result = array();
        foreach ($card_params as $key => $value) {
        	if(!isset($data[$key]) || $card_params[$key] != getType($data[$key])){
        		$validation_result[$key] = $card_params[$key];
        	}
        }
        $result = "";
        foreach ($validation_result as $key => $value) {
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
	    $validation_result = [
				"success" => $validated,
				"result"  => $result
			];
		return $validation_result;
	}

	public function validateEditCard($data)
	{
		$validated                      = true;
		$card_params                    = array();
		$card_params['name']            = gettype('abcd');
		$card_params['price']           = getType(20.0);
		$card_params['shape'] 	        = getType('abcd');
		$card_params['size']  	        = getType('abcd');
		$card_params['event_type']      = getType('abcd');
		$card_params['color']           = getType('abcd');
		$card_params['language']        = getType('abcd');
		$card_params['religion']        = getType('abcd');
		$card_params['theme']           = getType('abcd');
		$card_params['created_by']      = getType(1);
		$card_params['quantity']        = getType(1);
		$card_params['price']           = getType(20);
		$card_params['seller']          = getType(1);
		$card_params['print_available'] = getType(true);
		$card_params['url']             = getType([]);

		$validation_result    = array();
		$is_atleast_one_param = false;
        foreach ($card_params as $key => $value) {

        	if(isset($data[$key])){ 
        		if($card_params[$key] != getType($data[$key])){
        			$validation_result[$key] = $card_params[$key];
        		}
        		$is_atleast_one_param = true;
        	} 
        }
        $result = "";
        foreach ($validation_result as $key => $value) {
        	$result .= "$key should be $value,";
        }

        if (!$is_atleast_one_param) {
        	$validated = false;
        	$result .= "at least one param is mandatory";
        }
	    if($result) {
	    	$validated = false;
	    }
	    $validation_result = [
				"success" => $validated,
				"result"  => $result
			];
		return $validation_result;
	}
}


