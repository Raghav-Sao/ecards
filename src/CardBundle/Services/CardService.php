<?php

namespace CardBundle\Services;

use AppBundle\Exception\NotFoundException;
use AppBundle\Exception\BadRequestException;

use CardBundle\Entity\Card;
use CardBundle\Entity\CardImage;
use CardBundle\Entity\SellerCardRelation;
use CardBundle\Entity\UserCardRelation;



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
        
		$this->validator   = $validator;
		$this->currentUser = $currentUser;
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

        $response    = $this->getResponse($cards, ["Default"]);

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
		$userRoles = $this->currentUser->getRoles();
		
		$params    = $request->getContent();
        $params    = json_decode($params, true);

		$card   = self::newCard($params);
		$seller = self::getSeller($params);
		
		$em     = $this->doctrine->getManager();

		switch ($userRoles) {

			case in_array('ROLE_SUPER_ADMIN', $userRoles):
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

		return $this->getResponse($result);
	}

	/**
	 *
	 * @param  {card} and {seller optional for ROLE_SUPER_ADMIN and self for ROLE_ADMIN} params
	 * 
	 * @return return [{card->id} and {seller->id if seller generate else seller->id will not returned}
	 */
	public function editSellerCardRelation(int $id, Request $request)
	{
		$userRoles = $this->currentUser->getRoles();
		
		$params    = $request->getContent();
		$params    = json_decode($params, true);

		$sellerCardRelation = $this->doctrine->getRepository("CardBundle:SellerCardRelation")->find($id);
		if(!$sellerCardRelation) {
			throw new BadRequestException('Invalid Id passed');
		}

		$seller               = self::getSeller($params);
		$card                 = $sellerCardRelation->getCard();
		$sellerCardRelationId = $sellerCardRelation->getId();
		
		$em = $this->doctrine->getManager();

		switch ($userRoles) {

			case in_array('ROLE_SUPER_ADMIN', $userRoles):
				if(!$seller)
					throw new BadRequestException('seller_id is mandatory param');

			default:
				$sellerCardRelation = self::newSellerCardRelation($card, $seller, $params, $sellerCardRelation );
				$em->persist($sellerCardRelation);
				break;
		}

		$em->flush();

		$result = [
			'success' => true,
			'msg'     => 'Edited product',
			'cardId'  => $card->getId()
		];

		if($seller) {
			$result['sellerCardRelationId'] = $sellerCardRelation->getId();
		}

		return $this->getResponse($result);
	}

	public function newCard($params, $cardId = "", $doSave = false)
	{
		$validationResult =  $this->validateParams($params, $this->getMandatoryCardParams());

		if(!$validationResult["success"]){
			throw new BadRequestException($validationResult["error"]);
		}

		if($cardId) {
			$card = $this->doctrine->getRepository('CardBundle:Card')->find($cardId);

			if(!$card) {
				throw new BadRequestException('Invalid card_id Passsed');
			}

		} else {
			$card = new Card();
		}

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

		if($doSave) {
			$em->persist($card);
			$em->flush();
		}

		return $card;
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
		$userRoles        = $this->currentUser->getRoles();

		$validationResult =  $this->validateParams($params, $this->getMandatorySellerCardParams());

		if(!$validationResult["success"]){
			if(in_array('ROLE_SUPER_ADMIN', $userRoles)) {
				return null;
			}
			throw new BadRequestException($validationResult["error"]);
		}

		switch ($userRoles) {
			case in_array('ROLE_SELLER', $userRoles):
				$seller = $this->doctrine->getRepository('CardBundle:Seller')->findOneByUser($this->currentUser);
				break;
			
			case (in_array('ROLE_SUPER_ADMIN', $userRoles) || in_array('ROLE_ADMIN', $userRoles)):
				$sellerId = $params['seller_id'];
				$seller   = $this->doctrine->getRepository('CardBundle:Seller')->find($sellerId);
				break;

			default:
				throw new BadRequestException('Invalid Request: Not Authorized');
		}

		if(!$seller) {
			throw new BadRequestException('Invalid Seller');
		}

		return $seller;
	}

	/**
	 * @param  [card,seller, other params - mandatory]
	 * @param  [doSave                    - Default False]
	 * 
	 * @return [sellerCardRelation instance without save if doSave is false else with save]
	 */

	public function newSellerCardRelation($card, $seller, $params, $sellerCardRelation = "", $doSave = false)
	{
		if(!$sellerCardRelation) {
			$sellerCardRelation = new SellerCardRelation();
		}

		$sellerCardRelation->setCard($card);
		$sellerCardRelation->setSeller($seller);
		$sellerCardRelation->setQuantity($params['quantity']);
		$sellerCardRelation->setPrice($params['price']);
		$sellerCardRelation->setPrintAvailable($params['print_available']);
		$sellerCardRelation->setPrintingCharge($params['printing_charge']);
		$sellerCardRelation->setExtraCharge($params['extra_charge']);
		$sellerCardRelation->setTaxPercentage($params['tax_percentage']);
		$sellerCardRelation->setIsActive($params['is_active']);
		$sellerCardRelation->setLastUpdatedAt(new \DateTime);

		if($doSave) {
			$em->persist($sellerCardRelation);
			$em->flush();
		}

		return $sellerCardRelation;
	}

	public function editCard(int $id, Request $request) //Make history api also
	{
		// $this->checkForAccess(['ROLE_SUPER_ADMIN', 'ROLE_SELLER']); //will do

		$request           = $request->getContent();

        $request           = json_decode($request, true);

		$validationResult =  $this->validateParams($request, $this->getMandatoryCardParams());

		if(!$validationResult["success"]){
			return $this->getResponse($validationResult);
		}
		
		$em      = $this->doctrine->getManager();

        $card    = $this->doctrine->getRepository('CardBundle:Card')->find($id);

        if(!$card) {
			$result = [
				"success" => false,
				"msg"     => "invalid card id"
			];
			return $this->getResponse($result);
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

		$em = $this->doctrine->getManager();
		$em->persist($card);
		$em->flush();

		$result = [
			'success'              => true,
			'msg'                  => 'Saved product',
			'cardId'               => $card->getId()
		];

		return $this->getResponse($result);
	}

	public function Cardselling(Request $request) {
		$request          = $request->getContent();

        $request          = json_decode($request, true);

		$validationResult = $this->validateParams($request, $this->getMandatoryCardSellingParams());	

		if(!$validationResult["success"]){
			return $this->getResponse($validationResult);
		}

		$SellerCardRelation = $this->doctrine->getRepository('CardBundle:SellerCardRelation')->find($request['seller_card_relation_id']);

		if(!$SellerCardRelation) {
			throw new BadRequestException('Invalid SellerCardRelation.');
		}
		$realPrice = $SellerCardRelation->getPrice();

		if($realPrice != $request['price']) {
			throw new BadRequestException('Invalid Price.');
		}

		$quantity = $SellerCardRelation->getQuantity();

		$userCardRelation = new UserCardRelation;

		$userCardRelation->setQuantity($request['quantity']);
		$userCardRelation->setPrice($realPrice);
		$userCardRelation->setSellerCardRelation($SellerCardRelation);
		$userCardRelation->setUser($this->currentUser);

		$em = $this->doctrine->getManager();
		$em->persist($userCardRelation);
		$em->flush();

		$result = [
			'success'              => true,
			'msg'                  => 'Saved product',
			'cardId'               => $userCardRelation->getId()
		];

		return $this->getResponse($result);



	}
}


