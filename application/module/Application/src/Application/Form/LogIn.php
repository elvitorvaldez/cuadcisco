<?php

namespace Application\Form;

use Application\Form\Base\FormAdapter;
use Application\OCISchema\Util\Cipher;
use Application\Util\Validator;

/**
 * Description of LogIn
 *
 * @author Roman
 */
class LogIn extends FormAdapter {

    //Properties of LogIn
    public $userId;
    public $password;

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
            array("name" => "userId", "rules" => array("required")),
            array("name" => "password", "rules" => array("required"))
                )
        );
        if ($resultValidation === false) {
            return true;
        } else {
            return $resultValidation;
        }
    }

    /**
     * Gets teh password encrypted
     * 
     * @return String
     */
    public function getPasswordEncrypted() {
        $password = "";
        if ($this->password !== "") {
            $password = Cipher::encrypt($this->password);
        }
        return $password;
    }

}
