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
        
        return $this->render("EagleAdminBundle:dashboard:index.html.twig");
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
