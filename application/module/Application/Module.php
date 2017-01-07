<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Application\Model\UsuariosTable;
use Application\Model\Usuarios;

use Application\Model\UsersappTable;
use Application\Model\Usersapp;

use Application\Model\AppsTable;
use Application\Model\Apps;

class Module implements ConsoleBannerProviderInterface, ConsoleUsageProviderInterface {

    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach("dispatch", array($this, 'initServices'), 100);
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Gets our services created to instance in the controllers
     * 
     * @return Array
     */
    public function getServiceConfig() {
        
    return array(
                 'factories' => array(
                     'Application\Service\AccessList' => 'Application\Service\AccessListFactory',
                     'Application\Service\Session' => 'Application\Service\SessionFactory',
                     'Application\Model\UsuariosTable' =>  function($sm) {
                         $tableGateway = $sm->get('UsuariosTableGateway');
                         $table = new UsuariosTable($tableGateway);
                         return $table;
                     },
                     'UsuariosTableGateway' => function ($sm) {
                         $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                         $resultSetPrototype = new ResultSet();
                         $resultSetPrototype->setArrayObjectPrototype(new Usuarios());
                         return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
                     },
                     'Application\Model\UsersappTable' =>  function($sm) {
                         $tableGateway = $sm->get('UsersappTableGateway');
                         $table = new UsersappTable($tableGateway);
                         return $table;
                     },
                     'UsersappTableGateway' => function ($sm) {
                         $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                         $resultSetPrototype = new ResultSet();
                         $resultSetPrototype->setArrayObjectPrototype(new Usersapp());
                         return new TableGateway('usersapp', $dbAdapter, null, $resultSetPrototype);
                     },
                     'Application\Model\AppsTable' =>  function($sm) {
                         $tableGateway = $sm->get('AppsTableGateway');
                         $table = new AppsTable($tableGateway);
                         return $table;
                     },
                     'AppsTableGateway' => function ($sm) {
                         $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                         $resultSetPrototype = new ResultSet();
                         $resultSetPrototype->setArrayObjectPrototype(new Apps());
                         return new TableGateway('apps', $dbAdapter, null, $resultSetPrototype);
                     },
                 ),
             );
         

        
        
//        return array(
//            'factories' => array(
//                'Application\Service\Soap' => 'Application\Service\SoapFactory', //Return an stdclass that contains the soap client and the session id
//                'Application\Service\Session' => 'Application\Service\SessionFactory', //Return an instance of Container where is locating the session
//                'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory', //Return an instance of Database Adapter
//                'Application\Service\ResetPassword' => 'Application\Service\ResetPasswordFactory', //Return an instance of ResetPassowrd
//                'Application\Service\AccessList' => 'Application\Service\AccessListFactory' //Validate the current profile and redirect to ResetPassword, SetEmail or ErrorPage
//            )
//        );
        
        
    }
    
    public function getViewHelperConfig() {
    return array(
        'factories' => array(
            'config' => function($serviceManager) {
                $helper = new \Application\Helper\Config($serviceManager);
                return $helper;
            },
        )
    );
}

    /**
     * Inits configuration to work with session and apply filters to requests
     * 
     * @param MVCEvent $e
     */
    public function initServices($e) {
        $requestParams = $this->getRequestParams($e);
        $serviceManager = $e->getApplication()->getServiceManager();
        $accessList = $serviceManager->get('Application\Service\AccessList');
        $accessList->setRequestParams($requestParams);
        $accessList->check();
    }

    /**
     * Gets params of current request
     * 
     * @param MVCEvent $event
     * @return Array
     */
    private function getRequestParams($event) {
        $match = $event->getRouteMatch();
        return array(
            "response" => $event->getResponse(),
            "router" => $event->getRouter(),
            "target" => $event->getTarget(),
            "controller" => $match->getParam('controller'),
            "action" => $match->getParam('action'),
            "result" => $event->getViewModel()
        );
    }

    /**
     * Gets the banner in console
     * 
     * @param Console $console
     * @return string
     */
    public function getConsoleBanner(Console $console) {
        return 'Auth CISCO 1.0';
    }

    /**
     * Gets info to operate with the console
     * 
     * @param Console $console
     * @return Array
     */
    public function getConsoleUsage(Console $console) {
        return array(
            'List of commands to execute taks',
            'remove xlsx <token>' => 'Removes the xlsx files previusly generated by the system',
            'clean resetpwd <token>' => 'Cleans the request of reset password generated by the user',
            'notify resetpwd <token>' => 'Notifies of reset password generated by the system',
            array('<token>', 'Token validated to run task'),
        );
    }

}
