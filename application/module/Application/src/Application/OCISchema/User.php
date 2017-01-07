<?php

namespace Application\OCISchema;

use Application\OCISchema\Base\BaseSchema;
use Application\OCISchema\Util\Cipher;

/**
 * Description of User
 *
 * @author Roman
 */
class User extends BaseSchema {

    use Database\ProfileComplements;

    /**
     * Construct of User Class
     * 
     * @param SoapClient $client
     * @param String $sessionId
     * @param Zend\Db\Adapter\Adapter $db
     */
    public function __construct($client = null, $sessionId = null, $db = null) {
        if ($client !== null && $sessionId !== null) {
            parent::__construct2($client, $sessionId, $db);
        }
    }

    /**
     * Changes the current password of an user
     * 
     * @param String $userId User identifier
     * @param String $newPassword New password typed
     * @param String $oldPassword Current password
     * @return Null|Array
     */
    public function changePassword($userId, $newPassword, $oldPassword = '') {
        $response = null;
        if ($oldPassword === '') {
            $tags = array('userId', 'newPassword');
            $data = array($userId, $newPassword);
        } else {
            $tags = array('userId', 'oldPassword', 'newPassword');
            $data = array($userId, $oldPassword, $newPassword);
        }
        $response = $this->executeCall($data, "PasswordModifyRequest", $tags, true);
        if ($this->getErrorResponse() === "" && $response !== null) {
            $this->extendModification($userId, Cipher::encrypt($newPassword));
        }
        return $response;
    }

    /**
     * 
     * @param String $userId
     * @param String $newPassword
     * @return Null|Array
     */
    public function changeSystemPassword($userId, $newPassword) {
        $tags = array('userId', 'password');
        $data = array($userId, $newPassword);
        $response = $this->executeCall($data, "SystemAdminModifyRequest", $tags, true);
        if ($this->getErrorResponse() === "" && $response !== null) {
            $this->extendModification($userId, Cipher::encrypt($newPassword));
        }
        return $response;
    }

    /**
     * Gets user information
     * 
     * @param String $userId User identifier
     * @return NULL|Array
     */
    public function readUser($userId, $profile = 'Usuario') {
        if ($profile === "Usuario") {
            return $this->executeCall($userId, "UserGetRequest18", "userId", false);
        } else if ($profile === "Cliente") {
            return $this->executeCall($userId, "ServiceProviderAdminGetRequest14", "userId", false);
        } else {
            return $this->executeCall($userId, "SystemAdminGetRequest", "userId", false);
        }
    }

    /**
     * Gets the list devices by user
     * 
     * @param String $serviceProviderId
     * @param String $groupId
     * @param String $userId
     * @return NULL|Array
     */
    public function getListDevices($serviceProviderId, $groupId, $userId) {
        $data = array(
            $serviceProviderId,
            $groupId,
            '1000',
            'searchCriteriaUserId' => array('Equal To', $userId, 'true')
        );
        $tags = array(
            'serviceProviderId',
            'groupId',
            'responseSizeLimit',
            'searchCriteriaUserId' => array('mode', 'value', 'isCaseInsensitive')
        );
        return $this->executeCall($data, "GroupEndpointGetListRequest", $tags, true);
    }

    /**
     * Updates an user
     * 
     * @param Array $data
     * @return Array|NULL
     */
    public function updateUser($data) {
        $paremeters = $this->prepareDataUser($data);
        return $this->executeCall($paremeters["data"], "UserModifyRequest17sp4", $paremeters["tags"], true);
    }

    /**
     * Prepares data of user to save
     * 
     * @param Array $data
     * @return Array
     */
    private function prepareDataUser($data) {
        $tags = array(
            'userId',
            'lastName',
            'firstName',
            'callingLineIdLastName',
            'callingLineIdFirstName',
            'nameDialingName',
            'phoneNumber',
            'extension',
            'callingLineIdPhoneNumber',
            'department' => array('serviceProviderId', 'groupId', 'name'),
            'language',
            'timeZone',
            'title',
            'pagerPhoneNumber',
            'mobilePhoneNumber',
            'emailAddress',
            'yahooId',
            'addressLocation',
            'address' => array('addressLine1', 'addressLine2', 'city', 'zipOrPostalCode')
        );
        return array("tags" => $tags, "data" => $this->normalizeData($tags, $data));
    }

    /**
     * Sets email to an user
     * 
     * @param String $email
     * @param String $userId
     * @param String $profile
     * @return Boolean|String
     */
    public function setEmail($email, $userId, $profile) {
       
        $result = true;
        //die($profile);
        if ($profile === "Usuario") {
            
            $userData = $this->readUser($userId);
            $error = $this->getErrorResponse();
             
            if ($error !== "" && $userData === null) {
                $result = $error;
            } else {
                $userData["userId"] = $userId;
                $userData["emailAddress"] = $email;
                
                $response = $this->updateUser($userData);
                $error = $this->getErrorResponse();
                if ($error !== "" && $response === null) {
                    $result = $error;
                }
            }
        } else {
            $this->setEmailByAdmin($userId, $email);
        }
        return $result;
    }

}
