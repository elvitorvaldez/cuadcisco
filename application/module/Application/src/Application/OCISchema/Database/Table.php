<?php

namespace Application\OCISchema\Database;

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

use Application\Util\Logger;

/**
 * Table trait
 *
 * This file implements the 'table data gateway' dessign pattern who 
 * encapsulates data comunication between the system and a specific database
 * table. All requests related to the 'table data gateway' are contained.
 * 
 * PHP version 5
 *
 * @author        Alejandro Cedeño Quintero <acedeno@vsys.com>
 * @version        2.1
 * @modified            Carlos Román <croman@vsys.com> 
 */
trait Table {

    private $mongoConfig = array(
        "remote" => array(
            "server" => array(
                "host" => "IP_Mongo_Global.php",
                "port" => "Puerto_Mongo_Global.php",
                "user" => "Usuario_Mongo_Global.php",
                "pass" => "Contraseña_Mongo_Global.php",
                "db" => "Nombre_DB_Global.php"
            ),
            "proxy" => array(
                "name" => "DoctrineMongoODMModule\Proxy",
                "dir" => "data/DoctrineMongoODMModule/Proxy"
            ),
            "hydrator" => array(
                "name" => "DoctrineMongoODMModule\Hydrator",
                "dir" => "data/DoctrineMongoODMModule/Hydrator"
            )
        ),
        "local" => array(
            "server" => array(
                "host" => "IP_Mongo_Local.php",
                "port" => "Puerto_Mongo_Local.php",
                "user" => "Usuario_Mongo_Local.php",
                "pass" => "Contraseña_Mongo_Local.php",
                "db" => "Nombre_DB_Local.php"
            ),
            "proxy" => array(
                "name" => "DoctrineMongoODMModule\Proxy",
                "dir" => "data/DoctrineMongoODMModule/Proxy"
            ),
            "hydrator" => array(
                "name" => "DoctrineMongoODMModule\Hydrator",
                "dir" => "data/DoctrineMongoODMModule/Hydrator"
            )
        )
    );
    
    /**
     * Executes a MySQL query
     *
     * Prepares and executes MySQL queries.
     * 
     * @param string $sql a string with a sql statement.
     * @param array $data an optional array with parameters for the query.
     *
     * @return object $statement    an object with the result of the query.
     *
     * @access public
     */
    protected function makeStatement($sql, $data = NULL) {
        $result = null;
        $statement = $this->db->createStatement($sql);
        try {
            $result = $statement->execute($data);
        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->setRoute("Application\OCISchema\Database\Table");
            $logger->setMessage("error", "DATABASE_ERROR");
            $logger->setComment($e->getMessage());
            $logger->createLog($this->getODM());
        }
        return $result;
    }
    
    /**
     * Gets the object to interact with MongoDB
     * 
     * @return Doctrine\ODM\MongoDB\DocumentManager
     */
    private function getODM(){
        AnnotationDriver::registerAnnotationClasses();
        $temp = $this->mongoConfig["local"]; //Modifcar si quieres establecer una conexión local o remota (local|remote)
        $config = new Configuration();
        $config->setProxyDir($temp["proxy"]["dir"]);
        $config->setProxyNamespace($temp["proxy"]["name"]);
        $config->setHydratorDir($temp["hydrator"]["dir"]);
        $config->setHydratorNamespace($temp["hydrator"]["name"]);
        $config->setDefaultDB($temp["server"]["db"]);
        $config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__.'/../../Document'));
        return DocumentManager::create(new Connection($this->getServer($temp["server"])), $config);
    }
    
    /**
     * Gets the connection string to establish the connection
     * 
     * @param String $server
     * @return String
     */
    private function getServer($server){
        $str = "mongodb://";
        if( isset($server["user"]) && $server["user"] !== null &&
            isset($server["pass"]) && $server["pass"] !== null ){
            $str .= $server["user"].":".$server["pass"]."@";
        }
        $str.=$server["host"].":".$server["port"]."/".$server["db"];
        return $str;
    }

}
