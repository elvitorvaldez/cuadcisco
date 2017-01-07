<?php

namespace Application\Document\Base;

/**
 * Description of DocumentInterface
 *
 * @author Roman
 */
interface DocumentInterface {
    
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
