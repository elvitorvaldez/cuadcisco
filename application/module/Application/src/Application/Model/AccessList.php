<?php

namespace Application\Model;
use Zend\Session\Container;
/**
 * Description of AccessList
 *
 * @author Roman
 */
class AccessList {

    /**
     * Session Data of current user
     * 
     * @var Zend\Session\Container 
     */
    private $auth;

    /**
     * Request Data
     * 
     * @var Array 
     */
    private $requestParams;

    /**
     * Object of all routes by section (whiteList, requiresServiceProvider,
      userModules, requiresPermits, blockModifications)
     * 
     * @var Object
     */
    private $routes;

    /**
     * Constructor of AccessList
     * 
     * @param Zend\Session\Container $auth
     */
    public function __construct($auth) {
        $this->auth = $auth;
    }

    /**
     * Sets the current data requested
     * 
     * @param Array $requestParams
     */
    public function setRequestParams($requestParams) {
        $this->requestParams = $requestParams;
    }

    /**
     * Sets the array of all routes
     * 
     * @param type $config
     */
    public function setRoutes($config) {
        $this->routes = new \stdClass();
        $this->routes->whiteList = $config["whiteList"];
    }

    /**
     * Check the current request with routes and session
     * 
     */
    public function check() {
        $controller = $this->requestParams["controller"];
        $action = $this->requestParams["action"];
        #Validando expiración de la session
        #$this->isExpired($controller, $action);
        #Forzando a resetear contraseña
        $this->resetPassword($controller, $action);
        #Validando la solicitud conforme a la lista blanca (Si = Sesion Requerida || No = Sin Sesión)
        if (!$this->checkPermits($controller, $action, "whiteList")) {
            #Validando las solicitudes conforme al perfil
            $this->checkRequest($controller, $action);
        } else if ($this->auth && $this->auth->userId !== null &&
                $controller === 'Application\Controller\Users' &&
                ( $action === 'login' || $action == 'resetPassword' || $action == 'applyReset' )
        ) {
            #Validar el login solo si el usario no esta logeado
            $this->redirectURL($this->requestParams, "index", "home", 302);
        } else if ($this->auth->userId == null &&
                $controller == "Application\Controller\Users" &&
                ($action == 'login' || $action == 'resetPassword' || $action == 'applyReset')
        ) {
            if (\get_class($this->requestParams["result"]) === "Zend\View\Model\ViewModel") {
                $this->setVarsToLayout($controller, $action);
            }
        }
    }

    /**
     * Checks the current request with the user profile
     * 
     * @param String $controller
     * @param String $action
     */
    private function checkRequest($controller, $action) {
        if (!($this->auth && $this->auth->userId !== null)) {
            $this->redirectURL($this->requestParams, "login", "login", 302);
        }
        #Validando que el admin o admin provider tenga correo electronico
        $this->setEmail($controller, $action);
        #Obtener valores para enviar al layout principal
        if (\get_class($this->requestParams["result"]) === "Zend\View\Model\ViewModel") {
            $this->setVarsToLayout($controller, $action);
        }
    }

    /**
     * Checks if the user has permition of access
     * 
     * @param String $controller
     * @param String $action
     * @param String $kind
     * @return boolean
     */
    private function checkPermits($controller, $action, $kind, $exceptModules = array()) {
        $modules = $this->getModules($kind);
        $result = false;
        foreach ($modules as $m) {
            if ($m["controller"] === $controller && in_array($action, $m["action"])) {
                $result = true;
                if (count($exceptModules) > 0) {
                    $exist = false;
                    foreach ($exceptModules as $e) {
                        if ($e["controller"] === $m["controller"] && in_array($action, $e["action"])) {
                            $exist = true;
                            break;
                        }
                    }
                    if ($exist === true) {
                        $result = false;
                    }
                }
                if ($result) {
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Gets all routes by kind module
     * 
     * @param String $kind
     * @return Array
     */
    private function getModules($kind) {
        $modules = array();
        switch ($kind) {
            case "whiteList":
                $modules = $this->routes->whiteList;
                break;
        }
        return $modules;
    }

    /**
     * Sets the client to an error page
     * 
     * @param Array $requestParams
     * @param String $action
     * @param String $name
     * @param Int $statusCode
     */
    private function redirectURL($requestParams, $action, $name, $statusCode) {
        $url = $requestParams["router"]->assemble(array('action' => $action), array('name' => $name));
        $request = $requestParams["target"]->getRequest();
        $app = null;
        $flag = false;
        if($action === "users" && $name === "logout"){
            $url.="?credentials=expired";
            $flag=true;
        }
        if (isset($_GET["app"])) {
            $app = (isset($_GET["app"]) ? $_GET["app"] : null);
        }
        if ($app !== null) {
            if ($app === "gestion_cuad" || $app === "directorio" || $app === "reportes") {
                $url .= ($flag===true)?"&":"?"."app=" . $app;
            }
        }
        if (!empty($request->getServer()->HTTP_X_REQUESTED_WITH) && strtolower($request->getServer()->HTTP_X_REQUESTED_WITH) == 'xmlhttprequest'
        ) {
            if (isset($_POST["app"])) {
                $app = (isset($_POST["app"]) ? $_POST["app"] : null);
            }
            if ($app !== null) {
                if ($app === "gestion_cuad" || $app === "directorio" || $app === "reportes") {
                    $url .= "?app=" . $app;
                }
            }
            \header('Content-Type: application/json');
            die(\json_encode(array("redirect" => $url)));
        }
        $requestParams["response"]->getHeaders()->addHeaderLine('Location', $url);
        $requestParams["response"]->setStatusCode($statusCode);
        $requestParams["response"]->sendHeaders();
        exit();
    }

    /**
     * Redirect to logout
     * 
     * @param type $controller
     * @param type $action
     */
    private function isExpired($controller, $action) {
        if ($this->auth && $this->auth->time !== null &&
                (time() - $this->auth->time) >= 1800 &&
                $controller !== 'Application\Controller\Users' &&
                $action !== 'logout'
        ) {
            $this->redirectURL($this->requestParams, "users", "logout", 302);
        }
    }

    /**
     * Redirect to changePassword
     * 
     * @param type $controller
     * @param type $action
     */
    private function resetPassword($controller, $action) {
        #Forzar el cambio de contraseña
        if ($this->auth && ( $this->auth->token !== null || $this->auth->forceReset === true ) &&
                !($controller == "Application\Controller\Users" && $action == "changePassword") && !($controller == "Application\Controller\Users" && $action == "logout")
        ) {
            $this->redirectURL($this->requestParams, "change-password", "change-password", 302);
        }
    }

    /**
     * Redirect to setEmail
     * 
     * @param type $controller
     * @param type $action
     */
    private function setEmail($controller, $action) {
        
        $auth = new Container('auth');
        $email = $this->auth->emailAddress;
      
        if ($this->auth->userId != null && ( \is_null($email) || !\filter_var($email, FILTER_VALIDATE_EMAIL) ) && !($controller == "Application\Controller\Users" && $action == "setEmail") && !($controller == "Application\Controller\Users" && $action == "logout") && !($controller == "Application\Controller\Users" && $action == "changePassword")
        ) {
            $this->redirectURL($this->requestParams, "set-email", "set-email", 302);
        }
        #Validando que el admin o admin provider no vuelva a actualizar su correo electronico
        if ($this->auth->userId != null && (!\is_null($email) || \filter_var($email, FILTER_VALIDATE_EMAIL) ) && $controller == "Application\Controller\Users" && $action == "setEmail" && ( $this->auth->token === null || $this->auth->forceReset === null )
        ) {
            $this->redirectURL($this->requestParams, "error", "error", 403);
        }
    }

    /**
     * Sets variables to layout
     * 
     * @param String $controller
     * @param String $action
     */
    private function setVarsToLayout($controller, $action) {
        $this->requestParams["result"]->setVariable('auth', $this->auth->getArrayCopy());
        $this->requestParams["result"]->setVariable('controller', $controller);
        $this->requestParams["result"]->setVariable('action', $action);
    }

}
