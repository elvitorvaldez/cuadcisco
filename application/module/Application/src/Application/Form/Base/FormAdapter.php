<?php

namespace Application\Form\Base;

/**
 * Description of FormAdapter
 *
 * @author Roman
 */
abstract class FormAdapter implements FormInterface{

    /**
     * Abstract method to validate the model in each children 
     */
    abstract protected function isValid();

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
    
}
