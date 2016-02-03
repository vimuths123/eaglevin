<?php

namespace Eagle\ShopBundle\Services;

use Symfony\Component\HttpFoundation\Response;

class Paypal {

    public $items;
    public $userDetails;
    public $merchant_email = 'vit@yahoo.com';
    public $currency_code = 'US';
    public $tax_1 = "10";
    public $shipping_1 = "10";
    public $production_url = 'https://www.paypal.com/cgi-bin/webscr?';
    public $sandbox_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';

    public function __construct($mode) {
        if ($mode == 'paypal') {
            $this->action = $this->production_url;
        } elseif ($mode == 'sandbox') {
            $this->action = $this->sandbox_url;
        }
    }

    public function setItems($items) {
        $this->items = $items;
    }

    public function getUserDetails($userDetails) {
        $this->userDetails = $userDetails;
    }

    public function pay() {

//        Create paypal form
        $form = '';
        $form .= '<form name="frm_payment_method" action="' . $this->action . '" method="post">';
        $form .= '<input type = "hidden" name = "cmd" value = "_cart">';
        $form .= '<input type = "hidden" name = "upload" value = "1">';
        $form .= '<input type = "hidden" name = "business" value = "' . $action = $this->merchant_email . '">';
        $form .= '<input type="hidden" name="address1" value="' . $this->userDetails['address'] . '">';
        $form .= '<input type="hidden" name="city" value="' . $this->userDetails['city'] . '">';
        $form .= '<input type="hidden" name="country_code" value="' . $this->userDetails['country_code'] . '">';
        $form .= '<input type="hidden" name="zip" value="' . $this->userDetails['zip'] . '">';
        $form .= '<input type="hidden" name="first_name" value="' . $this->userDetails['first_name'] . '">';
        $form .= '<input type="hidden" name="last_name" value="' . $this->userDetails['last_name'] . '">';
        $form .= '<input type="hidden" name="email" value="' . $this->userDetails['email'] . '">';
        $form .= '<input type="hidden" name="H_PhoneNumber" value="' . $this->userDetails['H_PhoneNumber'] . '">';

        $form .= '<input type = "hidden" name = "shipping_1" value = "' . $this->shipping_1 . '">';
        $form .= '<input type = "hidden" name = "tax_1" value = "' . $this->tax_1 . '">';

        $x = 1;
        foreach ($this->items as $item) {
            $form .= '<input type = "hidden" name = "item_name_' . $x . '" value = "' . $item['title'] . '">';
            $form .= '<input type = "hidden" name = "quantity_' . $x . '" value = "' . $item['quantity'] . '">';
            $form .= '<input type = "hidden" name = "amount_' . $x . '" value = "' . $item['price'] . '">';
            $x++;
        }

        $form .= '<script>';
        $form .= 'setTimeout("document.frm_payment_method.submit()", 2);';
        $form .= '</script>';
        $form .= '</form>';
        
        return $form;
    }

    public function chkVal() {
        return 'no config';
    }

}
