<?php

namespace Eagle\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProuctImage
 */
class ProuctImage
{
    /**
     * @var integer
     */
    private $productId;

    /**
     * @var string
     */
    private $imgUrl;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set productId
     *
     * @param integer $productId
     * @return ProuctImage
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
     * Set imgUrl
     *
     * @param string $imgUrl
     * @return ProuctImage
     */
    public function setImgUrl($imgUrl)
    {
        $this->imgUrl = $imgUrl;

        return $this;
    }

    /**
     * Get imgUrl
     *
     * @return string 
     */
    public function getImgUrl()
    {
        return $this->imgUrl;
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
