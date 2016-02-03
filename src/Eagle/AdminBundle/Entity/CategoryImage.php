<?php

namespace Eagle\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryImage
 */
class CategoryImage
{
    /**
     * @var integer
     */
    private $categoryId;

    /**
     * @var string
     */
    private $imageName;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set categoryId
     *
     * @param integer $categoryId
     * @return CategoryImage
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return integer 
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     * @return CategoryImage
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get imageName
     *
     * @return string 
     */
    public function getImageName()
    {
        return $this->imageName;
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
