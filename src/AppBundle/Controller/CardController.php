<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


/**
 * Card controller.
 *
 */
class CardController extends Controller
{
    public function testAction()
    {
        return $this->render('AppBundle:Default:index.html.twig');
    }

    /**
     * Lists all card entities.
     *
     */
    public function indexAction()
    {
        $cards     = $this->container->get('app_bundle.card_service')->getCard();

        return $cards;
    }

    /**
     * Finds and displays a card entity.
     *
     */
    public function showAction($id)
    {

        $cards     = $this->container->get('app_bundle.card_service')->getCard($id);

        return $cards;
    }

    /**
     * Creates a new card entity.
     *
     */
    public function newAction(Request $request)
    {
        $response = $this->container->get('app_bundle.card_service')->newCard($request);
        return $response;

    }

    /**
     * Displays a form to edit an existing card entity.
     *
     */
    public function editAction($id, Request $request)
    { 
        $response = $this->container->get('app_bundle.card_service')->editCard($id, $request);
        return $response;   
    }

    /**
     * Deletes a card entity.
     *
     */
    public function deleteAction(Request $request, Card $card)
    {
        $form = $this->createDeleteForm($card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($card);
            $em->flush($card);
        }

        return $this->redirectToRoute('card_index');
    }
}
