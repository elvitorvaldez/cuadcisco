<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractController;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

use Application\Util\Logger;

/**
 * Basic action controller
 */
abstract class BaseControllerAction extends AbstractController {

    //Params for interact with other controllers
    protected $auth = null;
    protected $soap = null;
    protected $db = null;
    protected $dm = null;
    protected $server = null;

    /**
     * {@inheritDoc}
     */
    protected $eventIdentifier = __CLASS__;

    /**
     * Default action if none provided
     *
     * @return array
     */
    public function indexAction() {        
        return new ViewModel([
            'content' => 'Placeholder page'
        ]);
    }

    /**
     * Action called if matched action does not exist
     *
     * @return array
     */
    public function notFoundAction() {
        $response = $this->response;
        $event = $this->getEvent();
        $routeMatch = $event->getRouteMatch();
        $routeMatch->setParam('action', 'not-found');
        
        if ($response instanceof HttpResponse) {
            return $this->createHttpNotFoundModel($response);
        }
        return $this->createConsoleNotFoundModel($response);
    }

    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException
     */
    public function onDispatch(MvcEvent $e) {
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            /**
             * @todo Determine requirements for when route match is missing.
             *       Potentially allow pulling directly from request metadata?
             */
            throw new Exception\DomainException('Missing route matches; unsure how to retrieve action');
        }

        $action = $routeMatch->getParam('action', 'not-found');
        $method = static::getMethodFromAction($action);

        if (!method_exists($this, $method)) {
            $method = 'notFoundAction';
        }

        $actionResponse = $this->$method();

        $e->setResult($actionResponse);

        return $actionResponse;
    }

    /**
     * @deprecated please use the {@see \Zend\Mvc\Controller\Plugin\CreateHttpNotFoundModel} plugin instead: this
     *             method will be removed in release 2.5 or later.
     *
     * {@inheritDoc}
     */
    protected function createHttpNotFoundModel(HttpResponse $response) {
        return $this->__call('createHttpNotFoundModel', [$response]);
    }

    /**
     * @deprecated please use the {@see \Zend\Mvc\Controller\Plugin\CreateConsoleNotFoundModel} plugin instead: this
     *             method will be removed in release 2.5 or later.
     *
     * {@inheritDoc}
     */
    protected function createConsoleNotFoundModel($response) {
        return $this->__call('createConsoleNotFoundModel', [$response]);
    }

    /**
     * Get Services of Module.php (Session Values)
     * 
     * @return Object Instance of Container (auth)
     */
    protected function getSessionService() {
        if ($this->auth == null) {
            $sm = $this->getServiceLocator();
            $this->auth = new Container('auth');
        }
        return $this->auth;
    }

    /**
     * Get Services of Module.php (Soap Client and Session ID)
     * 
     * @return StdClass
     */
    protected function getSoapConnectionService() {
        if ($this->soap == null) {
            $sm = $this->getServiceLocator();
            $this->soap = $sm->get('Application\Service\Soap');
        }
        return $this->soap;
    }

    /**
     * Get Services of Module.php (Database - MySQL)
     * 
     * @return Zend\Db\Adapter\Adapter
     */
    protected function getDatabaseService() {
        if ($this->db === null) {
            $this->db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        }
        return $this->db;
    }
    
    /**
     * Get Services of Module.php (Mongo - Doctrine)
     * 
     * @return Doctrine\ODM\MongoDB\DocumentManager
     */
//    protected function getMongoService() {
//        if ($this->dm === null) {
//            $this->dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
//        }
//        return $this->dm;
//    }
    
    /**
     * Gets the url of web service
     * 
     * @return String
     */
    protected function getWebService(){
        if ($this->server === null) {
            $serviceLocator = $this->getServiceLocator();
            $config = $serviceLocator->get('Config');
            $config = $config ['server'];
            $this->server = $config["url"] . $config["wsdl"];
        }
        return $this->server;
    }
    
    /**
     * Generates a log system
     * 
     * @param String $route
     * @param String $event
     * @param String $key
     * @param String|Null $userId
     * @param String|Null $comment
     */
    protected function generateLog($route, $event, $key, $userId=null, $comment=null){
        $logger = Logger::getInstance();
        $logger->setRoute($route);
        $logger->setMessage($event, $key, $userId);
        if($comment!==null){
            $logger->setComment($comment);
        }
        //habrá que implementar un log con mysql
        //$logger->createLog($this->getMongoService());
    }

}
