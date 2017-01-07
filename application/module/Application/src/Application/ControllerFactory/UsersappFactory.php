<?php   
namespace Application\ControllerFactory;
use \Zend\ServiceManager\FactoryInterface;
use \Zend\ServiceManager\ServiceLocatorInterface;
use Application\Controller\UsersappController as UsersappController;

class UsersappFactory implements FactoryInterface
{
    protected $adapter;
    public function createService(ServiceLocatorInterface $serviceLocator) { 
       //if (!$this->adapter) {
       $sm   = $serviceLocator->getServiceLocator();  
       
       $UsersappTable= $sm->get('Application\Model\UsersappTable');  
           
      
       $UsersappController = new UsersappController($UsersappTable);   
       
        return $UsersappController;
       // }
    }
}