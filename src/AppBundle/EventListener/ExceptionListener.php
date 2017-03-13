<?php 

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use AppBundle\Exception\NotFoundException;
use AppBundle\Exception\BadRequestException;

use JMS;


class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

            $result = [
                "success"    => false,
                "error"      => $exception->__toString(),
            ];

        // if ($exception instanceof NotFoundException or $exception instanceof BadRequestException) {
        //     $result["extra_info"] = $exception->getExtra();
        //     $response =self::getResponse($result, $exception->getCustomCode());
        // } else {
        //     $response =self::getResponse($result, Response::HTTP_INTERNAL_SERVER_ERROR);
        // }

        // // Send the modified response object to the event
        // $event->setResponse($response);
    }

    public function getResponse($data, $code = 500) {
        $serializer  = JMS\Serializer\SerializerBuilder::create()->build();

        $data        = $serializer->serialize($data, 'json');

        $response    = new Response($data, $code);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}