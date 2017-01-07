<?php

namespace Application\Model;

use Application\OCISchema\Database\SessionComplements as SessionTrait;

/**
 * Description of Session
 *
 * @author Roman
 */
class Session {

    use SessionTrait;

    private $auth;
    private $db;

    /**
     * Construct of Session
     * 
     * @param Zend\Session\Container $auth
     * @param Zend\Db\Adapter\Adapter $db
     */
    public function __construct($auth, $db) {
        $this->auth = $auth;
        $this->db = $db;
    }

    /**
     * Initializes the session and check if an unique session registered
     * 
     * @param Array $data
     */
    public function init($data) {
      
        if (\is_array($data) && isset($data["userId"])) {
            $sessionId = $this->getSessionId($data["userId"]);
            if ($sessionId !== null && $this->auth->getManager()->getId() != $sessionId) {
                session_destroy();
                session_id($sessionId);
                session_start();
                session_destroy();
                session_start();
                $this->auth->getManager()->destroy();
                $this->auth->getManager()->start();
                $this->auth->getManager()->regenerateId(true);
                $this->auth->exchangeArray($data);
                $this->deleteSession($sessionId);
            } else {
                foreach ($data as $key => $value) {
                    $this->auth->$key = $value;
                }
            }
            $this->auth->time = time();
            $this->saveSession($this->auth->getManager()->getId(), $this->auth->userId);
        }
    }

    /**
     * Removes the current session
     * 
     */
    public function remove() {
        if ($this->auth && $this->auth->userId != null) {
            $sessionId = $this->getSessionId($this->auth->userId);
            $this->deleteSession($sessionId);
            $this->auth->getManager()->getStorage()->clear('auth');
        }
    }

}
