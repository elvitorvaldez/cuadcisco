<?php

namespace Application\Form\Base;

/**
 * Description of Form
 *
 * @author Roman
 */
interface FormInterface {
    
    /**
     * Method defined for save data in a model
     * 
     * @param Array $data
     */
    public function setData($data);
    
    /**
     * Method defined for get data of a model
     * 
     */
    public function getData();
    
}
