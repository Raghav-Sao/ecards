<?php

namespace CardBundle\Services;

use AppBundle\Exception\NotFoundException;
use AppBundle\Exception\BadRequestException;

use CardBundle\Entity\Card;
use CardBundle\Entity\CardImage;
use CardBundle\Entity\SellerCardRelation;



use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use JMS;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\HttpFoundation\Session\Session;




class CardService Extends BaseService
{
	protected $validator;
	protected $currentUser;
	public function __construct(Doctrine  $doctrine, $validator, $currentUser)
	{
        parent::__construct($doctrine);
        $this->validator = $validator;
        $this->currentUser      = $currentUser;
	}

	/**
	 *@return Card/Cards
	 */
	public function getCard($id = null)
	{
		if($id) {
			$cards = $this->doctrine->getRepository('CardBundle:Card')->find($id);
        	
		} else{
        	$cards       = $this->doctrine->getRepository('CardBundle:Card')->findAll();
		}
		
		if(!$cards) {
			$cards = [
				"success" => true,
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


	/**
	 *
	 * @param  {card} and {seller optional for ROLE_SUPER_ADMIN and self for ROLE_ADMIN} params
	 * 
	 * @return return [{card->id} and {seller->id if seller generate else seller->id will not returned}
	 */
	public function newSellerCard(Request $request)
	{
		$userRoles = $this->currentUser->getRoles()[0];
		
		$params    = $request->getContent();
        $params    = json_decode($params, true);

		$card   = self::newCard($params);
		$seller = self::getSeller($params);
		
		$em     = $this->doctrine->getManager();

		switch ($userRoles) {

			case 'ROLE_SUPER_ADMIN':
				if(!$seller)
					break;

			default:

				$sellerCardRelation = self::newSellerCardRelation($card, $seller, $params);
				$em->persist($sellerCardRelation);
				break;
		}

		$em->persist($card);
		$em->flush();

		$result = [
			'success' => true,
			'msg'     => 'Saved new product',
			'cardId'  => $card->getId()
		];

		if($seller) {
			$result['sellerCardRelationId'] = $sellerCardRelation->getId();
		}

		return self::getResponse($result);

	}

	public function newCard($params, $doSave = false)
	{
		$validationResult =  self::validateParams($params, self::getMandatoryCardParams());

		if(!$validationResult["success"]){
			throw new BadRequestException($validationResult["result"]);
		}

		$card = new Card();
		$card->setColor($params['color']);
		$card->setCreatedBy($this->currentUser);
		$card->setEventType($params['event_type']);
		$card->setImgUrl($params['img_url']);
		$card->setLanguage($params['language']);
		$card->setName($params['name']);
		$card->setReligion($params['religion']);
		$card->setShape($params['shape']);
		$card->setSize($params['size']);
		$card->setTheme($params['theme']);

		if(!$doSave) {
			return $card;
		}
	}


	/**
	 * @param [mandatory params to crete seller card relation]
	 * @param [ROLE_SUPER_ADMIN - Optoinal ]
	 * @param [ROLE_ADMIN       - seller_id and other params mandatory ]
	 * @param [ROLE_SELLER      - seller_id will current seller ]
	 *
	 * @return [ seller - based on params ]
	 */
	public function getSeller($params)
	{
		$currentUser      = $this->currentUser;
		$currentUserRoles = $currentUser->getRoles()[0];

		$validationResult =  self::validateParams($params, self::getMandatorySellerCardParams());

		if(!$validationResult["success"]){
			if($currentUserRoles == 'ROLE_SUPER_ADMIN') {
				return null;
			}
			throw new BadRequestException($validationResult["result"]);
		}

		if($currentUserRoles == 'ROLE_SELLER') {
			$seller = $this->doctrine->getRepository('CardBundle:Seller')->findOneByUser($currentUser);
		} elseif($currentUserRoles == 'ROLE_ADMIN') {
			$sellerId = $params['seller_id'];
			$seller   = $this->doctrine->getRepository('CardBundle:Seller')->find($sellerId);
			
			if(!$seller) {
				throw new BadRequestException('Invalid param: seller_id passed.');	
			}
		} else {
			throw new BadRequestException('Invalid Request: Not Authorized');	
		}
		
		switch ($currentUserRoles) {

			case 'ROLE_SUPER_ADMIN':
				if(!$seller) {
					return false;
					break;
				}

			default:
				if(!$seller) {
					throw new BadRequestException('seller_id is mandatory param');
				}

				return $seller;
				break;
		}
	}

	/**
	 * @param  [card,seller, other params - mandatory]
	 * @param  [doSave                    - Default False]
	 * 
	 * @return [sellerCardRelation instance without save if doSave is false else with save]
	 */

	public function newSellerCardRelation($card, $seller, $params, $doSave = false)
	{
		$sellerCardRelation = new SellerCardRelation();
		$sellerCardRelation->setCard($card);
		$sellerCardRelation->setSeller($seller);
		$sellerCardRelation->setQuantity($params['quantity']);
		$sellerCardRelation->setPrice($params['price']);
		$sellerCardRelation->setPrintAvailable($params['print_available']);
		$sellerCardRelation->setPrintingCharge($params['printing_charge']); //set it from parameter later
		$sellerCardRelation->setExtraCharge($params['extra_charge']); //set it from parameter later
		$sellerCardRelation->setTaxPercentage($params['tax_percentage']); //set it from parameter later
		$sellerCardRelation->setIsActive($params['is_active']); //set it from parameter later

		if($doSave) {
			$em->persist($sellerCardRelation);
			$em->flush();
		}

		return $sellerCardRelation;
	}

	public function editCard(int $id, Request $request)
	{
		$request           = $request->getContent();

        $request           = json_decode($request, true);

		$validationResult =  self::validateParams($request, self::getMandatoryCardParams());



		if(!$validationResult["success"]){
			return self::getResponse($validationResult);
		}
		
		$em      = $this->doctrine->getManager();

        $card    = $this->doctrine->getRepository('CardBundle:Card')->find($id);

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
		$card->setImgUrl($request['img_url']);

		$created_by = $this->doctrine->getRepository('CardBundle:Seller')->find($request['created_by']);
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

        $sellerCardRelation = $this->doctrine->getRepository('CardBundle:SellerCardRelation')->findOneByCard($card);
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

	public function validateParams($data, $mandatoryParams)
	{
		$validated        = true;
		$validationResult = array();

        foreach ($mandatoryParams as $key => $value) {
        	if(!isset($data[$key]) || $mandatoryParams[$key] != getType($data[$key])){
        		$validationResult[$key] = $mandatoryParams[$key];
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
		$validated                     = true;
		$cardParams                    = array();
		$cardParams['color']           = getType('abcd');
		$cardParams['created_by']      = getType(1);
		$cardParams['event_type']      = getType('abcd');
		$cardParams['img_url']         = getType([]);
		$cardParams['language']        = getType('abcd');
		$cardParams['name']            = gettype('abcd');
		$cardParams['price']           = getType(20.0);
		$cardParams['print_available'] = getType(true);
		$cardParams['quantity']        = getType(1);
		$cardParams['religion']        = getType('abcd');
		$cardParams['shape']           = getType('abcd');
		$cardParams['size']            = getType('abcd');
		$cardParams['theme']           = getType('abcd');

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

	public function getMandatoryCardParams()
	{
		$mandatoryCardParams               = array();
		$mandatoryCardParams['color']      = getType('abcd');
		$mandatoryCardParams['created_by'] = getType(1);
		$mandatoryCardParams['event_type'] = getType('abcd');
		$mandatoryCardParams['img_url']    = getType([]);
		$mandatoryCardParams['language']   = getType('abcd');
		$mandatoryCardParams['name']       = gettype('abcd');
		$mandatoryCardParams['religion']   = getType('abcd');
		$mandatoryCardParams['shape']      = getType('abcd');
		$mandatoryCardParams['size']       = getType('abcd');
		$mandatoryCardParams['theme']      = getType('abcd');
		return $mandatoryCardParams;
	}

	public function getMandatorySellerCardParams($incluedSeller = True)
	{
		$mandatorySellerCardParams                   = array();
		$mandatorySellerCardParams['extra_charge']    = getType(20.0);
		$mandatorySellerCardParams['is_active']       = getType(true);
		$mandatorySellerCardParams['price']          = getType(20.0);
		$mandatorySellerCardParams['print_available'] = getType(true);
		$mandatorySellerCardParams['printing_charge'] = getType(20.0);
		$mandatorySellerCardParams['quantity']       = getType(1);
		$mandatorySellerCardParams['tax_percentage']  = getType(20.0);

		$currentUser      = $this->currentUser;
		$currentUserRoles = $currentUser->getRoles()[0];
		if($currentUserRoles == 'ROLE_SUPER_ADMIN' || $currentUserRoles == 'ROLE_ADMIN') {
			$mandatorySellerCardParams['seller_id']       = getType(1);
		}

		return $mandatorySellerCardParams;
	}
}


