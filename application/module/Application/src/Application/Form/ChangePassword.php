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
class ChangePassword extends FormAdapter {

    //Properties of ChangePassword
    public $realOldPassword;
    public $newPassword;
    public $oldPassword;
    public $confirmPassword;

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
            array("name" => "oldPassword", "rules" => array("required", "minlength_8", "maxlength_45", "equalOldPassword_" . $this->realOldPassword)),
            array("name" => "newPassword", "rules" => array("required", "minlength_8", "maxlength_45", "password", "distinctPassword_" . $this->realOldPassword, "reversePassword_" . $this->realOldPassword)),
            array("name" => "confirmPassword", "rules" => array("required", "equalPassword" . $this->newPassword))
                )
        );
        if ($resultValidation === false) {
            return true;
        } else {
            return $resultValidation;
        }
    }

    /**
     * Sets the cuurent password to model
     * 
     * @param String $str
     */
    public function setRealOldPassword($str) {
        $this->realOldPassword = Cipher::decrypt($str);
    }

    /**
     * Gets the new password encrypted
     * 
     * @return String
     */
    public function getNewPassword() {
        return Cipher::encrypt($this->newPassword);
    }

}
