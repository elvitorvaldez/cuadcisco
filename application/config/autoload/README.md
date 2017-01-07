Como Generar las Configuraciones:
=====================

Por defecto esta aplicación esta configurada para leer las configuraciones globales y locales. Al tener dos configuraciones en global y local, la aplicación reescribe la configuracion global por la local. 

A continuación describiremos como crear el archivo local para un entorno de desarrollo ya que por defecto esta configurada para ejecutarse en un ambiente productivo.

**Configuración de la Aplicación**

```php
<?php
    return array(
        'server' => array(
            'url' => 'https://cuad1.telmex.com/ (Productivo) o http://10.1.1.158/' (Laboratorio),
            'wsdl' => 'webservice/services/ProvisioningService?wsdl'
        ),
        'db' => array(
            'driver' => 'Pdo',
            'dsn' => 'mysql:dbname=Nombre_DB_MySQL;host=IP_MySQL',
            'username' => 'Usuario_MYSQL',
            'password' => 'Contraeña_MySQL'
        ),
        'auth' => array( //Dependiendo del XSP si es laboratorio o productivo las credenciales van a variar
            //XSP Laboratorio:
            "provising" => array( //Cuenta Aprovisionador
                'user' => 'Spk1MAl84gQXRTLGgtXuLS1kr1TU[hCWws1TfLznTHw=',
                'pass' => 'bze31AdUch2I[A[9GlJl00r39lqVix4LCoVpWP6f3cI='
            ),
            "system" => array( //Cuenta de Sistema
                'user' => 'Spk1MAl84gQXRTLGgtXuLS1kr1TU[hCWws1TfLznTHw=',
                'pass' => 'bze31AdUch2I[A[9GlJl00r39lqVix4LCoVpWP6f3cI='
            )
            //XSP Productivo:
            "provising" => array(
                'user' => 'uS}X95C4OcotEnWagozLyTHAKA4J6XuaIxn9Ucssk[4=',
                'pass' => 'zzd65Vb6HAl[5ww7b17JO[caivaAlHbOx1oiqlPvWRw='
            ),
            "system" => array(
                'user' => 'ThAKr1ORtYLjDkos4hsiYDlT55pZirN3hdDSv9yiq6o=',
                'pass' => 'c3esxT6[p543ONFqnPCmLSMPhDVfendIixcTVJ4QiD8='
            )
        ),
        'token' => "2RVsY2PKBun36InIVI6Q" //Modificar para ejecutar comandos de 
            // consola de manera mas rápida ejemplo dejarlo con un valor de '1'
            // ya que es mucho mas sencillo aprenderlo.
    );
?>
```

Como observacion es recomendable nombrar la base de datos o los namespace con un prefijo `_lab` si se esta utilizando el XSP de laboratorio.

**Configuración de Conexion a Mongo**

```php
<?php
return array(
    'doctrine' => array(
        'connection' => array(
            'odm_default' => array(
                'server'           => 'IP_Mongo',
                'port'             => 'Puerto_Mongo',
                'connectionString' => 'Connection_Strin_Mongo',
                'user'             => 'Usuario_Mongo',
                'password'         => 'Contraseña_Mongo',
                'dbname'           => 'logs',
                'options'          => array()
            ),
        ),
        'configuration' => array(
            'odm_default' => array(
                //'metadata_cache'     => 'array',
                'driver'             => 'odm_default',
                'generate_proxies'   => true,
                'proxy_dir'          => 'data/DoctrineMongoODMModule/Proxy',
                'proxy_namespace'    => 'DoctrineMongoODMModule\Proxy',
                'generate_hydrators' => true,
                'hydrator_dir'       => 'data/DoctrineMongoODMModule/Hydrator',
                'hydrator_namespace' => 'DoctrineMongoODMModule\Hydrator',
                //'default_db'         => test,
                //'filters'            => array(),  // array('filterName' => 'BSON\Filter\Class'),
                //'logger'             => null // 'DoctrineMongoODMModule\Logging\DebugStack'
            )s
        ),
        'driver' => array(
            'odm_driver' => array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => array(__DIR__ . '/../../module/Application/src/Application/Document')
            ),
            'odm_default' => array(
                'drivers' => array(
                    'Application\Document' => 'odm_driver'
                )
            ),
        ),
        'documentmanager' => array(
            'odm_default' => array(
                //'connection'    => 'odm_default',
                //'configuration' => 'odm_default',
                //'eventmanager' => 'odm_default'
            )
        ),
        'eventmanager' => array(
            'odm_default' => array(
                'subscribers' => array()
            )
        ),
    ),
);
?>
```

Tambien es necesario modificar el archivo `gestion_cuad/application/module/Application/src/Application/Api/Database/Table.php` ([Enlace](http://10.1.20.51:8000/Developers/gestion_cuad/blob/master/application/module/Application/src/Application/Api/Database/Table.php)) con las nuevas credenciales de Mongo.

```php
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
 * @author		Alejandro Cedeño Quintero <acedeno@vsys.com>
 * @version		2.1
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
     * @return object $statement	an object with the result of the query.
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
```
