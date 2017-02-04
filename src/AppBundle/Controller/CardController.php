<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serialize\Serialize;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use JMS;


/**
 * Card controller.
 *
 */
class CardController extends Controller
{
    /**
     * Lists all card entities.
     *
     */
    public function indexAction()
    {
        $em          = $this->getDoctrine()->getManager();

        $cards       = $em->getRepository('AppBundle:Card')->findAll();

        $serializer  = JMS\Serializer\SerializerBuilder::create()->build();

        $data        = $serializer->serialize($cards, 'json');

        $response    = new Response($data);

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Creates a new card entity.
     *
     */
    public function newAction(Request $request)
    {
        $card = new Card();
        $form = $this->createForm('AppBundle\Form\CardType', $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($card);
            $em->flush($card);

            return $this->redirectToRoute('card_show', array('id' => $card->getId()));
        }

        return $this->render('card/new.html.twig', array(
            'card' => $card,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a card entity.
     *
     */
    public function showAction($id)
    {
        $em          = $this->getDoctrine()->getManager();

        $cards       = $em->getRepository('AppBundle:Card')->find($id);
        
        if(!$cards) {
            var_dump($cards);die;
        }

        $serializer  = JMS\Serializer\SerializerBuilder::create()->build();

        $data        = $serializer->serialize($cards, 'json');

        $response    = new Response($data);
        
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Displays a form to edit an existing card entity.
     *
     */
    public function editAction(Request $request, Card $card)
    {
        $deleteForm = $this->createDeleteForm($card);
        $editForm = $this->createForm('AppBundle\Form\CardType', $card);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('card_edit', array('id' => $card->getId()));
        }

        return $this->render('card/edit.html.twig', array(
            'card' => $card,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
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

    /**
     * Creates a form to delete a card entity.
     *
     * @param Card $card The card entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Card $card)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('card_delete', array('id' => $card->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
