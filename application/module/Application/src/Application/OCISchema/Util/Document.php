<?php

namespace Application\OCISchema\Util;

/**
 * Methods to create an xml
 *
 * This file implements the required methods to create xml files for data 
 * exchange through SOAP protocol. All requests related to xml files are 
 * contained.
 *
 * PHP version 5
 *
 * @author		Alejandro Cedeño Quintero <acedeno@vsys.com>
 * @version		2.0
 * @modified            Carlos Román <croman@vsys.com> 
 */

use DOMDocument;

/**
 * Document class
 *
 * This class creates xml files for data exchange.
 * 
 * @author		Alejandro Cedeño Quintero <acedeno@vsys.com>
 * @version		1.0
 */

class Document {

    private $xml;
    
    /**
     * Constructor
     *
     * It's the first method called when an object is created
     *
     * @return object	an instance of the class
     *
     * @access public
     */
    public function __construct($id) {
        $this->resetXML($id);
    }

    /**
     * Appends a child node
     *
     * Appends a child node after the last child node of the specified element.
     *
     * @param object $command	a node represented as an object.
     * 
     * @return object	an object with the child node appended.
     *
     * @access public
     */
    public function appendCommand($command) {
        return $this->xml->BroadsoftDocument->appendChild($command);
    }

    /**
     * Builds an array
     * 
     * Builds an array (key-value) with empty strings instead null values.
     *
     * @param array $tags an array with tags of the required fields.
     * @param array $data an array with data for the required fields.
     * 
     * @return array $info an array with the entry arrays merged.
     *
     * @access private
     */
    private function buildArray($tags, $data) {
        $info = array();
        foreach ($tags as $key => $tag) {
            if (is_array($tag)) {
                $info[$key] = $this->buildArray($tag, $data[$key]);
            } else {
                $info[$tag] = isset($data[$key]) ? $data[$key] : '';
            }
        }
        return $info;
    }

    /**
     * Executes a request
     *
     * Creates an array with the tags and the data, then executes a command.
     *
     * @param array $data an array with entry data.
     * @param string $commandTag a string with the instruction for Broadsoft.
     * @param array $tags an array with the tags of required values.
     * 
     * @return object $command an object with the child node appended.
     *
     * @access protected
     */
    public function complexCommand($data, $commandTag, $tags) {
        $info = $this->buildArray($tags, $data);
        $command = $this->createCommand($commandTag);
        $command->appendChild($this->makeStructure($info));
        return $command;
    }

    /**
     * Creates a new element node
     *
     * Creates a new element node with a command to execute.
     *
     * @param strign $type a string with the type of response (success or error).
     * 
     * @return object $command an object with the attributes updated.
     *
     * @access protected
     */
    public function createCommand($type) {
        $command = $this->xml->createElement('command');
        $command->setAttribute('xsi:type', $type);
        $command->setAttribute('xmlns', '');
        $this->xmlSchema($command);
        return $command;
    }

    /**
     * Creates a Domain
     *
     * Appends a child node with the Domain value.
     *
     * @param strign $domain a string with the domain's value.
     * 
     * @return object $command an object with the child node appended.
     *
     * @access public
     */
    public function createDomain($domain) {
        $command = $this->createCommand('SystemDomainAddRequest');
        if ($domain != '')
            $command->appendChild($this->createElementEscape('domain', $domain));
        return $command;
    }

    /**
     * Appends a text node
     *
     * Creates the element, a new text node and append this node to the element.
     *
     * @param string $nameElement a string with the name of the new element.
     * @param strign $content a string with text for the node.
     * 
     * @return object $element	an object with the child node appended.
     *
     * @access protected
     */
    public function createElementEscape($nameElement, $content) {
        $element = $this->xml->createElement($nameElement);
        if ($content === 'null') {
            $element->setAttribute('xsi:nil', 'true');
        } else {
            $element->appendChild($this->xml->createTextNode($content));
        }
        return $element;
    }

    /**
     * Groups data from the same source
     *
     * Groups data (in object format) from the same source
     *
     * @param string $tag a string with the name of the object.
     * @param array $val an array with key-value pairs of data.
     * 
     * @return object $fragment an object with the current value.
     * @return boolean false if there is no value.
     *
     * @access public
     */
    public function elementsSameTag($tag, $val) {
        $fragment = $this->xml->createDocumentFragment();
        foreach ($val as $key => $value)
            $fragment->appendChild($this->createElementEscape($tag, $value));
        return ($fragment->childNodes->length > 0) ? $fragment : false;
    }

    /**
     * Gets the domain
     *
     * Builds the array, creates the command and append the child node.
     *
     * @param array $data an array with entry data.
     * 
     * @return object $command an object with the child node appended.
     *
     * @access public
     */
    public function getDomain($data) {
        $tags = array('serviceProviderId', 'groupId');
        $info = $this->buildArray($tags, $data);
        $command = $this->createCommand('GroupDomainGetAssignedListRequest');
        $command->appendChild($this->makeStructure($info));
        return $command;
    }

    /**
     * Creates an xml structure
     *
     * It's a recursive function that creates an xml structure starting with an
     * array.
     *
     * @param array $data	an array (key, value) with entry data.
     * 
     * @return object $elements	an object with the child node appended.
     *
     * @access protected
     */
    public function makeStructure($data) {
        $elements = $this->xml->createDocumentFragment();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $newElements = $this->makeStructure($value);
                $this->makeStructure2($elements, $newElements, $key);
            } else {
                $this->makeStructure3($key, $value, $elements);
            }
        }
        return $elements;
    }

    /**
     * Creates an xml structure (part 2)
     *
     * Appends child nodes.
     *
     * @param object $elements an xml partial structure.
     * @param object $newElements an xml partial structure.
     * @param string $key a string with the name for the node.
     * 
     * @return object $elements	an object with the child node appended.
     *
     * @access private
     */
    private function makeStructure2($elements, $newElements, $key) {
        if ($newElements->childNodes->length > 0) {
            $newNode = $this->xml->createElement($key);
            $newNode->appendChild($newElements);
            $elements->appendChild($newNode);
        }
        return $elements;
    }

    /**
     * Creates an xml structure (part 3)
     *
     * Appends the child node if it's not an empty string.
     *
     * @param string $key a string with the name for the node.
     * @param string $value a string with the value for the node.
     * @param object $elements an xml partial structure.
     * 
     * @return object $elements	an object with the child node appended.
     *
     * @access private
     */
    private function makeStructure3($key, $value, $elements) {
        if ($value != '')
            $elements->appendChild($this->createElementEscape($key, $value));
        return $elements;
    }

    /**
     * Creates an xml structure
     *
     * Appends the child node if it's not an empty string.
     *
     * @param string $entry a string with the value for the node
     * @param string $commandTag a string with the command for the xml
     * @param string $elementTag a strign with the tag for the node
     * 
     * @return object $command	an object with the child node appended.
     *
     * @access protected
     */
    public function simpleCommand($entry, $commandTag, $elementTag) {
        $command = $this->createCommand($commandTag);
        $this->makeStructure3($elementTag, $entry, $command);
        return $command;
    }

    /**
     * Sets the XML Schema
     *
     * Sets the XML Schema according to the recommendation of W3C.
     *
     * @param object $obj an object with the current document.
     * 
     * @return object	an object with the new attribute configured.
     *
     * @access private
     */
    private function xmlSchema($obj) {
        return $obj->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    }
    
    /**
     * Gets the XML Document created
     * 
     * @return String
     */
    public function saveXML(){
        return $this->xml->saveXML();
    }    
    
    /**
     * Creates a new XML Document
     * 
     * @param String $id
     */
    public function resetXML($id){
        
        if(!is_null($this->xml)){
            $this->xml=null;
        }
        
        $this->xml=new DOMDocument('1.0', 'UTF-8');

        $this->xml->BroadsoftDocument = $this->xml->createElement('BroadsoftDocument');
        $this->xml->BroadsoftDocument->setAttribute('xmlns', 'C');
        $this->xml->BroadsoftDocument->setAttribute('protocol', 'OCI');

        $this->xmlSchema($this->xml->BroadsoftDocument);
        $this->xml->sessionId = $this->xml->createElement('sessionId');
        $this->xml->sessionId->setAttribute('xmlns', '');
        $this->xml->appendChild($this->xml->BroadsoftDocument);
        $this->xml->sessionId->nodeValue = $id;
        $this->xml->BroadsoftDocument->appendChild($this->xml->sessionId);
    }

}