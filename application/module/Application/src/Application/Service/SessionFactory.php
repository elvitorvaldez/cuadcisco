<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;

/**
 * Description of SessionFactory
 *
 * @author Roman
 */
class SessionFactory implements FactoryInterface {

    /**
     * Initializes the session manager and containser
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return Container
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {
        ini_set("session.gc_divisor", 1);
        ini_set("session.gc_maxlifetime", 1800);
        ini_set("session.cookie_lifetime", 1800);
        $config = new SessionConfig();
        $config->setOptions(array(
            'name' => 'PSI',
            'use_cookies' => true,
            'cookie_httponly' => true,
            'gc_divisor' => 1,
            'hash_function' => 'sha512'
        ));
        $manager = new SessionManager($config);
        return new Container('auth', $manager);
    }

}
