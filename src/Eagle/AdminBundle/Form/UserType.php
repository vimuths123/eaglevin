<?php

namespace Eagle\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('username')
//                ->add('password')
                ->add('password', 'repeated', array(
                    'type' => 'password',
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat Password'),
                ))
                ->add('is_admin', 'choice', array(
                    'choices' => array(
                        '0' => 'Normal User',
                        '1' => 'Admin',),))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Eagle\AdminBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'eagle_adminbundle_user';
    }

}
