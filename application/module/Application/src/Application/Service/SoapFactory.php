<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\OCISchema\Authentication;
use Application\Model\Session;

/**
 * Description of SoapFactory
 *
 * @author Roman
 */
class SoapFactory implements FactoryInterface {

    /**
     * Connects and Initializes the communication between XSP and PHP
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return Connection Instance of Connection
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {
        $soap = null;
        $auth = $serviceLocator->get('Application\Service\Session');
        if ($auth && $auth->userId != null) {
            $config = $serviceLocator->get('Config');
            $authentication = new Authentication($config['server']["url"] . $config['server']["wsdl"]);
            $response = $authentication->login($auth->userId, $auth->password);
            $error = $authentication->getErrorResponse();
            if ($error !== "" && $response === null) {
                $this->redirectToLogin($serviceLocator, $auth);
            } else {
                $soap = new \stdClass();
                $soap->client = $authentication->getSoapClient();
                $soap->id = $authentication->getSessionId();
            }
        }
        return $soap;
    }

    /**
     * Redirects to Login
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @param Application\Service\Session $auth
     */
    private function redirectToLogin($serviceLocator, $auth) {
        $db = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $request = $serviceLocator->get('Request');
        $response = $serviceLocator->get('Response');
        $router = $serviceLocator->get('Router');
        $session = new Session($auth, $db);
        $session->remove();
        $url = $router->assemble(array('action' => "login"), array('name' => "login")) . "?credentials=expired";
        $app = null;
        if (isset($_GET["app"])) {
            $app = (isset($_GET["app"]) ? $_GET["app"] : null);
        }
        if ($app !== null) {
            if ($app === "gestion_cuad" || $app === "directorio" || $app === "reportes") {
                $url .= "&app=" . $app;
            }
        }
        if (!empty($request->getServer()->HTTP_X_REQUESTED_WITH) &&
                strtolower($request->getServer()->HTTP_X_REQUESTED_WITH) == 'xmlhttprequest'
        ) {
            if (isset($_POST["app"])) {
                $app = (isset($_POST["app"]) ? $_POST["app"] : null);
            }
            if ($app !== null) {
                if ($app === "gestion_cuad" || $app === "directorio" || $app === "reportes") {
                    $url .= "&app=" . $app;
                }
            }
            \header('Content-Type: application/json');
            die(\json_encode(array("redirect" => $url)));
        }
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(403);
        $response->sendHeaders();
        exit();
    }

}
