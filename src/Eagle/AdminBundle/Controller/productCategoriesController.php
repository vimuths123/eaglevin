<?php

namespace Eagle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eagle\AdminBundle\Entity\ProductCategory;
use Eagle\AdminBundle\Entity\RelatedCategories;
use Eagle\AdminBundle\Entity\CategoryImage;

class productCategoriesController extends Controller {

    /**
     * @Route("productCategories/add")
     * @Template()
     */
    public function addAction() {       
//        get All product categories
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProductCategory');
        $categories = $repository->findAll();

        return $this->render("EagleAdminBundle:productCategories:add.html.twig", array(
                    'cats' => $categories
        ));
    }

    /**
     * @Route("productCategories/view")
     * @Template()
     */
    public function viewAction() {               
        return $this->render("EagleAdminBundle:productCategories:view.html.twig");
    }

    /**
     * @Route("productCategories/jsonCat")
     * @Template()
     */
    public function jsonCatAction() {
        $request = Request::createFromGlobals();
        $cat = $request->query->get('q');

        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProductCategory');

        $query = $repository->createQueryBuilder('p')
                ->select('p.id,p.catTitle as name')
                ->where('p.catTitle LIKE :title')
                ->setParameter('title', '%' . $cat . '%')
                ->getQuery();

        print_r(json_encode($query->getResult()));
        exit();
    }

    /**
     * @Route("productCategories/json_add")
     * @Template()
     */
    public function json_addAction() {
//        Create object of product model
        $cat = new ProductCategory();

//        get from angularjs form values via ajax
        $request = Request::createFromGlobals();
        $formvals = json_decode($request->request->get('object'));


//        add form values to product object
        foreach ($formvals as $key => $value) {
            $key = "set" . ucfirst($key);
            $cat->$key($value);
        }

//        Set other values
        $cday = date("d-m-Y h:i:s");
        $cat->setCreatedAt(new \DateTime($cday));
        $cat->setUpdatedAt(new \DateTime($cday));
        $cat->setStoreId(1);
        $cat->setUrl("");

        if ($cat->getCatDesc() == Null) {
            $cat->setCatDesc("");
        }

//        Save categories to DB
        $em = $this->getDoctrine()->getManager();
        $em->persist($cat);
        $em->flush();

//        Save related categories to DB
        $related = new RelatedCategories();
        $related->setCategoryId($cat->getId()); //get last inserted id
        $related->setRelatedCategories($request->request->get('related'));
        $em->persist($related);
        $em->flush();

        $i = 1;
        foreach ($request->files as $key => $uploadedFile) {
            if (null != $uploadedFile) {
                $cid = $cat->getId();
                $imagename = $cid . "img" . $i++ . "." . $uploadedFile->guessExtension();

//                Rename and upload image
                $brochuresDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/categories';
                $uploadedFile->move($brochuresDir, $imagename);

//                Add image to `prouct_image` table
                $image = new CategoryImage();
                $image->setCategoryId($cid);
                $image->setImageName($imagename);

//                Save to DB
                $em->persist($image);
                $em->flush();
            }
        }



        return new Response('true');
    }

    /**
     * @Route("productCategories/getAll")
     * @Template()
     */
    public function getAllAction() {
//        $repository = $this->getDoctrine()->getRepository('EagleAdminBundle:ProductCategory');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

//SELECT pc.*, COUNT(p.id) AS products
//FROM `product_category` pc
//LEFT JOIN `products` p
//ON p.category = pc.id
//GROUP BY pc.id
        // Get *all* categories with products       
        $qb->select('pc')
                ->addSelect('COUNT(p.id) AS products')
                ->from('EagleAdminBundle:ProductCategory', 'pc')
                ->leftJoin('EagleAdminBundle:Products', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.category = pc.id')
                ->orderBy('pc.id', 'DESC')
                ->groupBy('pc.id');


        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsoncategories = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        return new Response($jsoncategories);
    }

    /**
     * @Route("productCategories/json_paginate")
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

        $qb->select('pc')
                ->addSelect('COUNT(p.id) AS products')
                ->from('EagleAdminBundle:ProductCategory', 'pc')
                ->leftJoin('EagleAdminBundle:Products', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.category = pc.id')
                ->orderBy('pc.id', 'DESC')
                ->groupBy('pc.id')
                ->setFirstResult($start)
                ->setMaxResults($perpage);

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("productCategories/jason_searchAll")
     * @Template()
     */
    public function jason_searchAllAction() {
        //Get start and end points       
        $params = json_decode(file_get_contents('php://input'), true);
        $search = $params['searchedVal'];


        //Combine tables and create the query with querybuilder
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();

        $qb->select('pc')
                ->addSelect('COUNT(p.id) AS products')
                ->from('EagleAdminBundle:ProductCategory', 'pc')
                ->leftJoin('EagleAdminBundle:Products', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.category = pc.id')
                ->where('pc.catTitle LIKE :title')
                ->setParameter('title', "$search%")
                ->groupBy('pc.id')
                ->orderBy('pc.id', 'DESC');


        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("productCategories/json_searchPaginate")
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

        $qb->select('pc')
                ->addSelect('COUNT(p.id) AS products')
                ->from('EagleAdminBundle:ProductCategory', 'pc')
                ->leftJoin('EagleAdminBundle:Products', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.category = pc.id')
                ->where('pc.catTitle LIKE :title')
                ->setParameter('title', "$search%")
                ->groupBy('pc.id')
                ->orderBy('pc.id', 'DESC')
                ->setFirstResult($start)
                ->setMaxResults($perpage);

        //convert to json using "JMSSerializerBundle"
        $serializer = $this->container->get('serializer');
        $jsonproducts = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        return new Response($jsonproducts);
    }

    /**
     * @Route("productCategories/multiDelete")
     * @Template()
     */
    public function multiDeleteAction() {
        //Get ids for deletion
        $params = json_decode(file_get_contents('php://input'), true);
        $ids = $params['ids'];

        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->delete('EagleAdminBundle:Products', 'p')
                ->where($qb->expr()->in('p.category', '?1'))
                ->setParameter(1, $ids);

        $qb->getQuery()->execute();

        $qb->delete('EagleAdminBundle:ProductCategory', 'pc')
                ->where($qb->expr()->in('pc.id', '?1'))
                ->setParameter(1, $ids);

        $qb->getQuery()->execute();

        $allproducts = $this->getallAction();
        return $allproducts;
        exit();
    }

    /**
     * @Route("productCategories/delete")
     * @Template()
     */
    public function deleteAction() {
        //Get id for deletion
        $params = json_decode(file_get_contents('php://input'), true);
        $id = $params['id'];

        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->delete('EagleAdminBundle:Products', 'p')
                ->where('p.category = ?1')
                ->setParameter(1, $id);
        $qb->getQuery()->execute();

        $qb->delete('EagleAdminBundle:ProductCategory', 'pc')
                ->where('pc.id = ?1')
                ->setParameter(1, $id);
        $qb->getQuery()->execute();

        $allproducts = $this->getallAction();
        return $allproducts;
        exit();
    }

    /**
     * @Route("productCategories/update/{id}")
     * @Template()
     */
    public function updateAction($id) {
        //        get All product categories
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProductCategory');
        $categories = $repository->findAll();

        //        Get the category by ID
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProductCategory');
        $category = $repository->find($id);


        //        Get category images
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:CategoryImage');
        $cat_img = $repository->findBy(
                array(
                    'categoryId' => $id
                )
        );

        //        Get related categories
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:RelatedCategories');
        $related_cats = $repository->findBy(
                array(
                    'categoryId' => $id
                )
        );


        if (!empty($related_cats)) {
            $related_ids = explode(',', $related_cats[0]->getRelatedCategories());

            $em = $this->container->get('doctrine.orm.entity_manager');
            $qb = $em->createQueryBuilder();

            $qb->select('pc.id AS id')
                    ->addSelect('pc.catTitle AS name')
                    ->from('EagleAdminBundle:ProductCategory', 'pc')
                    ->where($qb->expr()->in('pc.id', '?1'))
                    ->setParameter(1, $related_ids);

            //convert to json using "JMSSerializerBundle"
            $serializer = $this->container->get('serializer');
            $jsoncategories = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        } else {
            $jsoncategories = "[]";
        }

        return $this->render("EagleAdminBundle:productCategories:update.html.twig", array(
                    'cats' => $categories,
                    'category' => $category,
                    'related_cats' => $jsoncategories,
                    'cat_img' => $cat_img
        ));
    }

    /**
     * @Route("productCategories/json_update")
     * @Template()
     */
    public function json_updateAction() {
//        Create object of product model
//        $cat = new ProductCategory();
//        get from angularjs form values via ajax
        $request = Request::createFromGlobals();
        $formvals = json_decode($request->request->get('object'));


//        Get category by ID
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:ProductCategory');
        $cat = $repository->find($formvals->id);

//        add form values to product object
        foreach ($formvals as $key => $value) {
            if ($key != 'id') {
                $key = "set" . ucfirst($key);
                $cat->$key($value);
            }
        }

//        Set other values
        $cday = date("d-m-Y h:i:s");
//        $cat->setCreatedAt(new \DateTime($cday));
        $cat->setUpdatedAt(new \DateTime($cday));
        $cat->setStoreId(1);
        $cat->setUrl("");

        if ($cat->getCatDesc() == Null) {
            $cat->setCatDesc("");
        }

//        Save categories to DB
        $em = $this->getDoctrine()->getManager();
        $em->persist($cat);
        $em->flush();

//        update related categories to DB
        $related = new RelatedCategories();
        $related->setCategoryId($cat->getId()); //get last inserted id
        $related->setRelatedCategories($request->request->get('related'));
        $em->persist($related);
        $em->flush();
        
        //        Get related categories
        $repository = $this->getDoctrine()
                ->getRepository('EagleAdminBundle:RelatedCategories');
        $related_cats = $repository->findBy(
                array(
                    'categoryId' => $cat->getId()
                )
        );
        $related_cats->setRelatedCategories($request->request->get('related'));
        $em->persist($related_cats);
        $em->flush();
        

        $i = 1;
        foreach ($request->files as $key => $uploadedFile) {
            if (null != $uploadedFile) {
                $cid = $cat->getId();
                $imagename_noExtention = $cid . "img" . $key;
                $imagename = $cid . "img" . $i++ . "." . $uploadedFile->guessExtension();

//                Find and delete image if existing
                $em = $this->getDoctrine()->getManager();
                $qb = $em->createQueryBuilder();
                $qb->delete('Eagle\AdminBundle\Entity\CategoryImage', 'ci');
                $qb->andWhere('ci.imageName LIKE ?1');
                $qb->setParameter(1, $imagename_noExtention . '%');
                $query = $qb->getQuery();
                $query->getSingleScalarResult();

//                Rename and upload image
                $brochuresDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/categories';
                $uploadedFile->move($brochuresDir, $imagename);

//                Add image to `prouct_image` table
                $image = new CategoryImage();
                $image->setCategoryId($cid);
                $image->setImageName($imagename);

//                Save to DB
                $em->persist($image);
                $em->flush();
            }
        }



        return new Response('true');
    }

}
