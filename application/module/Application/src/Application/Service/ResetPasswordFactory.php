<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Model\ResetPassword;

/**
 * Description of SessionFactory
 *
 * @author Roman
 */
class ResetPasswordFactory implements FactoryInterface {

    /**
     * Initializes the reset password model
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return ResetPassword
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {
        $resetPassword = new ResetPassword();
        $db = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $config = $serviceLocator->get('Config');
        $resetPassword->setDriver($db);
        $resetPassword->setConfig($config);
        return $resetPassword;
    }

}
