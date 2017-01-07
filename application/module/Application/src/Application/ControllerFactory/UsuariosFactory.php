<?php   
namespace Application\ControllerFactory;
use \Zend\ServiceManager\FactoryInterface;
use \Zend\ServiceManager\ServiceLocatorInterface;
use Application\Controller\UsuariosController as UsuariosController;

class UsuariosFactory implements FactoryInterface
{
    protected $adapter;
    public function createService(ServiceLocatorInterface $serviceLocator) { 
       //if (!$this->adapter) {
       $sm   = $serviceLocator->getServiceLocator();  
       
       $UsuariosTable= $sm->get('Application\Model\UsuariosTable');  
           
      
       $UsuariosController = new UsuariosController($UsuariosTable);   
       
        return $UsuariosController;
       // }
    }
}