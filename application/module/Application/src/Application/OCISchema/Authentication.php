<?php

namespace Application\OCISchema;

use Application\OCISchema\Base\BaseSchema;
use Application\OCISchema\Util\Cipher;

/**
 * Description of Connection
 *
 * @author Roman
 */
class Authentication extends BaseSchema {

    use Database\ProfileComplements;

    /**
     * Constructor
     * 
     * @param String $server WbeService´s URL
     */
    public function __construct($server, $db = null) {
        parent::__construct($server, $db);
    }

    /**
     * Broadsoft login (part 1)
     * 
     * Sends the user's id to Broadsoft. If the user is registreded the login 
     * continues, else thrown an exception.
     * 
     * @param string $userId a string with the user's id.
     * @param string $password a string with the user's password.
     *
     * @return object an object with the user's data for access to Broadsoft.
     * 
     * @access public
     */
    public function login($userId, $password) {
        $command = $this->simpleCommand($userId, 'AuthenticationRequest', 'userId');
        $this->appendCommand($command);
        $responseLogin = $this->call($this->saveXML());
        $this->response = $responseLogin->checkResponse()[0];
        return ($this->response['type']) ? $this->validateLogin($responseLogin, $userId, $password) : null;
    }

    /**
     * Broadsoft login (part 2)
     * 
     * Validates the user's id and the user's password.
     * 
     * @param object $response an object with the response of the server.
     * @param string $userId a strign with the user's id.
     * @param string $password a strign with the user's password.
     *
     * @return object an object with the user's data for access to Broafsoft.
     * 
     * @access private
     */
    private function validateLogin($responseLogin, $userId, $password) {
        $nonce = $responseLogin->xml->command[0]->nonce;
        $encryptedPassword = md5($nonce . ":" . sha1(Cipher::decrypt($password)));
        $credential = array($userId, $encryptedPassword);
        $tags = array('userId', 'signedPassword');
        $command = $this->complexCommand($credential, 'LoginRequest14sp4', $tags);
        $this->appendCommand($command);
        $xml = $this->saveXML();
        $responseValidateLogin = $this->call($xml);
        $this->response = $responseValidateLogin->checkResponse()[0];
        if ($this->response['type']) {
            $dataLogin = $responseValidateLogin->getArray($responseValidateLogin->xml->command[0]);
            return $this->getAdminData($dataLogin, $userId);
        } else {
            return null;
        }
    }

    /**
     * Gets the Login Type.
     *
     * Gets the user's login Type. There are three different login types: 
     * Provisioning, Service Provider and Group. The first one is for the NOC
     * profile, the second one is for the Client profile and the third party is
     * for the users created by the system.
     * 
     * @param object $response an object with the response of the server.
     * @param string $userId a string with the user's id.
     * @param string $password a string with the user's password.
     *
     * @return object	an object with the user's data for access to Broafsoft.
     * 
     * @access private
     */
    private function getAdminData($data, $userId) {
        switch ($data['loginType']) {
            case 'System':
                $command = $this->simpleCommand($userId, 'SystemAdminGetRequest', 'userId');
                $this->appendCommand($command);
                break;
            case 'Provisioning':
                $command = $this->simpleCommand($userId, 'SystemAdminGetRequest', 'userId');
                $this->appendCommand($command);
                break;
            case 'Service Provider':
                $command = $this->simpleCommand($userId, 'ServiceProviderAdminGetRequest14', 'userId');
                $this->appendCommand($command);
                break;
            case 'Group':
                $command = $this->simpleCommand($userId, 'GroupAdminGetRequest', 'userId');
                $this->appendCommand($command);
                break;
            case 'User':
                $command = $this->simpleCommand($userId, 'UserGetRequest18', 'userId');
                $this->appendCommand($command);
                break;
        }
        $response = $this->call($this->saveXML());
        $this->response = $response->checkResponse()[0];
        $dataLogin = $response->getArray($response->xml->command[0]);
        return $this->getProfile(array_merge($data, $dataLogin), $userId);
    }

    /**
     * Gets the full profile of user authenticated
     * 
     * @param Array $data
     * @param String $userId
     * @return Array
     */
    private function getProfile($data, $userId) {
        if ($this->db !== null) {
            $profile = $this->getAccess($userId);
            if (!empty($profile)) {
                if (isset($profile["token"])) {
                    $data["token"] = $profile["token"];
                }
                if ($profile["forceReset"] === true) {
                    $data["forceReset"] = $profile["forceReset"];
                }
                $data["profile"] = $profile["type"];
                $data["enterprises"] = $profile["enterprises"];
                if ($data["profile"] == "Cliente" || $data["profile"] == "Usuario") {
                    $data["enterprises"] = array($data["serviceProviderId"]);
                }
                if ($data["profile"] != "Usuario") {
                    $data["emailAddress"] = $profile["email"];
                }
            }else{
                $this->response["typeText"] = "c:ErrorResponse";
                $this->response["numberError"] = "";
                $this->response["summary"] = "Usuario sin privilegios asignados, favor de comunicarse con desarrollo@vsys.com";
                $data = null;
            }
        }
        return $data;
    }

}
