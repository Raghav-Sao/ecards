<?php

namespace AppBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

class BadRequestException extends \Exception
{
    public function __construct(
        string     $message    = '',
        array      $extra      = ["raghav" => "sao"],
        string     $customCode = Response::HTTP_BAD_REQUEST,
        int        $code       = 0,
        \Exception $previous   = null
    )
    {

        parent::__construct($message, $code, $previous);

        $this->extra      = $extra;
        $this->customCode = $customCode;
    }

    public function __toString()
    {
        return $this->message;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function getCustomCode()
    {
        return $this->customCode;
    }
}
