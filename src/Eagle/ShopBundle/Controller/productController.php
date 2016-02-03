<?php

namespace Eagle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class productController extends Controller {

    /**
     * @Route("product/index")
     * @Template()
     */
    public function indexAction() {
        return $this->render("EagleShopBundle:global:index.html.twig", array(
                    'image_path' => '/bundles/eagleshop/images/',
        ));
    }

}
