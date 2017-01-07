<?php

namespace Application\OCISchema\Base;

use SoapClient;
use Application\OCISchema\Util\Document;
use Application\OCISchema\Util\Reader;
use Application\OCISchema\Util\Error;

/**
 * Description of BaseSchema
 *
 * @author Roman
 */
class BaseSchema extends Document {

    protected $client;
    protected $sessionId;
    protected $response;
    protected $db;

    /**
     * Constructor
     *
     * It's the first method called when an object is created. Gets the 
     * connection and the session id. Required values.
     * 
     * @param string $server a string with the server IP address.
     *
     * @return object an object with the session id.
     * 
     * @access public
     */
    public function __construct($server, $db = null) {
        $params = array(
            'trace' => 1,
            'exceptions' => true
        );
        $this->client = new SoapClient($server, $params);
        $this->sessionId = $this->pseudoRandomPassword(30);
        if ($db !== null) {
            $this->db = $db;
        }
        parent::__construct($this->sessionId);
    }

    /**
     * Constructor (2)
     *
     * It's the first method called when an object is created. Injects the 
     * connection and the session id. Required values.
     * 
     * @param SoapClient $client a soap client connected.
     * @param string $sessionId a session id genereated.
     *
     * @return object an object with the session id.
     * 
     * @access public
     */
    public function __construct2($client, $sessionId, $db = null) {
        $this->client = $client;
        $this->sessionId = $sessionId;
        if ($db !== null) {
            $this->db = $db;
        }
        parent::__construct($this->sessionId);
    }

    /**
     * Gets the server's response
     *
     * Makes requests to the server through SOAP.
     *
     * @param string $strXML an string with the xml.
     * 
     * @return string $response the string with the server response.
     *
     * @access public
     */
    public function call($strXML) {
        try {
            $params = array('in0' => $strXML);
            $response = $this->client->processOCIMessage($params);
            $this->resetXML($this->sessionId);
        } catch (\Exception $e) {
            throw new \Exception('SOAP ERROR ' . $e, 1);
        }
        $str = (String) $response->processOCIMessageReturn;
        return new Reader(utf8_decode($str));
    }

    /**
     * Executes the custom funcion to XSP
     * 
     * @param Array|String $data
     * @param String $func
     * @param Array|String $tags
     * @param Boolean $is_complex
     * @return Null|Array
     */
    public function executeCall($data, $func, $tags, $is_complex = false) {
        if ($is_complex === true) {
            $command = $this->complexCommand($data, $func, $tags);
        } else {
            $command = $this->simpleCommand($data, $func, $tags);
        }
        $this->appendCommand($command);
        $xml = $this->saveXML();
        if (isset($tags["department"])) {
            if ($data["department"][0] == "null" &&
                    $data["department"][1] == "null" &&
                    $data["department"][0] == "null"
            ) {
                $xml = \str_replace('<department><serviceProviderId xsi:nil="true"/><groupId xsi:nil="true"/><name xsi:nil="true"/></department>', '<department xsi:type="GroupDepartmentKey" xsi:nil="true" />', $xml);
            } else {
                $xml = \str_replace('<department>', '<department xsi:type="GroupDepartmentKey">', $xml);
            }
        }
        $response = $this->call($xml);
        $this->response = $response->checkResponse()[0];
        if ($this->response["type"]) {
            return $response->getArray($response->xml->command);
        } else {
            return null;
        }
    }

    /**
     * Normalizes the values conforming the tags
     * 
     * @param Array $tags
     * @param Array $data
     * @param Boolean $is_assoc
     * @return Array
     */
    public function normalizeData($tags, $data, $is_assoc = false) {
        $temp = array();
        foreach ($tags as $key => $value) {
            if (is_array($value)) {
                $temp[$key] = $this->normalizeData($value, (isset($data[$key]) ? $data[$key] : array()), $is_assoc);
            } else {
                $val = null;
                if (!isset($data[$value]) || $data[$value] == "") {
                    $val = "null";
                } else {
                    $val = $data[$value];
                }
                if ($is_assoc === true) {
                    $temp[$value] = $val;
                } else {
                    $temp[] = $val;
                }
            }
        }
        return $temp;
    }

    /**
     *
     * Throws an exception if the type is not defined.
     * 
     * @param object $check the object with the response from the server.
     *
     * @return object an object with the user's data for access to Broafsoft.
     * 
     * @access public
     */
    public function customException($check) {
        throw new \Exception($check['summary'], 1);
    }

    /**
     * Gets the soap client
     * 
     * @return SoapClient
     */
    public function getSoapClient() {
        return $this->client;
    }

    /**
     * Gets the current session id
     * 
     * @return String
     */
    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * Sets the soap client
     * 
     * @param SoapClient $client
     * @return SoapClient
     */
    public function setSoapClient($client) {
        return $this->client = $client;
    }

    /**
     * Sets the current session id
     * 
     * @param String $id
     * @return String
     */
    public function setSessionId($id) {
        return $this->sessionId = $id;
    }

    /**
     * Sets the database adapter
     * 
     * @param Zend\Db\Adapter\Adapter $db
     */
    public function setDatabaseAdapter($db) {
        $this->db = $db;
    }

    /**
     * Gets the current error
     * 
     * @return String
     */
    public function getErrorResponse() {
        $error = "";
        if ($this->response["typeText"] === "c:ErrorResponse") {
            $error = Error::getMessageError($this->response["numberError"]);
            if ($error === "") {
                if($this->response["numberError"] === ""){
                    $error = $this->response["summary"];
                }else{
                    $error = \str_replace("[Error " . $this->response["numberError"] . "] ", "", $this->response["summary"]);
                }
            }
        }
        return $error;
    }

    /**
     * Gets a pseudo random password
     * 
     * Gets a pseudo random password using the same library as OpenSSL. It
     * generates N bytes and convert them in base 64.
     *
     * @param integer $length an integer for the number of bytes that will be
     * 	generated.
     *
     * @return string a string with the password generated
     * 
     * @access private
     */
    private function pseudoRandomPassword($length) {
        /* CSTRONG devuelve TRUE si el metodo fue criptograficamente fuerte */
        return base64_encode(openssl_random_pseudo_bytes($length, $cstrong));
    }

}
