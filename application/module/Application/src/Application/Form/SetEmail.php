<?php

namespace Application\Form;

use Application\Form\Base\FormAdapter;
use Application\OCISchema\Authentication;
use Application\OCISchema\User;
use Application\OCISchema\Util\Cipher;
use Application\Util\Validator;


/**
 * Description of SetEmail
 *
 * @author Roman
 */
class SetEmail extends FormAdapter {

    //Properties of SetEmail
    public $email;

    /**
     * Constructor
     * 
     * @param Array $data
     */
    public function __construct($data = null) {
        if ($data !== null) {
            foreach ($data as $key => $value) {
                if (\property_exists(__CLASS__, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Validates the model
     * 
     * @return boolean|Array
     */
    public function isValid() {
        $validator = Validator::getInstance();
        $resultValidation = $validator->validateArray(
                $this->getData(), array(
            array("name" => "email", "rules" => array("required", "email"))
                )
        );
        if ($resultValidation === false) {
            return true;
        } else {
            return $resultValidation;
        }
    }

    /**
     * Saves the email to current user
     * 
     * @param String $userId
     * @param String $profile
     * @param Zend\Db\Adapter\Adapter $db
     * @param Array $config
     * @return Boolean|String
     */
    public function saveEmail($userId, $profile, $db = null, $config = null) {
        //$soap = null;
        $result = false;

        //buscar usuario
        // si el usuario no está en la bdd, agregarlo
        // de lo contrario, editarlo
       
        
        if ($profile === "Usuario") {
            //$soap = $this->authXSP($config);
            //$user = new User($soap->client, $soap->id);
            $result = $user->setEmail($this->email, $userId, $auth->profile);
        } else {
            $user = new User();
            $user->setDatabaseAdapter($db);
            $result = $user->setEmail($this->email, $userId, $profile);
        }
        return $result;
    }

    private function authXSP($config) {
        $soap = null;
        $authentication = new Authentication($config['server']["url"] . $config['server']["wsdl"]);
        $authentication->login(
                Cipher::decrypt($config['auth']["provising"]["user"]), $config['auth']["provising"]["pass"]
        );
        $soap = new \stdClass();
        $soap->client = $authentication->getSoapClient();
        $soap->id = $authentication->getSessionId();
        return $soap;
    }

}
