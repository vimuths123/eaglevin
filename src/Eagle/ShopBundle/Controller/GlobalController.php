<?php

namespace Eagle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Eagle\ShopBundle\Entity\Sells;

class GlobalController extends Controller {

    public function getLatestProduct($amount) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->select('p.id, p.productTitle, p.price, p.description, pi.imgUrl')
                ->from('EagleShopBundle:Products', 'p')
                ->leftJoin('EagleShopBundle:ProuctImage', 'pi', \Doctrine\ORM\Query\Expr\Join::WITH, 'pi.productId = p.id')
                ->orderBy('p.id', 'DESC')
                ->groupBy('p.id')
                ->setMaxResults($amount);

        return $qb->getQuery()->getResult();
    }

    public function getLatestCategory($amount) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->select('c')
                ->from('EagleShopBundle:ProductCategory', 'c')
                ->orderBy('c.id', 'DESC')
                ->setMaxResults($amount);

        return $qb->getQuery()->getResult();
    }

    public function getPopularProducts($amount) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->select('p.id, p.productTitle, p.price, p.description, sum(s.quantity) as quantitysum')
                ->from('EagleShopBundle:Sells', 's')
                ->leftJoin('EagleShopBundle:Products', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 's.productId = p.id')
                ->orderBy('quantitysum', 'DESC')
                ->groupBy('p.id')
                ->setMaxResults($amount);

        return $qb->getQuery()->getResult();
    }

    public function getfeaturedProduct($amount) {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('p.id, p.productTitle, p.price, p.description, pi.imgUrl')
                ->from('EagleShopBundle:Products', 'p')
                ->leftJoin('EagleShopBundle:ProuctImage', 'pi', \Doctrine\ORM\Query\Expr\Join::WITH, 'pi.productId = p.id')
                ->where('p.isfeatured = :isfeatured')
                ->orderBy('p.id', 'DESC')
                ->groupBy('p.id')
                ->setParameter('isfeatured', 1)
                ->setMaxResults($amount);

        return $qb->getQuery()->getResult();
    }

    public function getcategoryProduct($category, $amount, $id) {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('p.id, p.productTitle, p.price, p.description, pi.imgUrl')
                ->from('EagleShopBundle:Products', 'p')
                ->leftJoin('EagleShopBundle:ProuctImage', 'pi', \Doctrine\ORM\Query\Expr\Join::WITH, 'pi.productId = p.id')
                ->where('p.category = :category')
                ->andWhere('p.id != :id')
                ->orderBy('p.id', 'DESC')
                ->groupBy('p.id')
                ->setParameter('category', $category)
                ->setParameter('id', $id)
                ->setMaxResults($amount);

        return $qb->getQuery()->getResult();
    }

    /**
     * @Route("global/index/")
     * @Route("/")
     * @Template()
     */
    public function indexAction() {
        $latest = $this->getLatestProduct(8);
        $isfeatured = $this->getfeaturedProduct(8);

        return $this->render("EagleShopBundle:global:home.html.twig", array(
                    'image_path' => '/bundles/eagleshop/images/',
                    'latest_products' => $latest,
                    'isfeatured' => $isfeatured
        ));
    }

    /**
     * @Route("/categories")
     * @Template()
     */
    public function categoryAction() {
//        return array(
//                // ...
//        );
    }

    /**
     * @Route("/products")
     * @Template()
     */
    public function productsAction() {
        $category = 0;
        $searchText = "";
        if (isset($_GET['category']) && $_GET['category'] != "") {
            $category = $_GET['category'];
        }

        if (isset($_GET['search']) && $_GET['search'] != "") {
            $searchText = $_GET['search'];
        }

        $categories = $this->getDoctrine()
                ->getRepository('EagleShopBundle:ProductCategory')
                ->findAll();

        return $this->render("EagleShopBundle:global:products.html.twig", array(
                    'image_path' => '/bundles/eagleshop/images/',
                    'categories' => $categories,
                    'category' => $category,
                    'searchText' => $searchText
        ));
    }

    /**
     * @Route("/menuitmes")
     * @Template()
     */
    public function menuitmesAction() {
        $latestCategories = $this->getLatestCategory(5);
        $latestProducts = $this->getLatestProduct(5);
        $getPopularProducts = $this->getPopularProducts(5);

        return $this->render("EagleShopBundle:global:menuitems.html.twig", array(
                    'latestCategories' => $latestCategories,
                    'latestProducts' => $latestProducts,
                    'getPopularProducts' => $getPopularProducts,
        ));
    }

    /**
     * @Route("/products/cart")
     * @Template()
     */
    public function cartAction() {
        return $this->render("EagleShopBundle:global:cart.html.twig", array(
                    'image_path' => '/bundles/eagleshop/images/'
        ));
    }

    /**
     * @Route("/products/checkout")
     * @Template()
     */
    public function checkoutAction(Request $request) {
        if (!empty($_POST)) {
            $session = $this->getRequest()->getSession();
            $paypal = $this->get('Paypal');
            $paypal->setItems($session->get('items'));

            $paypal->getUserDetails($_POST);

//            Add sells to sells table            
            $items = $session->get('items');
            $em = $this->getDoctrine()->getManager();

            foreach ($items as $key => $value) {
                $sells = new Sells();
                $sells->setProductId($key);
                $sells->setQuantity($value['quantity']);
                $sells->setDate(date("d-m-Y"));
                $em->persist($sells);
                $em->flush();
            }


//            Destroy shopping cart
            $session->remove('items');

            echo $paypal->pay();
        }



        return $this->render("EagleShopBundle:global:checkout.html.twig", array(
                    'image_path' => '/bundles/eagleshop/images/'
        ));
    }

    /**
     * @Route("/products/checkService")
     * @Template()
     */
    public function checkServiceAction() {

        $session = $this->getRequest()->getSession();
//         print_r($session->get('items'));
//         exit();
//        
//        $items = array(
//            array(
//                'product' => 'Pro 1',
//                'quantity' => '2',
//                'price' => '20',
//            ),
//            array(
//                'product' => 'Pro 2',
//                'quantity' => '2',
//                'price' => '30',
//            )
//        );
//
        $userdetails = array(
            'address' => '57, Kadawatha Rd.',
            'city' => 'Colombo',
            'country_code' => 'LK',
            'first_name' => 'Vimuth',
            'last_name' => 'Somarathna',
            'email' => 'vi@ddfe.com',
            'zip' => '+94',
            'H_PhoneNumber' => '5435535',
        );


        $paypal = $this->get('Paypal');
        $paypal->setItems($session->get('items'));
        $paypal->getUserDetails($userdetails);
        echo $paypal->pay();

        return new Response($paypal->chkVal());
    }

    /**
     * @Route("/products/productsPaginate")
     * @Template()
     */
    public function productsPaginateAction() {
        return $this->render("EagleShopBundle:global:productsPaginate.html.twig", array(
                    'image_path' => '/bundles/eagleshop/images/'
        ));
    }

    /**
     * @Route("global/getAllproducts")
     * @Template()
     */
    public function getAllproductsAction() {

        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('p.productTitle, p.price, p.description, pi.imgUrl')
                ->from('EagleShopBundle:Products', 'p')
                ->leftJoin('EagleShopBundle:ProuctImage', 'pi', \Doctrine\ORM\Query\Expr\Join::WITH, 'pi.productId = p.id')
                ->orderBy('p.id', 'DESC')
                ->groupBy('p.id')
                ->setMaxResults(20);

        print_r($qb->getQuery()->getResult());
        exit();


//-------------------------------------------------------------------------------------------------------


        $repository = $this->getDoctrine()->getRepository('EagleShopBundle:Products');

        // find *all* products        
        $products = $repository->findAll();

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($products, 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("global/json_filterAllproducts")
     * @Template()
     */
    public function json_filterAllproductsAction() {
        //Get searched value
        $params = json_decode(file_get_contents('php://input'), true);
        $search = $params['searchedVal'];
        $category = $params['category'];
//        $search = "";
//        $category = 3;
        //Combine tables and create the query with querybuilder
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('p')
                ->from('EagleAdminBundle:Products', 'p')
                ->orderBy('p.id', 'DESC');
        if ($category != 0) {
            $qb->andWhere('p.category = :category')
                    ->setParameter('category', $category);
        }
        $qb->andWhere('p.productTitle LIKE :title')
                ->setParameter('title', "$search%");

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("global/chkjson")
     * @Template()
     */
    public function chkjsonAction(Request $request) {
        return new Response($request->request->get('pid'));
    }

    /**
     * @Route("global/json_filterstorepaginate")
     * @Template()
     */
    public function json_filterstorepaginateAction() {
        //Get start and end points       
        $params = json_decode(file_get_contents('php://input'), true);
        $search = $params['searchedVal'];
        $category = $params['category'];
        $start = $params['startpoint'];
        $perpage = $params['perpage'];
        $filter_item = $params['filter_item'];


        //Combine tables and create the query with querybuilder
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('p.description, p.productTitle, p.id, p.price, pi.imgUrl')
                ->from('EagleAdminBundle:Products', 'p')
                ->leftJoin('EagleShopBundle:ProuctImage', 'pi', \Doctrine\ORM\Query\Expr\Join::WITH, 'pi.productId = p.id')
                ->orderBy('p.id', 'DESC')
                ->groupBy('p.id');

        if ($category != 0) {
            $qb->andWhere('p.category = :category')
                    ->setParameter('category', $category);
        }

        $qb->andWhere('p.productTitle LIKE :title')
                ->setParameter('title', "$search%");

        switch ($filter_item) {
            case 1:
                $qb->orderBy('p.id', 'DESC');
                break;
            case 2:
                $qb->orderBy('p.price', 'DESC');
                break;
            case 3:
                $qb->orderBy('p.price', 'ASC');
                break;
        }

        $qb->setFirstResult($start)
                ->setMaxResults($perpage);

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("products/display/{id}")
     * @Template()
     */
    public function displayAction($id) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->select('p, partial pc.{id, catTitle}')
                ->from('EagleShopBundle:Products', 'p')
                ->leftJoin('p.category', 'pc')
                ->where('p.id = :productId')
                ->setParameter('productId', $id);


        $product = $qb->getQuery()->getOneOrNullResult();

        $category = $product->getCategory()->getId();

        $related_products = $this->getcategoryProduct($category, 8, $id);

        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProuctImage');
        $product_images = $repository->findBy(
                array(
                    'productId' => $id
                )
        );

        return $this->render("EagleShopBundle:global:product.html.twig", array(
                    'product' => $product,
                    'product_images' => $product_images,
                    'related_products' => $related_products,
                    'image_path' => '/bundles/eagleshop/images/'
        ));
    }

}
