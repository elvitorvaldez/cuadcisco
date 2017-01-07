<?php

namespace Application\Controller;

use Zend\View\Model\JsonModel;
use Application\Form\Report;

/**
 * Description of ReportsController
 *
 * @author Roman
 */
class ReportsController extends BaseControllerAction {

    /**
     * Get a JsonModel of URL (Excel File)
     * 
     * @return JsonModel URL of Excel File
     */
    public function generateReportAction() {
        if ($this->getRequest()->isPost()) {
            \ini_set('memory_limit', '-1');
            \ini_set('post_max_size', '100M');
            \ini_set('upload_max_filesize', '100M');
            \set_time_limit(300);
            $result = $this->validateFormReport((array) $this->getRequest()->getPost());
            return new JsonModel($result);
        }
    }

    /**
     * Validates an Form/Report
     * 
     * @param Array $data
     * @return Array
     */
    private function validateFormReport($data) {
        $this->getSessionService();
        $result = array(
            "success" => false,
            "message" => "Ocurrió un error inesperado"
        );
        $route = "ReportsController/generateReportAction";
        try {
            $report = new Report($data);
            $resultValidation = $report->isValid();
            if ($resultValidation === true) {
                $response = $report->getExcel();
                if ($response["success"] === true) {
                    $result["success"] = $response["success"];
                    $result["url"] = $response["url"];
                    $this->generateLog($route, "event", "GENERATE_REPORT", $this->auth->userId, $response["url"]);
                } else {
                    $this->generateLog($route, "error", "GENERATE_REPORT_ERROR", $this->auth->userId, null);
                }
            } else {
                $result["validation"] = $resultValidation;
                $this->generateLog($route, "error", "VALIDATION_FAILS", null, null);
            }
        } catch (\Exception $ex) {
            $this->generateLog($route, "error", "SYSTEM_ERROR", null, $ex->getMessage());
        }
        return $result;
    }

}