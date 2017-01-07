<?php

namespace Application\OCISchema\Util;

/**
 * Description of Reader
 *
 * @author Roman
 */
class Reader {

    /**
     * Constants required to check the response of a requested command.
     */
    private $ER = 'c:ErrorResponse';
    private $SR = 'c:SuccessResponse';

    /**
     * Object SimpleXMLElement
     */
    public $xml;

    /**
     * Constructor
     *
     * Creates an SimpleXMLElement object.
     *
     * @param string $str	an string with an XML to read.
     *
     * @return object	an object with an XML created.
     * 
     * @access public
     */
    public function __construct($str) {
        $this->xml = new \SimpleXMLElement($str, LIBXML_NOWARNING);
    }

    /**
     * Checks the response from the server
     *
     * Checks and saves in an array the response from the server.
     *
     * @return array that contains info about commands.
     * 
     * @access public
     */
    public function checkResponse() {
        $xml = $this->xml;
        $response = array();
        $commands = $xml->command;
        foreach ($commands as $command) {
            $attr = $command->attributes('xsi', true);
            $type = ($attr == $this->SR || $attr != $this->ER) ? true : false;
            $response[] = array(
                'type' => (boolean) $type,
                'typeText' => (string) $attr,
                'summary' => (string) $command->summary,
                'detail' => (string) $command->detail,
                'numberError' => $this->getNumberError((string) $command->summary)
            );
        }
        return $response;
    }

    /**
     * Extracts the number error of a message
     * 
     * @param String $error
     * @return int
     */
    private function getNumberError($error) {
        $regex = '/^(\[)(Error)(\s)([\d]{1,4})(\])/';
        $number = null;
        $matches = array();
        \preg_match_all($regex, $error, $matches);
        if (count($matches) > 0) {
            if (!empty($matches[4])) {
                $number = (int) \implode(",", $matches[4]);
            }
        }
        return $number;
    }

    /**
     * Gets the array
     *
     * Converts an object to an array.
     *
     * @param object $xmlObject an object of SimpleXMLElement
     *
     * @return array an object converted in an array.
     * 
     * @access public
     */
    public function getArray($xmlObject = null) {
        if ($xmlObject == null){
            $xmlObject = $this->xml;
        }
        $data = array();
        $data = ( \is_object($xmlObject) ) ? \get_object_vars($xmlObject) : $xmlObject;
        foreach ($data as $key => $value) {
            if($key!="@attributes"){
                if ( (\is_object($value) || \is_array($value))){
                    if($key==="row"){
                        $data[$key] = $this->getRows($value);
                    } else {
                        $data[$key] = $this->getArray($value);
                    }
                }
            }else{
                unset($data[$key]);
            }
        }
        if(count($data)==0){
            $data="";
        }
        return $data;
    }
    
    /**
     * Gets an array of rows
     * 
     * @param Array $rows
     * @return Array
     */
    private function getRows($rows){
        $temp=array();
        if(!isset($rows->col)){
            foreach($rows as $r){
                $temp[] = $this->getRow($r);
            }
        }else{
            $temp[] = $this->getRow($rows);
        }
        return $temp;
    }
    
    /**
     * Gets a normalized data of a row
     * 
     * @param ArrayObject $row
     * @return Array
     */
    private function getRow($row) {
        $r = null;
        if (is_array($row)) {
            if (!empty($row["col"])) {
                $r = $row["col"];
            } else {
                $r = $row;
            }
        } else if (isset($row->col) && !empty($row->col)) {
            $r = (array) $row->col;
        } else if (is_object($row)) {
            $r = (array) $row;
        } else {
            $r = $row;
        }
        if (is_array($r)) {
            if (array_keys($r) !== range(0, count($r) - 1)) {
                $tempArray = array();
                foreach ($r as $k => $v) {
                    if ($k !== "@attributes") {
                        $tempArray[$k] = $this->normalizeValue($v);
                        if (\is_array($tempArray[$k])) {
                            if (array_keys($tempArray[$k]) !== range(0, count($tempArray[$k]) - 1)) {
                                $tempArray[$k] = $this->getRow($tempArray[$k]);
                            }
                        }
                    }
                }
                $r = $tempArray;
            } else {
                for ($x = 0; $x < count($r); $x++) {
                    $r[$x] = $this->normalizeValue($r[$x]);
                    if (\is_array($r[$x])) {
                        $r[$x] = $this->row($r[$x]);
                    }
                }
            }
        }
        return $r;
    }

    /**
     * Gets the true value and validate if an object or array is empty
     * 
     * @param StringArrayObject $v
     * @return StringArrayObject
     */
    private function normalizeValue($v) {
        $result = null;
        if (is_object($v)) {
            $result = (array) $v;
        } else {
            $result = $v;
        }
        if (\is_array($result) && empty($result)) {
            $result = "";
        } else {
            $result = $result;
        }
        return $result;
    }
    
}