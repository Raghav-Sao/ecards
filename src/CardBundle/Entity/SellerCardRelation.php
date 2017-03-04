<?php

namespace CardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * SellerCardRelation
 *
 * @UniqueEntity("card")
 * @ORM\Table(name="seller_card_relation")
 * @ORM\Entity(repositoryClass="CardBundle\Repository\SellerCardRelationRepository")
 */
class SellerCardRelation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Card", inversedBy="sellerCardRelation")
     * @ORM\JoinColumn(name="card_id", referencedColumnName="id")
     */
    private $card;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Seller")
     * @ORM\JoinColumn(name="seller_id", referencedColumnName="id")
     */
    private $seller;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;

    /**
     * @var bool
     *
     * @ORM\Column(name="print_available", type="boolean")
     */
    private $printAvailable;

    /**
     * @var float
     *
     * @ORM\Column(name="printing_charge", type="float")
     */
    private $printingCharge;

    /**
     * @var float
     *
     * @ORM\Column(name="extra_charge", type="float")
     */
    private $extraCharge;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_percentage", type="float")
     */
    private $taxPercentage;

    /**
     * @var bool
     * @Assert\NotNull()
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set card
     *
     * @param Card $card
     *
     * @return SellerCardRelation
     */
    public function setCard($card)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set seller
     *
     * @param Seller $seller
     *
     * @return SellerCardRelation
     */
    public function setSeller($seller)
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * Get seller
     *
     * @return Seller
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return SellerCardRelation
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set printingCharge
     *
     * @return float
     */
    public function setPrintingCharge($printingCharge)
    {
        $this->printingCharge = $printingCharge;
        return $this;
    }

    /**
     * Get printingCharge
     *
     * @return float
     */
    public function getPrintingCharge()
    {
        return $this->price;
    }

    /**
     * Set extraCharge
     *
     * @return float
     */
    public function setExtraCharge($extraCharge)
    {
        $this->extraCharge = $extraCharge;
        return $this;
    }

    /**
     * Get extraCharge
     *
     * @return float
     */
    public function getExtraCharge()
    {
        return $this->extraCharge;
    }

    /**
     * Set taxPercentage
     *
     * @return float
     */
    public function setTaxPercentage($taxPercentage)
    {
        $this->taxPercentage = $taxPercentage;
        return $this;
    }

    /**
     * Get taxPercentage
     *
     * @return float
     */
    public function getTaxPercentage()
    {
        return $this->taxPercentage;
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     *
     * @return SellerCardRelation
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set printAvailable
     *
     * @param boolean $printAvailable
     *
     * @return SellerCardRelation
     */
    public function setPrintAvailable($printAvailable)
    {
        $this->printAvailable = $printAvailable;

        return $this;
    }

    /**
     * Get printAvailable
     *
     * @return bool
     */
    public function getPrintAvailable()
    {
        return $this->printAvailable;
    }

    
    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return CardImage
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
