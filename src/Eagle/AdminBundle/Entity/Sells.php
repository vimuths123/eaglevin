<?php

namespace Eagle\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sells
 */
class Sells
{
    /**
     * @var integer
     */
    private $productId;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var string
     */
    private $date;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set productId
     *
     * @param integer $productId
     * @return Sells
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return integer 
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Sells
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set date
     *
     * @param string $date
     * @return Sells
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
