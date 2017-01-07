<?php   
namespace Application\ControllerFactory;
use \Zend\ServiceManager\FactoryInterface;
use \Zend\ServiceManager\ServiceLocatorInterface;
use Application\Controller\AppsController as AppsController;

class AppsFactory implements FactoryInterface
{
    protected $adapter;
    public function createService(ServiceLocatorInterface $serviceLocator) { 
       //if (!$this->adapter) {
       $sm   = $serviceLocator->getServiceLocator();  
       
       $AppsTable= $sm->get('Application\Model\AppsTable');  
           
      
       $AppsController = new UsersappController($AppsTable);   
       
        return $AppsController;
       // }
    }
}