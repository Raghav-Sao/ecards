<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping                    as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CardImage
 *
 * @ORM\Table(name="card_image")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CardImageRepository")
 */
class CardImage
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
     * @ORM\ManyToOne(targetEntity="Card", inversedBy="cardImage")
     * @ORM\JoinColumn(name="card_id", referencedColumnName="id")
     */
    private $card;

    /**
     * @var string
     * @Assert\NotNull()
     * @ORM\Column(name="url", type="string", length=510)
     */
    private $url;

    /**
     * @var bool
     * @Assert\NotNull()
     * @ORM\Column(name="isActive", type="boolean")
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
     * @return CardImage
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
     * Set url
     *
     * @param string $url
     *
     * @return CardImage
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
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

