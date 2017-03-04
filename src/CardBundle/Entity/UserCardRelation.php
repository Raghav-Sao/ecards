<?php

namespace CardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use LoginBundle\Entity\User;

/**
 * UserCardRelation
 *
 * @ORM\Table(name="user_card_relation")
 * @ORM\Entity(repositoryClass="CardBundle\Repository\UserCardRelationRepository")
 */
class UserCardRelation
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
     *
     * @ORM\ManyToOne(targetEntity = "SellerCardRelation")
     * @ORM\JoinColumn(name = "seller_card_relaton_id", referencedColumnName = "id")
     *
     */
    private $sellerCardRelation;

    /**
     *
     * @ORM\ManyToOne(targetEntity = "LoginBundle\Entity\User")
     * @ORM\JoinColumn(name = "user_id", referencedColumnName = "id")
     *
     */
    private $User;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="total_paid", type="float")
     */
    private $totalPaid;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;


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
     * Set price
     *
     * @param float $price
     *
     * @return UserCardRelation
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
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return UserCardRelation
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set totalPaid
     *
     * @param float $totalPaid
     *
     * @return UserCardRelation
     */
    public function setTotalPaid($totalPaid)
    {
        $this->totalPaid = $totalPaid;

        return $this;
    }

    /**
     * Get totalPaid
     *
     * @return float
     */
    public function getTotalPaid()
    {
        return $this->totalPaid;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return UserCardRelation
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}

