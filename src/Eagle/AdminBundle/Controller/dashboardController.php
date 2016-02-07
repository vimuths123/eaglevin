<?php

namespace Eagle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Eagle\AdminBundle\Entity\Task;
use Symfony\Component\Form\FormError;

class dashboardController extends Controller {

    /**
     * @Route("dashboard/index")
     * @Template()
     */
    public function indexAction() {
        $session = $this->getRequest()->getSession();
        $user = $session->get('user');

        if ($user === NULL) {
            return $this->redirect('/login', 301);
        }        

        return $this->render("EagleAdminBundle:dashboard:index.html.twig", array(
                    'monthChart' => $this->monthChart(),
                    'summery' => $this->summery()
        ));
    }

    public function summery() {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->select('count(p.id)')
                ->from('EagleAdminBundle:Products', 'p');

        $allProducts = $qb->getQuery()->getResult()[0][1];

        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->select('count(c.id)')
                ->from('EagleAdminBundle:ProductCategory', 'c');

        $allCategories = $qb->getQuery()->getResult()[0][1];

        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->select('count(p.id)')
                ->from('EagleAdminBundle:Products', 'p')
                ->andWhere('p.createdAt LIKE :date')
                ->setParameter('date', date('Y-m-d')."%");

        $prsThsMnth = $qb->getQuery()->getResult()[0][1];
        
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->select('count(pc.id)')
                ->from('EagleAdminBundle:ProductCategory', 'pc')
                ->andWhere('pc.createdAt LIKE :date')
                ->setParameter('date', date('Y-m-d')."%");

        $catsThsMnth = $qb->getQuery()->getResult()[0][1];
        
        return array(
            'allProducts' => $allProducts,
            'allCategories' => $allCategories,
            'prsThsMnth' => $prsThsMnth,
            'catsThsMnth' => $catsThsMnth,
        );
    }

    public function monthChart() {

//        Get chart values
        $prods = array();
        $monthTotal = 0;
        $sellsTotal = 0;
        $chartval = '';

        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();

        $qb->select('s.date, sum(s.quantity)')
                ->from('EagleAdminBundle:Sells', 's')
                ->groupBy('s.date');

        $sold = $qb->getQuery()->getResult();

        if ($sold != null) {
            foreach ($sold as $value) {
                $sellsTotal += $value['1'];
                if (date('m') == substr($value['date'], 3, 2)) {
                    $prods[substr($value['date'], 0, 2)] = $value['1'];
                    $monthTotal += $value['1'];
                }
            }
        }

        $chartval = '[';
        foreach (range(1, date('j')) as $number) {
//            echo sprintf("%02d", $number);
            if (array_key_exists(sprintf("%02d", $number), $prods)) {
                $chartval .= '[' . sprintf("%02d", $number) . ',' . $prods[sprintf("%02d", $number)] . ']';
            } else {
                $chartval .= '[' . sprintf("%02d", $number) . ', 0' . ']';
            }

            if ($number != 31) {
                $chartval .= ',';
            }
        }
        $chartval = $chartval . ']';
//        /Get chart values



        $monthChart = array(
            'chartval' => $chartval,
            'monthTotal' => $monthTotal,
            'sellsTotal' => $sellsTotal
        );

        return $monthChart;
    }

    /**
     * @Route("/login")
     * @Template()
     */
    public function loginAction(Request $request) {
        // create a task and give it some dummy data for this example
        $task = new Task();
        $form = $this->createFormBuilder($task)
                ->add('username', 'text')
                ->add('password', 'password')
                ->add('save', 'submit', array('label' => 'Login'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//            Hardcoded username and password
            $username = 'admin';
            $password = 'admin123';


            if ($task->getUsername() == $username) {
                if ($task->getPassword() == $password) {

                    $session = $this->getRequest()->getSession();
                    $user = array(
                        'username' => $username,
                        'password' => $password
                    );
                    $session->set('user', $user);


                    return $this->redirect('dashboard/index', 301);
                } else {
                    $error = new FormError("You'r password is wrong");
                    $form->get('password')->addError($error);
                }
            } else {
                $error = new FormError("The user doesn't exist");
                $form->get('username')->addError($error);
            }


//            return $this->redirectToRoute('task_success');
        }

        return $this->render("EagleAdminBundle:dashboard:login.html.twig", array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/logout")
     * @Template()
     */
    public function logoutAction() {
        $session = $this->getRequest()->getSession();
        $session->remove('user');

        return $this->redirect('/login', 301);
    }

}
