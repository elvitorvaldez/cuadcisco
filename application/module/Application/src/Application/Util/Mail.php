<?php

namespace Application\Util;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail as SendmailTransport;

/**
 * Description of Mail
 *
 * @author Roman
 */
class Mail {

    private $mail = null;
    private $subject = null;
    private $to = null;
    private $message = null;

    public function __construct() {
        $this->mail = new Message();
        $this->mail->setFrom('noreply@vsys.com', 'Gestión CUAD');
        $this->mail->setEncoding("UTF-8");
    }

    /**
     * Adds the receivers of mail
     * 
     * @param Array $to
     */
    public function addTo($to) {
        $this->to = array();
        if (\is_array($to)) {
            if (count($to) == 2) {
                if ($to["name"] !== "" && $to["email"] !== "") {
                    $this->to[] = $to;
                }
            }
            if (count($this->to) === 0) {
                $this->to = $to;
            }
            foreach ($this->to as $t) {
                $this->mail->addTo($t["email"], $t["name"]);
            }
        } else {
            $this->mail->addTo($to);
        }
    }

    /**
     * Sets the subject of mail
     * 
     * @param String $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
        $this->mail->setSubject($this->subject);
    }

    /**
     * Adds the message of mail
     * 
     * @param String $type
     * @param Array $data
     */
    public function addMessage($type, $data = null) {
        $this->message = $this->getBodyMessage($type, $data);
        $html = new MimePart(\str_replace("{{body}}", $this->message, $this->getLayoutMessage()));
        $html->type = "text/html";
        $html->charset = "utf-8";
        $body = new MimeMessage();
        $body->setParts(array($html));
        $this->mail->setBody($body);
    }

    /**
     * Sends the mail
     * 
     */
    public function send() {
        if ($this->mail !== null && $this->to !== null &&
                $this->subject !== null && $this->message !== null) {
            $transport = new SendmailTransport();
            $transport->send($this->mail);
        }
    }

    /**
     * Gets the layout of mail
     * 
     * @return String
     */
    private function getLayoutMessage() {
        $url = $this->getBaseUrl();
        $layout = '<html><head><meta charset="utf-8" /><title>{{title}}</title></head><body><style>p{margin:10px 0;}@media(max-width:470px) {img{float:none !important;max-width:180px;margin:auto;}}</style><div style="padding:10px 15px; text-align:justify; font-family: Arial, Helvetica, sans-serif;"><div style="text-align:center;"><img src="'.$url.'images/telmex_logo_xlsx.jpg" alt="TELMEX" style="float:left; width:219px;"><img src="'.$url.'images/gestion_cuad.jpg" alt="Gestión CUAD" style="float:right; width:200px;"><div style="clear:both"></div></div><div>{{body}}</div></div></body></html>';
        return \str_replace("{{title}}", $this->subject, $layout);
    }

    /**
     * Gets the boyd of mail
     * 
     * @param String $type
     * @param Array $data
     * @return String
     */
    private function getBodyMessage($type, $data = null) {
        $body = "";
        switch ($type) {
            #Notifica de la expiracion de la contraseña
            case "notify_change_password":
                $url = $this->getBaseUrl();
                $html_body = '<p>Estimad@ {{name}}.</p>
                    <p>Por este medio le notificamos que el sistema ha iniciado una solicitud de cambio de contraseña para la cuenta {{userId}}, haga clic en el siguiente link <a href="' . $url . 'resetPassword/{{token}}">' . $url . 'resetPassword/{{token}}</a> para reestablecer su contraseña.</p>
                    <p><b>Favor de hacer el cambio de contraseña</b></p>';
                $body = \str_replace(array("{{name}}", "{{userId}}", "{{token}}"), array($data["name"], $data["userId"], $data["token"]), $html_body);
                break;
            #Recuperación de contraseña
            case "reset_password_confirm":
                $url = $this->getBaseUrl();
                if ($data["app"] !== null) {
                    if ($data["app"] === "gestion_cuad" ||
                            $data["app"] === "directorio" || $data["app"] === "reportes"
                    ) {
                        $data["token"].="?app=" . $data["app"];
                    }
                }
                $html_body = '<p>Estimad@ {{name}}.</p>
                    <p>Por este medio le notificamos que ha iniciado una solicitud de cambio de contraseña para la cuenta {{userId}}, haga clic en el siguiente link <a href="' . $url . 'resetPassword/{{token}}">' . $url . 'resetPassword/{{token}}</a> para reestablecer su contraseña.</p>
                    <p><b>Si usted no ha solicitado un cambio ignore este correo</b></p>';
                $body = \str_replace(array("{{name}}", "{{userId}}", "{{token}}"), array($data["name"], $data["userId"], $data["token"]), $html_body);
                break;
            #Cambio de contraseña por el mismo usuario
            case "change_password":
                $html_body = '<p>Estimad@ {{name}}.</p>
                    <p>Por este medio le notificamos que su contraseña para la cuenta {{userId}} de  la plataforma “Gestión CUAD” ha sido modificada exitosamente.</p>';
                $body = \str_replace(array("{{name}}", "{{userId}}"), array($data["name"], $data["userId"]), $html_body);
                break;
        }
        $html_body.=$this->getFooter();
        return $body;
    }

    /**
     * Gets the footer message of an email
     * 
     * @return string
     */
    private function getFooter() {
        return '<p>Para cualquier duda o aclaración comuníquese con su “Mesa de ayuda”.</p>
	<div style="border-bottom:1px solid #000; height:1px; width:100%; margin:25px 0;"></div>
	<div style="font-size:10px;"><p><b>AVISO DE CONFIDENCIALIDAD</b>:</p><p>Este correo electrónico, incluyendo en su caso, los archivos adjuntos al mismo, pueden contener información de carácter confidencial y/o privilegiada, y se envían a la atención única y exclusivamente de la persona y/o entidad a quien va dirigido. La copia, revisión, uso, revelación y/o distribución de dicha información confidencial sin la autorización por escrito de Teléfonos de México está prohibida. Si usted no es el destinatario a quien se dirige el presente correo, favor de contactar al remitente respondiendo al presente correo y eliminar el correo original incluyendo sus archivos, así como cualesquiera copia del mismo. Mediante la recepción del presente correo usted reconoce y acepta que en caso de incumplimiento de su parte y/o de sus representantes a los términos antes mencionados, Teléfonos de México tendrá derecho a los daños y perjuicios que esto le cause.</p><br /><p><b>CONFIDENTIALITY NOTICE</b>:</p><p>This e-mail message including attachments, if any, is intended only for the person or entity to which it is addressed and may contain confidential and /or privileged material. Any review, use, disclosure or distribution of such confidential information without the written authorization of Teléfonos de México is prohibited. If you are not the intended recipient, please contact the sender by reply e-mail and destroy all copies of the original message. By receiving this e-mail you acknowledge that any breach by you and/or your representatives of the above provisions may entitle Teléfonos de México to seek for damages.</p></div>';
    }

    /**
     * Gets base url
     * 
     * @param String $app
     * @return String
     */
    private function getBaseUrl($app = null) {
        $dir = getcwd();
        $array = array();
        $dir = \str_replace("\\", "/", $dir);
        if (\preg_match_all("/htdocs/", $dir) !== 0) {
            $array = \explode("htdocs", $dir);
        } else if (\preg_match_all("/www/", $dir) !== 0) {
            $array = \explode("www", $dir);
        }
        $path = "/" . \str_replace("application", "", $array[count($array) - 1]);
        if (php_sapi_name() != "apache2handler") {
            $host = gethostname();
            $ip = gethostbyname($host);
            $protocol = ($host !== 80) ? 'https' : 'http';
            if($host === 'reportes-bsft-des'){
                $ip = '10.1.20.51';
            }else if($host === 'reportes-bsft' || $ip === '10.5.0.135' || $ip === '10.3.21.141'){
                #$ip = '10.5.0.135';
                $ip = 'https://gestioncuad.telmex.com:8443/';
            }
            if($ip === 'https://gestioncuad.telmex.com:8443/'){
                $url = $ip.'auth/'; 
            }else{
                $url = $protocol . "://" . \str_replace("//", "/", $ip . $path);
            }
            
        } else {
            $protocol = (!empty($_SERVER["HTTPS"])) ? 'https' : 'http';
            if( $_SERVER["HTTP_HOST"] === 'gestioncuad.telmex.com:8443' || 
                $_SERVER["HTTP_HOST"] === 'gestioncuad.telmex.com' 
            ){
                $url = 'https://gestioncuad.telmex.com/cuadcisco/';
            } else {
                $url = $protocol . "://" . \str_replace("//", "/", $_SERVER['HTTP_HOST'] . $path);
            }
        }
        if ($app !== null) {
            if($url === 'https://gestioncuad.telmex.com/'){
                $app = \str_replace("gestion_cuad", "gestion", $app);
                $url .= $app;
            }else{
                $url = \str_replace("auth", $app, $url);
            }
        }
        return $url;
    }

}
