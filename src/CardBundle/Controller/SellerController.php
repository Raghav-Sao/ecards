<?php

namespace CardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 *Seller Controller
 *
 */

class SellerController extends Controller
{
	/**
     * Lists all Seller entities.
     *
     */
    public function indexAction()
    {
        $sellers = $this->container->get('card_bundle.seller_service')->getSeller();

        return $sellers;
    }

    /**
     * Finds and displays a Seller entity.
     *
     */
    public function showAction($id)
    {

        $seller = $this->container->get('card_bundle.seller_service')->getSeller($id);

        return $seller;
    }

	/**
     * Creates a new Seller entity.
     *
     */
    public function newAction(Request $request)
    {
        $response = $this->container->get('card_bundle.seller_service')->newSeller($request);
        return $response;

    }

    /**
     * edit an existing Seller entity.
     *
     */
    public function editAction($id, Request $request)
    { 
        $response = $this->container->get('card_bundle.seller_service')->editSeller($id, $request);
        return $response;   
    }
}