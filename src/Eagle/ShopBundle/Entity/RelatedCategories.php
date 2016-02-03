<?php

namespace Eagle\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RelatedCategories
 *
 * @ORM\Table(name="related_categories")
 * @ORM\Entity
 */
class RelatedCategories
{
    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer", nullable=false)
     */
    private $categoryId;

    /**
     * @var string
     *
     * @ORM\Column(name="related_categories", type="text", nullable=false)
     */
    private $relatedCategories;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set categoryId
     *
     * @param integer $categoryId
     * @return RelatedCategories
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
     * Set relatedCategories
     *
     * @param string $relatedCategories
     * @return RelatedCategories
     */
    public function setRelatedCategories($relatedCategories)
    {
        $this->relatedCategories = $relatedCategories;

        return $this;
    }

    /**
     * Get relatedCategories
     *
     * @return string 
     */
    public function getRelatedCategories()
    {
        return $this->relatedCategories;
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
