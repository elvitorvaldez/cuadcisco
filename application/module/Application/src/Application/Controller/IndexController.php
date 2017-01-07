<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class IndexController extends BaseControllerAction {

    protected $userTable;
    protected $userappTable;
    protected $appsTable;
    
    public function getUserTable()
    {
        
        if (!$this->userTable) {
            
            $sm = $this->getServiceLocator();
            
            $this->userTable = $sm->get('Application\Model\UsuariosTable');
            
        }
        return $this->userTable;
    }
    
    
        public function getUserappTable()
    {
        
        if (!$this->userappTable) {
            
            $sm = $this->getServiceLocator();
            $this->userappTable = $sm->get('Application\Model\UsersappTable');
        }
         return $this->userappTable;
    }
    
    
    
        public function getAppsTable()
    {
        
        if (!$this->appsTable) {
            
            $sm = $this->getServiceLocator();
            
            $this->appsTable = $sm->get('Application\Model\AppsTable');
            
        }
        return $this->appsTable;
    }
    
    
    public function indexAction() {
        $auth=$this->getSessionService();        
        $view = new ViewModel();
                  
        // $this->layout("layout/layout_tables.phtml");
        
        
                $listApps=$this->getUserappTable()->getAppsByUser($auth->username);               
                //print_r($listApps);
                return new ViewModel(array(
                    'auth' => $this->auth->getArrayCopy(),
                    'lista' => $listApps,
                ));
    }

    /**
     * Default view of page error
     * 
     * @return ViewModel
     */
    public function errorAction() {
        $view = new ViewModel();
        $view->setTemplate('error/404.phtml'); // path to phtml file under view folder
        $layout = $this->layout();
        $layout->setTemplate('error/404');
        return $view;
    }

}
