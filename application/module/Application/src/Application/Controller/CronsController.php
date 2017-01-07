<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Application\Util\Logger;

/**
 * Description of CronsController
 *
 * @author Roman
 */
class CronsController extends AbstractActionController {

    //Params
    protected $token = null;
    protected $db = null;
    protected $dm = null;
    protected $logger = null;
    protected $time_start = 0;

    /**
     * Default action to call the "REMOVE_XLSX" cron
     */
    public function removeXLSXAction() {
        $this->logger = Logger::getInstance();
        $this->logger->setRoute("CronsController/removeXLSXAction");
        $this->executeCron("REMOVE_XLSX");
    }

    /**
     * Default action to call the "REMOVE_XLSX" cron
     */
    public function cleanRequestOfResetPasswordAction() {
        $this->logger = Logger::getInstance();
        $this->logger->setRoute("CronsController/cleanRequestOfResetPasswordAction");
        $this->executeCron("CLEAN_RESET_PWD");
    }

    /**
     * Default action to call the "SEND_RESET_PWD" cron
     */
    public function sendNotifyOfResetPasswordAction() {
        $this->logger = Logger::getInstance();
        $this->logger->setRoute("CronsController/cleanRequestOfResetPasswordAction");
        $this->executeCron("SEND_RESET_PWD");
    }

    /**
     * Executes the specific cron job (RESET_LOGS, REMOVE_XLSX)
     * 
     * @param String $cron
     * @return String
     * @throws \RuntimeException
     */
    private function executeCron($cron) {
        $this->time_start = microtime(true);
        $total_time = 0;
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            $this->logger->setMessage("error", $cron . "_ERROR");
            $this->logger->setComment($log->getMessageError("ONLY_CONSOE"));
            throw new \RuntimeException('You can only use this action from a console!');
        }
        $clientToken = $request->getParam('token', null);
        $this->getToken();
        if ($clientToken === $this->token) {
            switch ($cron) {
                case "REMOVE_XLSX":
                    $total_time = $this->removeXLSXCron();
                    break;
                case "CLEAN_RESET_PWD":
                    $total_time = $this->cleanRequestOfResetPasswordCron();
                    break;
                case "SEND_RESET_PWD":
                    $total_time = $this->sendNotifyOfResetPasswordCron();
                    break;
                default:
                    echo "TASK UNDEFINED";
                    break;
            }
        } else {
            $this->logger->setMessage("error", $cron . "_ERROR");
            $this->logger->setComment("El token no es válido.");
            echo "Operation can not is allowed" . PHP_EOL;
        }
        if ($total_time === 0) {
            $total_time = (microtime(true) - $this->time_start);
        }
        $this->logger->createLog($this->getMongoService());
        return 'Time of Execution: ' . $total_time;
    }

    /**
     * Removes the XLSX´s created
     * 
     * @return int
     */
    private function removeXLSXCron() {
        $total_time = 0;
        echo "Initializing Task of Removing XLSX Files" . PHP_EOL;
        $path = getcwd() . "/../";
        $path = str_replace("\\", "/", $path);
        echo $path;
        try {
            $files = scandir($path);
            foreach ($files as $f) {
                $regex = "/\\.(xlsx)$/i";
                if (\preg_match($regex, $f) != false) {
                    echo "Deleting " . $f . PHP_EOL;
                    \unlink($path . $f);
                }
            }
            $total_time = (microtime(true) - $this->time_start);
            $this->logger->setMessage("event", "REMOVE_XLSX_OK");
            $this->logger->setComment("Tiempo de Ejecución: " . $total_time . " segundos.");
            echo "Task finished successfully...!!" . PHP_EOL;
        } catch (\Exception $ex) {
            $this->logger->setMessage("error", "REMOVE_XLSX_ERROR");
            $this->logger->setComment($ex->getMessage());
            echo "Error to remove xlsx files" . PHP_EOL;
            var_dump($ex->getMessage());
        }
        return $total_time;
    }

    /**
     * Removes the resquest of reset password
     * 
     * @return int
     */
    private function cleanRequestOfResetPasswordCron() {
        $total_time = 0;
        echo "Initializing Task of Cleaning Request of Reset Password" . PHP_EOL;
        try {
            $this->getDatabaseService();
            $sql = "DELETE FROM resetpassword WHERE DATEDIFF(NOW(), date)>1";
            $statement = $this->db->createStatement($sql);
            $statement->execute();
            $total_time = (microtime(true) - $this->time_start);
            $this->logger->setMessage("event", "CLEAN_RESET_PWD_OK");
            $this->logger->setComment("Tiempo de Ejecución: " . $total_time . " segundos.");
            echo "Task finished successfully...!!" . PHP_EOL;
        } catch (\Exception $ex) {
            $this->logger->setMessage("error", "CLEAN_RESET_PWD_ERROR");
            $this->logger->setComment($ex->getMessage());
            echo "Error to remove xlsx files" . PHP_EOL;
            var_dump($ex->getMessage());
        }
        return $total_time;
    }

    /**
     * Sends the notify of reset password
     * 
     * @return int
     */
    private function sendNotifyOfResetPasswordCron() {
        $total_time = 0;
        echo "Initializing Task of Sending Notification of Reset Password" . PHP_EOL;
        try {
            $this->getDatabaseService();
            $sql = "SELECT profile.id as id, profile.userId, profile.email as email," .
                    " roles.rol FROM profile INNER JOIN roles ON roles.id=profile.type " .
                    "WHERE DATEDIFF(NOW(), profile.updatedAt)>90";
            $statement = $this->db->createStatement($sql);
            $result = $statement->execute();
            $resetPassword = null;
            foreach ($result as $r) {
                if ($resetPassword === null) {
                    $resetPassword = $this->getServiceLocator()->get('Application\Service\ResetPassword');
                }
                $resetPassword->setData(array("userId" => $r["userId"]));
                if ($resetPassword->isValid() === true) {
                    $resetPassword->resetPassword(true);
                }
                $resetPassword->userId = null;
                $resetPassword->token = null;
            }
            $total_time = (microtime(true) - $this->time_start);
            $this->logger->setMessage("event", "SEND_RESET_PWD_OK");
            $this->logger->setComment("Tiempo de Ejecución: " . $total_time . " segundos.");
            echo "Task finished successfully...!!" . PHP_EOL;
        } catch (\Exception $ex) {
            $this->logger->setMessage("error", "SEND_RESET_PWD_ERROR");
            $this->logger->setComment($ex->getMessage());
            echo "Error to remove xlsx files" . PHP_EOL;
            var_dump($ex->getMessage());
        }
        return $total_time;
    }

    /**
     * Gets a token to validate request from console
     * 
     */
    private function getToken() {
        if ($this->token === null) {
            $serviceLocator = $this->getServiceLocator();
            $config = $serviceLocator->get('Config');
            $this->token = $config["token"];
        }
    }

    /**
     * Gets the instance of Zend\Db\Adapter\Adapter
     * 
     * @return Zend\Db\Adapter\Adapter
     */
    private function getDatabaseService() {
        if ($this->db === null) {
            $this->db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        }
        return $this->db;
    }
    
    /**
     * Get Services of Module.php (Mongo - Doctrine)
     * 
     * @return Doctrine\ODM\MongoDB\DocumentManager
     */
    private function getMongoService() {
        if ($this->dm === null) {
            $this->dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        }
        return $this->dm;
    }

}
