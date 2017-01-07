<?php

namespace Application\Controller;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Application\OCISchema\Authentication;
use Application\OCISchema\User;
use Application\Form\ChangePassword;
use Application\Form\LogIn;
use Application\Form\SetEmail;
use Application\Model\Session;
use Application\Util\Mail;
use Zend\Session\Container;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;


use Zend\Mvc\Controller\AbstractController;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;




/**
 * Description of UsersController
 *
 * @author Roman
 */
class UsersController extends AbstractActionController
{
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
    
    
    /**
     * Gets the login form or authenticates an user
     * 
     * @return ViewModel|JsonModel
     */
    public function loginAction()
    {
        $attemps=0;
        $postRequest = (array) $this->getRequest()->getPost();
        if ($this->getRequest()->isPost()) {
            
            $username              = $this->getRequest()->getPost('userId');
            $password              = $this->getRequest()->getPost('password');
            $ldap_dn_operators     = "CN=Clarus Operators,ou=Groups,dc=vsys, dc=com";
            $ldap_dn_administrator = "CN=Clarus Administrators,ou=Groups,dc=vsys, dc=com";
            $ldap_dn_usuarios      = "OU=People,DC=vsys,DC=com";
            $msg                   = "";
            $msgError              = 0;
            
            
            if ($username == "" || $password == "") {
                $msgError = 1;
            }
            
            $ldapconn = ldap_connect("10.1.17.47");
            if (!$ldapconn) {
                $msgError = 3;
            }
            
            
            if ($ldapconn) {
                $userlogin    = "uid=$username,ou=People,dc=vsys,dc=com";
                
                //echo "$ldapconn   $userlogin   $password";
                $ldapbinduser = ldap_bind($ldapconn, $userlogin, $password);
               
                if (!($ldapbinduser)) {
                    $msgError = 2;
                }
                
                
                else {
                    $unmail  = explode("@", $username);
                    $usuario = $unmail[0];
                    if (isset($unmail[1])) {
                        $domext = $unmail[1];
                    } else {
                        $domext = "";
                    }
                    $username = $unmail[0];
                    if ($domext != "" && $domext != "vsys.com") {
                        $msgError = 4;
                    }
                }
               
                
                $correo = $username . '@vsys.com';
               
                $filter = "(uid=$username)";
                //$filter = "(mail=$correo)";
                $attr   = array(
                    "cn",
                    "mail",
                    "uid",
                    "givenName",
                    "sn"
                );
                
                $resultUsers = ldap_search($ldapconn, $ldap_dn_usuarios, $filter, $attr);
                $entries     = ldap_get_entries($ldapconn, $resultUsers);
             
                
                if ($entries["count"] > 0) {
                
                    $partirNombre=explode(" ",$entries[0]["cn"][0]);
                    $emailAddress    = $entries[0]["mail"][0];
                    $name    = $entries[0]["cn"][0];
                    $username  = $entries[0]["uid"][0];
                    $firstname = $partirNombre[0];
                    $lastname  = $entries[0]["sn"][0];
                  
                    $attr = array(
                        "member"
                    );
                                       
                    $resultAdmin  = ldap_search($ldapconn, $ldap_dn_administrator, "(member= *uid=$username*)", $attr);
                    $entriesAdmin = ldap_get_entries($ldapconn, $resultAdmin);
                    $resultOp     = ldap_search($ldapconn, $ldap_dn_operators, "(member= *uid=$username*)", $attr);
                    $entriesOp    = ldap_get_entries($ldapconn, $resultOp);
                    $success      = true;
                    if ($entriesAdmin["count"] > 0) {
                        $result["profile"] = "Administrator";
                        $result["group"] = "Administrators";
                        $emailAddress="sistemas@vsys.com";
                    }
                    
                    else if ($entriesOp["count"] > 0) {
                        $result["profile"] = "User";
                        $result["group"]   = "Operators";
                       
                    }
                    
                    else {
                        $msgError = 2;
                        $success  = false;
                    }
                }
            }
            
            
            
            /*$buscaUsuario=$this->getUserTable()->getUserByUsername($username);
            
            if (isset($buscaUsuario))
            {
            $getAttemp=$this->getUserTable()->getAttempByUsername($username);
                $attemps=$getAttemp->attemps;
                if ($attemps=="3"){$msj=5; break;}
            }
            
            if (isset($buscaUsuario) && $msgError = 2 && !(isset($success)))
            {
                $getAttemp=$this->getUserTable()->getAttempByUsername($username);
                $attemps=$getAttemp->attemps;
                if ($attemps=="3"){$msj=5; break;}
                if ($attemps==""){$attemps="0";}
                
                    $attemps++;
                $this->getUserTable()->addAttemp($getAttemp->id,$attemps);  
            }*/
            
            
            switch ($msgError) {
                
                case 1:
                    $msg = "El usuario y la contraseña no pueden ser vacíos.";
                    break;
                case 2:
                    $msg = "El usuario o contraseña son incorrectos o no existen en el grupo";
                    break;
                case 3:
                    $msg = "No se pudo conectar al servidor de autenticación.";
                    break;
                case 4:
                    $msg = "El correo no es de Vsys.";
                    break;
                case 5:
                    $msg = "Usuario Bloqueado";
                    break;
                    
            }
            
            
//            $ldapOK=ldap_error($ldapconn);
//            if ($ldapOK=="Success") 
//            {$success=true;}
//            
            $result["success"] = $success;
            $result["message"] = $msg;
   
            if ($result["success"] == true) {
                //$auth=$this->getSessionService();
                //$this->getDatabaseService();
                // $session = new Session($this->auth, $this->db);
             
                $auth             = new Container("auth");
                
                $root="";
                
                $auth->name     = $name;
                $auth->emailAddress     = $emailAddress;
                $auth->username   = $username;
               
                //$auth->role        = "Administrators";
                $auth->user_group      = $result["group"];
                $auth->firstName  = $firstname;
                $auth->lastName   = $lastname;
                $result["userId"] = $username;                
                $auth->userId     = $username;
                $auth->psw        = $password;
               
                //$this->getUserTable()->addAttemp($getAttemp->id,"0");
               
               
                //$session->init($result["userId"]);
                $isUser = $this->getUserTable()->getUserByUsername($auth->username);
                if($isUser)
                {
                 $specificRole=$this->getUserTable()->getRoleByUsername($auth->username); 
                 $usergroup=$specificRole->user_group;
                 $roll=$specificRole->role;
                 //die($usergroup."  +++ ". $roll);
                 if ($usergroup==2 and $roll==4)
                 {
                     $root="1";
                     $result["profile"]="root";
                 }
                 else if ($usergroup==2 and $roll<4)
                 {
                     $result["profile"]="NOC";
                 }
                 
                }
                 $auth->root= $root;
                  $auth->role        = $result["profile"];
                $this->getUserTable()->saveMail($auth, $isUser);
                unset($result["profile"]);
                               
                //if (isset($postRequest["app"])) {
                    //$result["url"] = $this->getURL($postRequest["app"], $auth->rol);
                  //  $result["url"] = "../../".($postRequest["app"]);                   
                //} else {
                    $result["url"] = $this->url()->fromRoute("home");
                //}
            }
            return new JsonModel($result);
        } else {
            $credentials = (isset($_GET["credentials"]) ? $_GET["credentials"] : null);
            $app         = (isset($_GET["app"]) ? $_GET["app"] : null);
            return $this->showForm(array(
                "credentials" => $credentials,
                "app" => $app
            ));
        }
        
    }
    

    
    /**
     * Clears the current session
     * 
     * @return Redirect
     */
    public function logoutAction()
    {
        $auth        = new Container("auth");
        $credentials = (isset($_GET["credentials"]) ? $_GET["credentials"] : null);
        $app         = (isset($_GET["app"]) ? $_GET["app"] : null);
        
         foreach ($auth->getArrayCopy() as $key => $item) {
                unset($auth->$key);
            }
        
        if (!$app && $auth->userId != null) {

           
         return $this->redirect()->toRoute('home');
          // return $this->redirect()->toUrl($this->url()->fromRoute("home") . "login?app=" . $app);
            //            $this->getDatabaseService();
            //            $session = new Session($this->auth, $this->db);
            //$session->remove();
        }
        if ($credentials === "expired" && $app !== null) {
           
            return $this->redirect()->toUrl($this->url()->fromRoute("home") . "login?app=" . $app . "&credentials=expired");
        } else if ($credentials === "expired") {
        
            return $this->redirect()->toUrl($this->url()->fromRoute("home") . "login?credentials=expired");
        } else if ($app !== null) {
            
            return $this->redirect()->toUrl($this->url()->fromRoute("home") . "login?app=" . $app);
        } else {

            return $this->redirect()->toRoute('login', array(
                'controller' => 'Index',
                'action' => 'login'
            ), array(
                "credentials"
            ));
        }
    }
    
    /**
     * Gets the view setEmail or sets the email of current user logged
     * 
     * @return JsonModel|ViewModel
     */
    
    
    
    public function controlpanelAction()
    {
        //$this->layout("layout/layout_tables.phtml");
        $auth        = new Container("auth");   
         $allApps=$this->getAppsTable()->fetchAll();
         $allUsers=$this->getUserTable()->fetchAll();
          $view = new ViewModel();
            $view->setVariables(array(
                
                "allApps" => $allApps,
                "allUsers" => $allUsers
            ));
            return $view;
         
    } 
    
    
    
    
     public function saveUserAppsAction()
    {
         $Apps = $this->getRequest()->getPost('Apps');
         $user = $this->getRequest()->getPost('user');
        
         $appsArray=explode('|',$Apps);
        //borrar todo por usuario
         $this->getUserappTable()->clearByUser($user);
        //crear nuevos registros
         //print_r($appsArray); echo $user;die();
         foreach ($appsArray as $app)
         {
            $this->getUserappTable()->addUserApp($user,$app);               
         }       
        die();
    }
    
    public function setEmailAction()
    {
        
        $auth = new Container('auth');
        
        if ($this->getRequest()->isPost()) {
            $postRequest = (array) $this->getRequest()->getPost();
            $auth        = new Container("auth");
            $result      = $this->validateFormSetEmail($postRequest);
            
            if ($result["success"]) {
                
                $this->auth->emailAddress = $result["email"];
                if (isset($postRequest["app"])) {
                    $result["url"] = $this->getURL($postRequest["app"], $this->auth->profile);
                } else {
                    
                    $result["url"] = $this->url()->fromRoute("home");
                }
                
                unset($result["email"]);
            }
            return new JsonModel($result);
        } else {
            
            $app  = (isset($_GET["app"]) ? $_GET["app"] : null);
            $view = new ViewModel();
            $view->setVariables(array(
                "auth" => (object) $auth,
                "app" => $app
            ));
            return $view;
        }
        
    }
    
    /**
     * Gets the view changePassword or Changes the password of the current user
     * 
     * @return JsonModel|ViewModel
     */
    public function changePasswordAction()
    {
        $this->getSessionService();
        if ($this->getRequest()->isPost()) {
            $result = $this->validateFormChangePassword((array) $this->getRequest()->getPost());
            if ($result["success"]) {
                $result["userId"]     = $this->auth->userId;
                $this->auth->password = $result["password"];
                $this->auth->offsetUnset("token");
                $this->auth->offsetUnset("forceReset");
                unset($result["password"]);
            }
            $result["userId"] = $this->auth->userId;
            return new JsonModel($result);
        } else {
            $view = new ViewModel();
            $view->setVariable("auth", $this->auth->getArrayCopy());
            return $view;
        }
    }
    
    /**
     * Gets the view resetPassword or Request by email a link to reset the password
     * 
     * @return JsonModel|ViewModel
     */
    public function resetPasswordAction()
    {
        if ($this->getRequest()->isPost()) {
            $postRequest = (array) $this->getRequest()->getPost();
            $result      = $this->validateFormResetPassword($postRequest);
            if ($result["success"] === 1) {
                if (isset($postRequest["app"])) {
                    $result["url"] = $this->getURL($postRequest["app"], null, 'login');
                } else {
                    $result["url"] = $this->url()->fromRoute("login");
                    ;
                }
            }
            return new JsonModel($result);
        } else {
            $app = (isset($_GET["app"]) ? $_GET["app"] : null);
            return $this->showForm(array(
                "app" => $app
            ));
        }
    }
    
    /**
     * Gets the view applyResetPassword or Resets the current password
     * 
     * @return JsonModel|ViewModel
     */
    public function applyResetAction()
    {
        if ($this->getRequest()->isPost()) {
            $postRequest = (array) $this->getRequest()->getPost();
            $result      = $this->validateFormApplyResetPassword($postRequest);
            if ($result["success"] === true) {
                if (isset($postRequest["app"])) {
                    $result["url"] = $this->getURL($postRequest["app"], null, 'login');
                } else {
                    $result["url"] = $this->url()->fromRoute('login');
                }
            }
            return new JsonModel($result);
        } else {
            $token                = $this->getEvent()->getRouteMatch()->getParam("token");
            $resetPassword        = $this->getServiceLocator()->get('Application\Service\ResetPassword');
            $resetPassword->token = $token;
            $resultValidation     = $resetPassword->validateToken();
            if ($resultValidation === true) {
                $app = (isset($_GET["app"]) ? $_GET["app"] : null);
                return $this->showForm(array(
                    "token" => $token,
                    "app" => $app
                ));
            } else {
                return $this->notFoundAction();
            }
        }
    }
    
    /**
     * Gets the view´s form depending of the view (Login|Reset|ApplyReset)
     * 
     * @return ViewModel
     */
    private function showForm($paremetes = array())
    {
        $layout = $this->layout();
        $layout->setTemplate('layout/form');
        $viewModel = new ViewModel();
        if (count($paremetes) > 0) {
            $viewModel->setVariables($paremetes);
        }
        return $viewModel;
    }
    
    /**
     * Validates and authenticates an user
     * 
     * @param Array $data This contains two keys userId and password
     * @return Array
     */
    private function validateFormLogIn($data)
    {
        
        $result = array(
            "success" => false,
            "message" => "Ocurrió un error inesperado"
        );
        $route  = "UsersController/loginAction";
        try {
            $login            = new LogIn($data);
            $resultValidation = $login->isValid();
            if ($resultValidation === true) {
                
                $auth     = new Authentication($this->getWebService(), $this->getDatabaseService());
                $response = $auth->login($login->userId, $login->getPasswordEncrypted());
                $error    = $auth->getErrorResponse();
                if ($error === "" && $response !== null) {
                    $result["success"]             = true;
                    $result["message"]             = "OK";
                    $result["profile"]             = \array_merge($response, $login->getData());
                    $result["profile"]["password"] = $login->getPasswordEncrypted();
                    $this->generateLog($route, "event", "USER_LOGGED", $data["userId"], null);
                } else {
                    $result["message"] = ($error !== "") ? $error : "Credenciales inválidas";
                    $this->generateLog($route, "error", "USER_LOGIN_FAIL", $data["userId"], $error);
                }
            } else {
                $result["validation"] = $resultValidation;
                $this->generateLog($route, "error", "VALIDATION_FAILS", null, null);
            }
        }
        catch (\Exception $ex) {
            $this->generateLog($route, "error", "SYSTEM_ERROR", null, $ex->getMessage());
        }
        return $result;
    }
    
    /**
     * Validates and sets the email an user
     * 
     * @param Array $data This contains one key that is email
     * @return Array
     */
    private function validateFormSetEmail($data)
    {
        $result = array(
            "success" => false,
            "message" => "Ocurrió un error inesperado"
        );
        $route  = "UsersController/setEmailAction";
        try {
            //$this->getSessionService();
            
            $setEmail         = new SetEmail($data);
            $resultValidation = $setEmail->isValid();
            
            $config = array();
            if ($resultValidation === true) {
                $auth = new Container("auth");
                
                $isUser = $this->getUserTable()->getUserByUsername($auth->username);
                
                //SI isuser es true, actualizar
                
                $this->getUserTable()->saveMail($auth, $isUser);
                
                //de lo contrario, insertar
                
                
                //print_r($returnArray);
                //$response = $setEmail->saveEmail($this->auth->userId, $this->auth->profile, $this->db, $config);
                $response = true;
                
                if ($response !== true) {
                    $result["message"] = $response;
                    //$this->generateLog($route, "error", "PROFILE_EMAIL_ERROR", $this->auth->userId, $response);
                } else {
                    $result["success"] = true;
                    $result["message"] = "OK";
                    $result["email"]   = $data["email"];
                    //$this->generateLog($route, "event", "PROFILE_EMAIL_OK", $this->auth->userId, "Email: " . $data["email"] . ".");
                }
            } else {
                $result["validation"] = $resultValidation;
                //$this->generateLog($route, "error", "VALIDATION_FAILS", null, null);
            }
        }
        catch (\Exception $ex) {
            // $this->generateLog($route, "error", "SYSTEM_ERROR", null, $ex->getMessage());
        }
        return $result;
    }
    
    /**
     * Validates and changes password an user
     * 
     * @param Array $data This contains three keys (newPassword, oldPassword, confirmPassword)
     * @return Array
     */
    private function validateFormChangePassword($data)
    {
        $result = array(
            "success" => false,
            "message" => "Ocurrió un error inesperado"
        );
        $route  = "UsersController/changePasswordAction";
        try {
            $changePassword = new ChangePassword($data);
            $changePassword->setRealOldPassword($this->auth->password);
            $resultValidation = $changePassword->isValid();
            if ($resultValidation === true) {
                $this->getSoapConnectionService();
                $this->getDatabaseService();
                $user     = new User($this->soap->client, $this->soap->id, $this->db);
                $response = $user->changePassword($this->auth->userId, $changePassword->newPassword, $changePassword->oldPassword);
                $error    = $user->getErrorResponse();
                if ($error !== "" && $response === null) {
                    $mail = new Mail();
                    $mail->addTo($this->auth->emailAddress);
                    $mail->setSubject("Contraseña Actualizada - Gestión CUAD");
                    $mail->addMessage("change_password", array(
                        "name" => $this->auth->firstName . " " . $this->auth->lastName,
                        "userId" => $this->auth->userId
                    ));
                    $mail->send();
                    $result["message"] = $error;
                    $this->generateLog($route, "error", "CHANGE_PASSWORD_ERROR", $this->auth->userId, $error);
                } else {
                    $result["success"]  = true;
                    $result["message"]  = "OK";
                    $result["password"] = $changePassword->getNewPassword();
                    $this->generateLog($route, "event", "CHANGE_PASSWORD_OK", $this->auth->userId, null);
                }
            } else {
                $result["validation"] = $resultValidation;
                $this->generateLog($route, "error", "VALIDATION_FAILS", null, null);
            }
        }
        catch (\Exception $ex) {
            $this->generateLog($route, "error", "SYSTEM_ERROR", null, $ex->getMessage());
        }
        return $result;
    }
    
    /**
     * Validates and get a notification for reset password
     * 
     * @param Array $data This contains three keys (userId)
     * @return Array
     */
    private function validateFormResetPassword($data)
    {
        $result = array(
            "success" => -1,
            "message" => "Ocurrió un error inesperado"
        );
        $route  = "UsersController/resetPasswordAction";
        try {
            $resetPassword = $this->getServiceLocator()->get('Application\Service\ResetPassword');
            $resetPassword->setData($data);
            $resultValidation = $resetPassword->isValid();
            if ($resultValidation === true) {
                $result["success"] = $resetPassword->resetPassword();
                $error             = $resetPassword->getError();
                if ($result["success"] == 1 && $error === "") {
                    $result["message"] = "Se ha enviando un enlace para restablecer su contraseña.";
                    $this->generateLog($route, "event", "REQUEST_RESET_PASSWORD_OK", $resetPassword->userId, "Token: " . $resetPassword->token);
                } else {
                    if ($result["success"] === -2) {
                        $result["message"] = "No se encontró un Bussiness Comunicator asociado a su cuenta";
                    } else if ($result["success"] === -1) {
                        $result["message"] = "No se ha encontrado un email para poder recuperar su contraseña";
                    } else {
                        $result["message"] = "Usuario no válido";
                    }
                    $this->generateLog($route, "error", "REQUEST_RESET_PASSWORD_ERROR", $resetPassword->userId, $result["message"]);
                }
            } else {
                $result["validation"] = $resultValidation;
                $this->generateLog($route, "error", "VALIDATION_FAILS", null, null);
            }
        }
        catch (\Exception $ex) {
            $this->generateLog($route, "error", "SYSTEM_ERROR", null, $ex->getMessage());
        }
        return $result;
    }
    
    /**
     * Validates and changes the current password
     * 
     * @param Array $data This contains three keys (token)
     * @return Array
     */
    private function validateFormApplyResetPassword($data)
    {
        $result = array(
            "success" => false,
            "message" => "Ocurrió un error inesperado"
        );
        $route  = "UsersController/applyResetActionAction";
        try {
            $resetPassword        = $this->getServiceLocator()->get('Application\Service\ResetPassword');
            $resetPassword->token = $data["token"];
            $resultValidation     = $resetPassword->validateToken($data);
            if ($resultValidation === true) {
                $result["success"] = $resetPassword->applyReset();
                $error             = $resetPassword->getError();
                if ($result["success"] === true) {
                    $result["message"] = "Contraseña actualizada correctamente.";
                    $this->generateLog($route, "event", "CHANGE_PASSWORD_OK", $resetPassword->userId, "Token: " . $resetPassword->token);
                } else {
                    $result["message"] = $error;
                    $this->generateLog($route, "error", "CHANGE_PASSWORD_ERROR", $resetPassword->userId, $error);
                }
            } else {
                $result["validation"] = $resultValidation;
                $this->generateLog($route, "error", "VALIDATION_FAILS", null, null);
            }
        }
        catch (\Exception $ex) {
            $this->generateLog($route, "error", "SYSTEM_ERROR", null, $ex->getMessage());
        }
        return $result;
    }
    
    private function getURL($app, $profile, $route = 'home')
    {
        $serviceLocator = $this->getServiceLocator();
        $url            = $this->url()->fromRoute($route);
        if ($route === "home") {
            switch ($app) {
                case "gestion_cuad":
                    $url = str_replace("auth", "gestion_cuad", $url);
                    break;
                case "directorio":
                    if ($profile === "Usuario") {
                        $url = str_replace("auth", "directorio", $url);
                    }
                    break;
                case "reportes":
                    $url = str_replace("auth", "reportes", $url);
                    break;
            }
        } else {
            switch ($app) {
                case "gestion_cuad":
                    $url .= "?app=" . $app;
                    break;
                case "directorio":
                    $url .= "?app=" . $app;
                    break;
                case "reportes":
                    $url .= "?app=" . $app;
                    break;
            }
        }
        return $url;
    }
    
    public function getuserappsAction()
    {
        $user = $this->request->getPost("user");
        $userapps=$this->getUserappTable()->getAppsByUser($user);


        return new JsonModel(array(
            'usersapps' => $userapps,
        ));
        die();
    }  
    
    
}
