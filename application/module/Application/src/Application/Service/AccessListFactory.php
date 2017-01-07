<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Model\AccessList;

/**
 * Description of AccessListFactory
 *
 * @author Roman
 */
class AccessListFactory implements FactoryInterface {

    /**
     * Connects and Initializes the communication between Redis and PHP
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return AccessList Instance of AccessList
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {

        $config = $serviceLocator->get("Config");
        $sessionAuth = $serviceLocator->get('Application\Service\Session');

        $accessList = new AccessList($sessionAuth);
        $accessList->setRoutes($config);

        return $accessList;
    }

}
