<?php

namespace Application\Document\Base;

/**
 * Description of FormAdapter
 *
 * @author Roman
 */
abstract class DocumentAdapter implements DocumentInterface{
    
    /**
     * Sets the values to current model
     * 
     * @param Array $data
     */
    public function setData($data){
        foreach ($data as $key => $value){
            if(\property_exists(get_class($this), $key)){
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Gets the valus of current model
     * 
     * @return Array
     */
    public function getData() {
        $temp = array();
        $class_vars = get_class_vars(get_class($this));
        foreach ($class_vars as $name => $value) {
            $temp[$name] =  $this->$name;
        }
        return $temp;
    }
    
    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) 
    {
        return $this->$property;
    }
  
    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) 
    {
        $this->$property = $value;
    }
    
}
