<?php

namespace Application\Form;

use Application\Form\Base\FormAdapter;
use Application\Util\ExportExcel;
use Application\Util\Validator;

/**
 * Description of Report
 *
 * @author Roman
 */
class Report extends FormAdapter {

    public $creator;
    public $owner;
    public $subject;
    public $filename;
    public $dataset;
    public $columns;
    public $position_title;
    public $content_title;
    public $title_sheet;
    public $sheet_count;

    /**
     * Construct of Report
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
            array("name" => "creator", "rules" => array("required")),
            array("name" => "owner", "rules" => array("required")),
            array("name" => "subject", "rules" => array("required")),
            array("name" => "filename", "rules" => array("required")),
            array("name" => "dataset", "rules" => array("required")),
            array("name" => "columns", "rules" => array("required")),
            array("name" => "position_title", "rules" => array("required")),
            array("name" => "content_title", "rules" => array("required")),
            array("name" => "title_sheet", "rules" => array("required")),
            array("name" => "sheet_count", "rules" => array("required"))
                )
        );
        if ($resultValidation === false) {
            return true;
        } else {
            return $resultValidation;
        }
    }

    /**
     * Gets URL info of current data
     * 
     * @return Array
     */
    public function getExcel() {
        $exportExcel = new ExportExcel($this->getData());
        return $exportExcel->generateReport();
    }

}
