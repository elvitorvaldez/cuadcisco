<?php

namespace Application\Model;

use Application\Form\Base\FormAdapter;
use Application\OCISchema\Authentication;
use Application\OCISchema\User;
use Application\OCISchema\Database\ProfileComplements as Profiles;
use Application\OCISchema\Util\Cipher;
use Application\Util\Mail;
use Application\Util\Validator;
use Ramsey\Uuid\Uuid;

/**
 * Description of ResetPassword
 *
 * @author Roman
 */
class ResetPassword extends FormAdapter {

    use Profiles;

    public $userId;
    public $token;
    public $app;
    public $date;
    private $newPassword;
    private $confirmPassword;
    private $soap;
    private $config;
    private $db;
    private $error;

    /**
     * Validates the model
     * 
     * @return boolean|Array
     */
    public function isValid() {
        if ($this->userId !== null && $this->token === null) {
            $this->date = date("Y-m-d H:i:s");
            $this->token = Uuid::uuid5(Uuid::NAMESPACE_DNS, $this->userId . $this->date)->toString();
        }
        $validator = Validator::getInstance();
        $filters = array(
            array("name" => "userId", "rules" => array("required")),
            array("name" => "token", "rules" => array("required", "uuid5")),
        );
        $data = $this->getData();
        if ($this->newPassword !== null && $this->confirmPassword !== null) {
            $data = \array_merge($data, $this->getCredentials());
            $filters[] = array("name" => "newPassword", "rules" => array("required", "minlength_8", "maxlength_45", "password"));
            $filters[] = array("name" => "confirmPassword", "rules" => array("required", "equalPassword_" . $this->newPassword));
        }
        $resultValidation = $validator->validateArray($data, $filters);
        if ($resultValidation === false) {
            return true;
        } else {
            return $resultValidation;
        }
    }

    /**
     * Validates the model
     * 
     * @return boolean|Array
     */
    public function validateToken($credentials = null) {
        if ($credentials !== null) {
            $this->newPassword = $credentials["newPassword"];
            $this->confirmPassword = $credentials["confirmPassword"];
        }
        $resetPassord = $this->findResetPassword($this->token);
        if ($resetPassord->count() === 1) {
            $resetPassord->next();
            $this->userId = $resetPassord->current()["userId"];
            $this->date = $resetPassord->current()["date"];
        }
        return $this->isValid();
    }

    /**
     * Creates an register for reset the password of an users
     * 
     * @param bool $force
     * @return Array
     */
    public function resetPassword($force = false) {
        $result = -1;
        $profile = $this->getAccess($this->userId);
        if (!empty($profile)) {
            $email = null;
            $user = $this->getUser($this->userId, $profile["type"]);
            if ($user !== null) {
                if ($profile["type"] === "Usuario") {
                    $email = $user["emailAddress"];
                } else {
                    $email = $profile["email"];
                }
                if ($this->isAValidEmail($email)) {
                    $statement = $this->createResetPassword($this->userId, $this->token, $this->date);
                    if ($statement !== null && $statement->getGeneratedValue() >= 1) {
                        $kind = ( $force === true ) ? 'notify_change_password' : 'reset_password_confirm';
                        $this->sendEmail($email, $user["firstName"] . " " . $user["lastName"], $kind);
                        $result = 1;
                    }
                }
            } else {
                if ($profile["type"] === "Usuario") {
                    $result = -2;
                }   
            }
        }else{
            $result = 0;
        }
        return $result;
    }

    /**
     * Applies the new password
     * 
     * @return Boolean|String
     */
    public function applyReset() {
        $result = true;
        $this->getSoap();
        $profile = $this->getAccess($this->userId);
        $user = new User($this->soap->client, $this->soap->id, $this->db);
        $userData = $user->readUser($this->userId, $profile["type"]);
        if ($profile["type"] === "Usuario") {
            $response = $user->changePassword($this->userId, $this->newPassword);
        } else if ($profile["type"] === "Cliente") {
            $userData["emailAddress"] = $profile["email"];
            $response = $user->changePassword($this->userId, $this->newPassword);
        } else {
            $userData["emailAddress"] = $profile["email"];
            $response = $user->changeSystemPassword($this->userId, $this->newPassword);
        }
        $this->error = $user->getErrorResponse();
        if ($this->error !== "" && $response === null) {
            $result = false;
        } else {
            $this->deleteResetPassword($this->token);
            $this->sendEmail($userData["emailAddress"], $userData["firstName"] . " " . $userData["lastName"], 'change_password');
        }
        return $result;
    }

    /**
     * Gets the current error
     * 
     * @return String
     */
    public function getError() {
        $error = "";
        if ($this->error !== null) {
            $error = $this->error;
        }
        return $error;
    }

    /**
     * Sets the current database adpter
     * 
     * @param Zend\Db\Adapter\Adapter $db
     */
    public function setDriver($db) {
        $this->db = $db;
    }

    /**
     * Sets the current configuration
     * 
     * @param Array $config
     */
    public function setConfig($config) {
        $this->config = $config;
    }

    /**
     * Gets the user data
     * 
     * @param String $userId
     * @param String $profile
     * @return NULL|Array
     */
    private function getUser($userId, $profile = 'Usuario') {
        $this->getSoap( ( ($profile==='Supervisor')?true:false) );
        $user = new User($this->soap->client, $this->soap->id, $this->db);
        $userData = $user->readUser($userId, $profile);
        if ($profile === 'Usuario') {
            $devicesData = $user->getListDevices($userData["serviceProviderId"], $userData["groupId"], $userId);
            $devices = ( $devicesData !== null && 
                isset($devicesData["endpointTable"]["row"]) )
                ? $devicesData["endpointTable"]["row"] : array();
            if ($this->hasAnUCOne($devices)) {
                $userData["emailAddress"] = (isset($userData["emailAddress"]) ? $userData["emailAddress"] : "");
            } else {
                $userData = null;
            }
        }
        return $userData;
    }

    /**
     * Gets the soap connection to XSP
     * 
     * @return StdClass
     */
    private function getSoap($isSupervisor=false) {
        if ($this->soap === null) {
            $key = ($isSupervisor) ? 'system' : 'provising';
            $authentication = new Authentication($this->config['server']["url"] . $this->config['server']["wsdl"]);
            $user = Cipher::decrypt($this->config['auth'][$key]['user']);
            $pass = $this->config['auth'][$key]['pass'];
            $authentication->login($user, $pass);
            $this->soap = new \stdClass();
            $this->soap->client = $authentication->getSoapClient();
            $this->soap->id = $authentication->getSessionId();
        }
        return $this->soap;
    }

    /**
     * Gets the current credentials
     * 
     * @return Array
     */
    private function getCredentials() {
        $temp = array(
            "newPassword" => $this->newPassword,
            "confirmPassword" => $this->confirmPassword
        );
        return $temp;
    }

    /**
     * Validates an email address
     * 
     * @param String $email
     * @return boolean
     */
    private function isAValidEmail($email) {
        $result = true;
        if (\filter_var($email, \FILTER_VALIDATE_EMAIL) === FALSE) {
            $result = false;
        }
        return $result;
    }

    /**
     * Verify if in the array exist a ucone device
     * 
     * @param Array $data
     * @return boolean
     */
    private function hasAnUCOne($data) {
        $result = false;
        foreach ($data as $d) {
            if (( $d[7] === "Business Communicator - PC" ||
                    $d[7] === "Business Communicator - Table" ||
                    $d[7] === "Business Communicator - Mobile" ) &&
                    !\preg_match('/Polycom|polycom/', $d[7])
            ) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Sends a notification to user for reset him/her password
     * 
     * @param String $email
     */
    private function sendEmail($email, $name, $kind = "reset_password_confirm") {
        $mail = new Mail();
        $mail->addTo($email);
        $flag = false;
        $subject = "";
        $data = array();
        switch ($kind) {
            case 'notify_change_password':
                $flag = true;
                $subject = "Expiración de Contraseña - Gestión CUAD";
                $data = array("name" => $name, "userId" => $this->userId, "token" => $this->token, "app" => $this->app);
                break;
            case 'reset_password_confirm':
                $flag = true;
                $subject = "Recuperación de Contraseña - Gestión CUAD";
                $data = array("name" => $name, "userId" => $this->userId, "token" => $this->token, "app" => $this->app);
                break;
            case 'change_password':
                $flag = true;
                $subject = "Contraseña Actualizada - Gestión CUAD";
                $data = array("name" => $name, "userId" => $this->userId);
                break;
        }
        if ($flag === true) {
            $mail->setSubject($subject);
            $mail->addMessage($kind, $data);
            $mail->send();
        }
    }

}
