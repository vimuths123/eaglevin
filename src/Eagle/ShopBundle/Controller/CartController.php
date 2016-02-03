<?php

namespace Eagle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CartController extends Controller {

    /**
     * @Route("/cart/add")
     * @Template()
     */
    public function addAction(Request $request) {
        $session = $this->getRequest()->getSession();
        $items = $session->get('items');
        $id = $request->request->get('pid');
        $quantity = $request->request->get('quantity');
        $price = $request->request->get('price');
        $title = $request->request->get('title');

        if ($items === NULL) {
            $items = array();
        }

        if (array_key_exists($id, $items)) {
            $items[$id]['quantity'] = $items[$id]['quantity'] + $quantity;
        } else {
            $items[$id] = array(
                'quantity' => $quantity,
                'price' => $price,
                'title' => $title
            );
        }

        $session->set('items', $items);

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($session->get('items'), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("/cart/remove")
     * @Template()
     */
    public function removeAction(Request $request) {
        $id = $request->request->get('pid');
        $session = $this->getRequest()->getSession();
        $items = $session->get('items');

        if ($items === NULL) {
            $items = array();
        }

        unset($items[$id]);
        $session->set('items', $items);

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($session->get('items'), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("/cart/removeall")
     * @Template()
     */
    public function removeallAction() {
        $session = $this->getRequest()->getSession();
        $session->remove('items');
    }

    /**
     * @Route("/cart/viewall")
     * @Template()
     */
    public function viewallAction() {
        $session = $this->getRequest()->getSession();

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($session->get('items'), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("/cart/update")
     * @Template()
     */
    public function updateAction(Request $request) {
        $id = $request->request->get('pid');
        $quantity = $request->request->get('quantity');

        $session = $this->getRequest()->getSession();
        $items = $session->get('items');

        if ($items === NULL) {
            $items = array();
        }

        $items[$id]['quantity'] = $quantity;

        $session->set('items', $items);

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($session->get('items'), 'json');
        return new Response($jsonproducts);
    }

}
