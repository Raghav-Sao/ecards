<?php

namespace CardBundle\Services;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Validator\Constraints as Assert;



class BaseService
{
	public function __construct(Doctrine $doctrine)
	{
		$this->doctrine = $doctrine;

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
}