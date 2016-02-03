<?php

// src/EagleAdminBundle/Twig/AppExtension.php
namespace Eagle\AdminBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('imgFormat', array($this, 'imgFormater')),
        );
    }

    public function imgFormater($imgname)
    {
        $imgpos = explode('.',explode("img",$imgname)[1])[0];
        
//        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
//        $price = '$'.$price;

        return $imgpos;
    }

    public function getName()
    {
        return 'app_extension';
    }
}