<?php

namespace Application\Util;

use Application\Document\Log;

/**
 * Description of Logger
 *
 * @author Roman
 */
class Logger {

    private static $instance;
    //Initilizes vars to use in this class
    private $route = "";
    private $message = "";
    private $tag = "";
    private $comment = "";

    private function __construct() {
        //Empty
    }

    /**
     * Returns Instance of Log
     * 
     * @return Log Return an Instance
     */
    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Sets route to current log
     * 
     * @param string $route Name of controller and action
     */
    public function setRoute($route) {
        $this->route = $route;
    }

    /**
     * Sets message to current log
     * 
     * @param string $tag Kind of log (event, error, loads, intercel)
     * @param string $key Kind of message
     * @param string|Array $value Its optional
     */
    public function setMessage($tag, $key, $value = null) {
        $this->tag = $tag;
        $this->message = $this->getMessageStatus($key, $value);
    }

    /**
     * Sets comment to current log
     * 
     * @param string $comment Text to save
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }

    /**
     * Saves current log
     */
    public function createLog($dm) {
        $log = new Log();
        $agent = $this->getAgent();
        $data = array(
            "timestamp" => new \MongoDate(strtotime($this->getTimestamp())),
            "kind" => $this->tag,
            "ip" => $this->getIp(),
            "browser" => $agent["browser"],
            "version" => $agent["version"],
            "os" => $agent["os"],
            "request" => $this->route,
            "description" => $this->message,
            "comment" => $this->comment
        );
        $log->setData($data);
        $dm->persist($log);
        $dm->flush();
    }

    /**
     * Gets message of current log
     * 
     * @return string Value of message
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * Gets comment of current log
     * 
     * @return string Value of comment
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Gets specific list of log
     * 
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm Document manager of MongoDB
     * @param string $tag Kind of log (event, error, loads, intercel)
     * @return ArrayObject List of log
     */
    public function getLogs($dm, $tag) {
        $result = array();
        $now = date("Y-m-d");
        $final = date("Y-m-d", strtotime("+1 month", strtotime($now)));
        $temp1 = explode("-", $now);
        $temp2 = explode("-", $final);
        $start = $temp1[0]."-".$temp1[1]."-01 00:00:00";
        $end = $temp2[0]."-".$temp2[1]."-01 00:00:00";
        $logs = $dm->getRepository('Application\Document\Log')
            ->findBy( 
                array(
                    'kind' => $tag,
                    'timestamp' => array(
                        '$gte' => new \MongoDate(strtotime($start)),
                        '$lt' => new \MongoDate(strtotime($end))
                    )
                ) 
        );
        if(!empty($logs)){
            foreach($logs as $l){
                $t = $l->getData();
                $t["timestamp"] = $t["timestamp"]->format('Y-m-d H:i:s');
                $result[] = $t;
            }
        }
        return $result;
    }

    /**
     * Gets path of logs
     * 
     * @param String $log Filename of log
     * @return string Current path of $log
     */
    private function getPath($log) {
        $path = getcwd() . "/data/logs/" . $log . ".log";
        $path = str_replace("\\", "/", $path);
        return $path;
    }

    /**
     * Gets the client's IP
     *
     * @return string A string with the IP.
     */
    private function getIp() {
        $ip = "Unknown";
        if (php_sapi_name() != "apache2handler") {
            $host = gethostname();
            $ip = gethostbyname($host);
        } else {
            if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
        }
        return $ip;
    }

    /**
     * Gets the client´s user agent
     * 
     * @return Arrayobject Contains keys specifying os, browser, broser´s version
     */
    private function getAgent() {
        $result = array(
            "browser" => "Unknown",
            "os" => "Unknown",
            "version" => "Unknown"
        );
        $os = array(
            "Windows" => "/Windows/i",
            "Linux" => "/Linux/i",
            "Unix" => "/Unix/i",
            "Mac" => "/Mac/i"
        );
        if (php_sapi_name() != "apache2handler") {
            $user_agent = php_uname('s');
        } else {
            if (!empty($_SERVER["HTTP_USER_AGENT"])) {
                $user_agent = $_SERVER["HTTP_USER_AGENT"];
                $browser = array(
                    "Navigator" => "/Navigator(.*)/i",
                    "Firefox" => "/Firefox(.*)/i",
                    "Internet Explorer" => "/MSIE(.*)/i",
                    "Google Chrome" => "/chrome(.*)/i",
                    "MAXTHON" => "/MAXTHON(.*)/i",
                    "Opera" => "/Opera(.*)/i",
                );
                foreach ($browser as $key => $value) {
                    if (preg_match($value, $user_agent)) {
                        $result["browser"] = $key;
                        \preg_match_all($value, $user_agent, $match);
                        switch ($result["browser"]) {
                            case "Firefox":
                                $result["version"] = str_replace("/", "", $match[1][0]);
                                break;
                            case "Internet Explorer":
                                $result["version"] = \substr($match[1][0], 0, 4);
                                break;
                            case "Opera":
                                $result["version"] = str_replace("/", "", \substr($match[1][0], 0, 5));
                                break;
                            case "Navigator":
                                $result["version"] = \substr($match[1][0], 1, 7);
                                break;
                            case "MAXTHON":
                                $result["version"] = str_replace(")", "", $match[1][0]);
                                break;
                            case "Google Chrome":
                                $result["version"] = \substr($match[1][0], 1, 10);
                                break;
                        }
                        break;
                    }
                }
            }
        }
        foreach ($os as $key => $value) {
            if (preg_match($value, $user_agent)) {
                $result["os"] = $key;
                break;
            }
        }
        return $result;
    }

    /**
     * Gets a custom timestamp
     *
     * @return string Text with the timestamp.
     */
    private function getTimestamp() {
        $mt = substr(microtime(), 1, 7);
        return date("Y-m-d H:i:s");
    }

    /**
     * Gets the specific message
     * 
     * @param string $key Kind of message
     * @param string|ArrayObject $value Params for concatenate to current message
     * @return string Text of message
     */
    private function getMessageStatus($key, $value = "Usuario No Identificado") {
        $msg = "";
        $messages = array(
            'CREATE_CACHE_OK' => "La cache ha sido generada exitosamente",
            'CREATE_CACHE_ERROR' => "La cache no pudo generar correctamente",            
            'REMOVE_XLSX_OK' => "Los archivos '.xlsx' han sido eliminados correctamente",
            'REMOVE_XLSX_ERROR' => "Los archivos '.xlsx' no fueron eliminados correctamente",
            "SYSTEM_ERROR" => "Error de sistema.",
            "VALIDATION_FAILS" => "El formulario no cumplió las reglas de validación",
            "PASSWORD_NO_MATCH" => "Las contraseñas no coinciden.",
            "OLD_PASSWORD_INCORRECT" => "La contraseña anterior no coincide.",
            "CHANGE_PASSWORD_OK" => "La contraseña se ha actualizado correctamente.",
            "CHANGE_PASSWORD_ERROR" => "La contraseña no se ha actualizado correctamente.",
            "NEW_PASSWORD_IS_REPEATED" => "La nueva contraseña debe ser distinta a la actual.",
            "ONLY_CONSOLE" => "Esta operación no fue ejecutada desde línea de comandos",
            "USER_NO_PRIVILEGED" => "Usuario intentó escalar privilegios o no tiene los permisos adecuados",
            "USER_REJECTED" => "Usuario no autorizado.",
            "USER_LOGIN_FAIL" => "'{userId}' no pudo iniciar sesión.",
            "USER_LOGGED" => "'{userId}' inició sesión satisfactoriamente.",
            "USER_LOGOUT" => "'{userId}' finalizó sesión satisfactoriamente.",
            "GET_USERS" => "'{userId}' obtuvo el 'Listado de Usuarios'.",
            "FAIL_SOAP_CONNECTION" => "'{userId}' rechazado por el Web Service.",
            "IS_POST_REQUEST" => "'{userId}' no accedió por método _POST.",
            "DEVICES_REPORT" => "'{userId}' obtuvo el 'Listado de Dispositivos'.",
            "USERS_REPORT" => "'{userId}' obtuvo el 'Listado de Usuarios'.",
            "EMPTY_SERIVCE_PROVIDER_ID" => "'{userId}' no había seleccionado a un 'Service Provider'.",
            "GENERATE_REPORT" => "'{userId}' generó correctamente un 'Reporte de Excel'.",
            "GENERATE_REPORT_ERROR" => "'{userId}' no generó correctamente un 'Reporte de Excel'.",
            "CHANGE_PASSWORD" => "'{userId}' ha actualizado exitosamente su contraseña.",
            "CHANGE_PASSWORD_ERROR" => "'{userId}' no logró actualizar su contraseña.",
            "USER_ADD_ERROR" => "'{userId}' no logro agregar un usuario.",
            "USER_ADD_OK" => "'{userId}' agrego un usuario.",
            "USER_EDIT_ERROR" => "'{userId}' no logro actualizar a un usuario.",
            "USER_EDIT_OK" => "'{userId}' actualizó a un usuario.",
            "USER_DELETE_ERROR" => "'{userId}' no logro eliminar a un usuario.",
            "USER_DELETE_OK" => "'{userId}' eliminó a un usuario.",
            "USER_PROFILE" => "'{userId}' obtuvo un perfil de usuario correctamente",
            "FAIL_USER_PROFILE" => "'{userId}' no pudo obtener el perfil de un usuario",
            "GET_SERVICES" => "'{userId}' obtuvo el listado de servicios de un usuario",
            "FAIL_GET_SERVICES" => "'{userId}' no pudo obtener los servicios de un usuario",
            "CREATE_CACHE_ERROR" => "Error al crear Cache en Redis.",
            "CREATE_CACHE_OK" => "Se creó la Cache en Redis satisfactoriamente.",
            "FAIL_UPDATE_SERVICES" => "'{userId}' no pudo asignar los nuevos servicios.",
            "SUCCESS_UPDATE_SERVICES" => "'{userId}' asignó correctamente los nuevos servicios.",
            "GET_DEVICES_USER" => "'{userId}' obtuvo listado de dispositivos",
            "FAIL_GET_DEVICES_USER" => "'{userId}' no logro obtener los dispositivos",
            "LOG_DEVICE_POLYCOM" => "'{userId}' obtuvo la configuración de un Polycom",
            "LOG_DEVICE_COMMUNICATOR" => "'{userId}' obtuvo la configuración de un Business Communicator ",
            "FAIL_LOG_DEVICE" => "'{userId}' no logro obtener la configuración de un dispositivo",
            "ADD_DEVICE_OK" => "'{userId}' agregó correctamente un dispositivo.",
            "ADD_DEVICE_ERROR" => "'{userId}' no logró agregar un dispositivo.",
            "EDIT_DEVICE_OK" => "'{userId}' logró modificar un dispositivo.",
            "EDIT_DEVICE_ERROR" => "'{userId}' no logró modificar un dispositivo.",
            "DELETE_DEVICE_OK" => "'{userId}' logró eliminar un dispositivo.",
            "DELETE_DEVICE_ERROR" => "'{userId}' no logró eliminar un dispositivo.",
            "EDIT_PASS_UC_ONE_OK" => "'{userId}' logró actualizar la contraseña de un dispositivo.",
            "EDIT_PASS_UC_ONE_ERROR" => "'{userId}' no logró actualizar la contraseña de un dispositivo.",
            "ENABLED_FAC" => "'{userId}' habilitó la FAC de un usuario",
            "DISABLED_FAC" => "'{userId}' deshabilitó la FAC de un usuario",
            "FAIL_ENABLED_FAC" => "'{userId}' no logro habilitar la FAC de un usuario",
            "FAIL_DISABLED_FAC" => "'{userId}' no logro deshabilitar la FAC de un usuario",
            "CHANGE_FAC" => "'{userId}' cambio el Fac de un usuario",
            "FAIL_CHANGE_FAC" => "'{userId}' no logro cambiar el Fac de un usuario",
            "UPDATE_PERMITS" => "'{userId}' modifico los permisos de un usuario",
            "FAIL_UPDATE_PERMITS" => "'{userId} no logro modificar los permisos de un usuario'",
            "GET_PROFILES" => "'{userId}' obtuvo el Listado de Perfiles",
            "PROFILE_EDIT_ERROR" => "'{userId}' no logró modificar un perfil.",
            "PROFILE_ADD_ERROR" => "'{userId}' no logró agregar un perfil.",
            "PROFILE_DELETE_ERROR" => "'{userId}' no logró eliminar un perfil.",
            "PROFILE_EMAIL_ERROR" => "'{userId}' no logró activar su correo electrónico.",
            "PROFILE_EDIT_OK" => "'{userId}' logró modificar un perfil exitosamente.",
            "PROFILE_ADD_OK" => "'{userId}' logró agregar un perfil exitosamente.",
            "PROFILE_DELETE_OK" => "'{userId}' logró eliminar un perfil.",
            "PROFILE_EMAIL_OK" => "'{userId}' logró activar su correo electrónico.",
            "DATABASE_ERROR" => "Error al procesar la solicitud a la Base de Datos",
            "REQUEST_RESET_PASSWORD_OK" => "'{userId}' solicitó exitosamente la recuperación de su contraseña",
            "REQUEST_RESET_PASSWORD_ERROR" => "'{userId}' no logró solicitar la recuperación de su contraseña",
            "CLEAN_RESET_PWD_OK" => "Se han limpiado las solicitudes de recuperación de contraseña",
            "CLEAN_RESET_PWD_ERROR" => "No se lograron limpiar las solicitudes de recuperación de contraseña",
            "SEND_RESET_PWD_OK" => "Se han enviado las notificaciones para cambio de contraseña",
            "SEND_RESET_PWD_ERROR" => "No se lograron enviar las notificaciones para cambio de contraseña"
        );
        if ($messages[$key] != null) {
            $msg = \str_replace("{userId}", $value, $messages[$key]);
        }
        return $msg;
    }

}
