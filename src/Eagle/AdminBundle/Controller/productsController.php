<?php

namespace Eagle\AdminBundle\Controller;

//use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eagle\AdminBundle\Entity\Products;
use Eagle\AdminBundle\Entity\ProductCategory;
use Eagle\AdminBundle\Entity\ProuctImage;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;

class productsController extends Controller {


    /**
     * @Route("products/add")
     * @Template()
     */
    public function addAction() {
        $session = new Session();

        $user = $session->get('user');

        if ($user == null) {
            return $this->redirect('/login', 301);
        }
        
//        get All product categories
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProductCategory');
        $categories = $repository->findAll();

        return $this->render("EagleAdminBundle:products:add.html.twig", array(
                    'cats' => $categories
        ));
    }

    /**
     * @Route("products/json_add")
     * @Template()
     */
    public function json_addAction() {
//        Create object of product model
        $product = new Products();

//        get fromm angularjs form values via ajax
        $request = Request::createFromGlobals();
        $formvals = json_decode($request->request->get('object'));


//        add form values to product object
        foreach ($formvals as $key => $value) {
            $key = "set" . ucfirst($key);
            $product->$key($value);
        }

//        Set category
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('EagleAdminBundle:ProductCategory')->findOneById($formvals->category);
        $product->setCategory($category);


//        Set other values
        $cday = date("d-m-Y h:i:s");
        $product->setCreatedAt(new \DateTime($cday));
        $product->setUpdatedAt(new \DateTime($cday));
        $product->setStoreId(1);
        $product->setOnline(1);

        if ($product->getDescription() == Null) {
            $product->setDescription("");
        }

        if ($product->getQuantity() == Null) {
            $product->setQuantity(0);
        }

        $product->setSold(0);

//        print_r($product);
//        exit();
//        Save to DB        
        $em->persist($product);
        $em->flush();

        $i = 1;
        foreach ($request->files as $key => $uploadedFile) {
            if (null != $uploadedFile) {
                $pid = $product->getId();
                $imagename = $pid . "img" . $i++ . "." . $uploadedFile->guessExtension();

//                Rename and upload image
//                $brochuresDir = $this->container->getParameter('kernel.root_dir') . '/../src/Eagle/AdminBundle/Resources/public/images/uploads/products';
                $brochuresDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/products';
                $uploadedFile->move($brochuresDir, $imagename);

//                Add image to `prouct_image` table
                $image = New ProuctImage();
                $image->setProductId($pid);
                $image->setImgUrl($imagename);

//                Save to DB
                $em->persist($image);
                $em->flush();
            }
        }

        return new Response('true');
    }

    /**
     * @Route("products/view")
     * @Template()
     */
    public function viewAction() {

        $session = new Session();

        $user = $session->get('user');

        if ($user == null) {
            return $this->redirect('/login', 301);
        }

        //        get All product categories
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProductCategory');
        $categories = $repository->findAll();

        return $this->render("EagleAdminBundle:products:view.html.twig", array(
                    'cats' => $categories
        ));
    }

    /**
     * @Route("products/getall")
     * @Template()
     */
    public function getallAction() {
        $repository = $this->getDoctrine()->getRepository('EagleAdminBundle:Products');

        // find *all* products        
        $products = $repository->findAll();

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($products, 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("products/getcount")
     * @Template()
     */
    public function getcountAction() {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();
        $qb->select('count(p.id)')
                ->from('EagleAdminBundle:Products', 'p')
                ->orderBy('p.id', 'DESC');


        return new Response($qb->getQuery()->getSingleScalarResult());
    }

    /**
     * @Route("products/tes")
     * @Template()
     */
    public function tesAction() {
        $request = Request::createFromGlobals();
        echo $request->request->get('count');
        exit();
    }

    /**
     * @Route("products/json_paginate")
     * @Template()
     */
    public function json_paginateAction() {
        //Get start and end points       
        $params = json_decode(file_get_contents('php://input'), true);
        $start = $params['startpoint'];
        $perpage = $params['perpage'];

        //Combine tables and create the query with querybuilder
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('p')
                ->from('EagleAdminBundle:Products', 'p')
                ->orderBy('p.id', 'DESC')
                ->setFirstResult($start)
                ->setMaxResults($perpage);

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("products/json_searchPaginate")
     * @Template()
     */
    public function json_searchPaginateAction() {
        //Get start and end points       
        $params = json_decode(file_get_contents('php://input'), true);
        $search = $params['searchedVal'];
        $start = $params['startpoint'];
        $perpage = $params['perpage'];

        //Combine tables and create the query with querybuilder
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('p')
                ->from('EagleAdminBundle:Products', 'p')
                ->orderBy('p.id', 'DESC')
                ->where('p.productTitle LIKE :title')
                ->setParameter('title', "$search%")
                ->setFirstResult($start)
                ->setMaxResults($perpage);

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("products/jason_searchAll")
     * @Template()
     */
    public function jason_searchAllAction() {
        //Get searched value
        $params = json_decode(file_get_contents('php://input'), true);
        $search = $params['searchedVal'];


        //Combine tables and create the query with querybuilder
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('p')
                ->from('EagleAdminBundle:Products', 'p')
                ->orderBy('p.id', 'DESC')
                ->where('p.productTitle LIKE :title')
                ->setParameter('title', "$search%");

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("products/searchCount")
     * @Template()
     */
    public function searchCountAction() {
        //Get searched value
        $params = json_decode(file_get_contents('php://input'), true);
        $search = $params['searchedVal'];


        //Combine tables and create the query with querybuilder
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('count(p.id)')
                ->from('EagleAdminBundle:Products', 'p')
                ->orderBy('p.id', 'DESC')
                ->where('p.productTitle LIKE :title')
                ->setParameter('title', "$search%");

        return new Response($qb->getQuery()->getSingleScalarResult());
    }

    /**
     * @Route("products/multiDelete")
     * @Template()
     */
    public function multiDeleteAction() {
        //Get ids for deletion
//        $params = json_decode(file_get_contents('php://input'), true);
//        $ids = $params['searchedVal'];
        $params = json_decode(file_get_contents('php://input'), true);
        $ids = $params['ids'];

        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:Products');

        foreach ($ids as $id) {
            $product = $repository->find($id);
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        $allproducts = $this->getallAction();
        return $allproducts;
        exit();
    }

    /**
     * @Route("products/multiUpdate")
     * @Template()
     */
    public function multiUpdateAction() {
        //Get ids for Update
        $params = json_decode(file_get_contents('php://input'), true);
        $ids = $params['ids'];
        $updates = $params['valeusToUpdate'];

//        Old and long code
//        $em = $this->getDoctrine()->getManager();
//
//        $repository = $this->getDoctrine()
//                ->getRepository('EagleAdminBundle:Products');
//
//        foreach ($ids as $id) {
//            $product = $repository->find($id);
//
////          Check category exists in $updates array
//            if (array_key_exists('category', $updates)) {
//                $category = $em->getRepository('EagleAdminBundle:ProductCategory')->findOneById($updates['category']);
//            }
//
//            foreach ($updates as $key => $value) {
//                if ($key == "category") {
//                    $key = "set" . ucfirst($key);
//                    $product->$key($category);
//                } else {
//                    $key = "set" . ucfirst($key);
//                    $product->$key($value);
//                }
//            }
//
//
//            $em->persist($product);
//            $em->flush();
//        }
//        New quick code
        if (!empty($updates)) {
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $qb->update('Eagle\AdminBundle\Entity\Products', 'p');
            $qb->where($qb->expr()->in('p.id', '?5'));
            $qb->setParameter(5, $ids);

            $i = 1;
            foreach ($updates as $key => $value) {
                $qb->set('p.' . $key, '?' . $i);
                $qb->setParameter($i, $value);
                $i++;
            }

            $query = $qb->getQuery();
            $query->getSingleScalarResult();
        }

        $allproducts = $this->getallAction();
        return $allproducts;
        exit();
    }

    /**
     * @Route("products/multiAddItems")
     * @Template()
     */
    public function multiAddItemsAction() {
        //Get ids for Update
        $params = json_decode(file_get_contents('php://input'), true);
        $ids = $params['ids'];
        $addItems = $params['addItems'];

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->update('Eagle\AdminBundle\Entity\Products', 'p');
        $qb->where($qb->expr()->in('p.id', '?5'));
        $qb->setParameter(5, $ids);

        $qb->set('p.quantity', 'p.quantity + ?6');
        $qb->setParameter(6, $addItems);

        $query = $qb->getQuery();
        $query->getSingleScalarResult();

        $allproducts = $this->getallAction();
        return $allproducts;
        exit();
    }

    /**
     * @Route("products/delete")
     * @Template()
     */
    public function deleteAction() {
//        Get Id for deletion
        $params = json_decode(file_get_contents('php://input'), true);
        $id = $params['id'];

        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:Products');

        $cat = $repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($cat);
        $em->flush();

        $allcats = $this->getallAction();
        return $allcats;
        exit();
    }

    /**
     * @Route("products/service")
     * @Template()
     */
    public function serviceAction() {
        $updater = $this->get('category_update');
//        $updater->setId(1);


        $updater->setFields($feilds);
//        echo $updater->execute();

        print_r($updater->execute());
        exit();
    }

    /**
     * @Route("products/update/{id}")
     * @Template()
     */
    public function updateAction($id) {
        $session = new Session();

        $user = $session->get('user');

        if ($user == null) {
            return $this->redirect('/login', 301);
        }
        
        //        Get product by ID
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:Products');
        $product = $repository->find($id);


        //        Get product images
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProuctImage');
        $product_images = $repository->findBy(
                array(
                    'productId' => $id
                )
        );

//        $str = "150img3.png";
//        echo explode('.',explode("img",$str)[1])[0];
//        exit();
//        print_r($product_images);
//        exit();
        //        get All product categories
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProductCategory');
        $categories = $repository->findAll();

        return $this->render("EagleAdminBundle:products:update.html.twig", array(
                    'cats' => $categories,
                    'product' => $product,
                    'product_images' => $product_images
        ));
    }

    /**
     * @Route("products/json_update")
     * @Template()
     */
    public function json_updateAction() {

//        get fromm angularjs form values via ajax
        $request = Request::createFromGlobals();
        $formvals = json_decode($request->request->get('object'));


        //        Get product by ID
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:Products');
        $product = $repository->find($formvals->id);


//        add form values to product object
        foreach ($formvals as $key => $value) {
            if ($key != 'id') {
                $key = "set" . ucfirst($key);
                $product->$key($value);
            }
        }

//        Set category
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('EagleAdminBundle:ProductCategory')->findOneById($formvals->category);
        $product->setCategory($category);


//        Set other values
        $cday = date("d-m-Y h:i:s");
//        $product->setCreatedAt(new \DateTime($cday));
        $product->setUpdatedAt(new \DateTime($cday));
        $product->setStoreId(1);
        $product->setOnline(1);

        if ($product->getDescription() == Null) {
            $product->setDescription("");
        }

        if ($product->getQuantity() == Null) {
            $product->setQuantity(0);
        }
//        print_r($product);
//        exit();
//        Save to DB        
        $em->persist($product);
        $em->flush();

        $i = 1;
        foreach ($request->files as $key => $uploadedFile) {
            if (null != $uploadedFile) {
                $pid = $product->getId();
                $imagename_noExtention = $pid . "img" . $key;
                $imagename = $pid . "img" . $key . "." . $uploadedFile->guessExtension();

//                Find and delete image if existing
                $em = $this->getDoctrine()->getManager();
                $qb = $em->createQueryBuilder();
                $qb->delete('Eagle\AdminBundle\Entity\ProuctImage', 'pi');
                $qb->andWhere('pi.imgUrl LIKE ?1');
                $qb->setParameter(1, $imagename_noExtention . '%');
                $query = $qb->getQuery();
                $query->getSingleScalarResult();


//                Rename and upload image
                $brochuresDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/products';
                $uploadedFile->move($brochuresDir, $imagename);

//                Add image to `prouct_image` table
                $image = New ProuctImage();
                $image->setProductId($pid);
                $image->setImgUrl($imagename);

//                Save to DB
                $em->persist($image);
                $em->flush();
            }
        }

        return new Response('true');
    }

}
