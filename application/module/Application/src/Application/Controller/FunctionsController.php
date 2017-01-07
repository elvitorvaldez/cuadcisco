<?php

namespace Application\Controller;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use Application\Model\Session;

use Zend\Session\Container;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;


use Zend\Mvc\Controller\AbstractController;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;




/**
 * Description of FunctionsController
 *
 * @author Vvaldez
 */
class FunctionsController extends AbstractActionController
{
    
    public function gotoappAction()
    {
        $auth        = new Container("auth");
        
    }
    
    
    public function gotodashAction()
    {
        $auth        = new Container("auth");
    }
    
    
}
