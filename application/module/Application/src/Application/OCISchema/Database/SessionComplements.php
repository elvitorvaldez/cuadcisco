<?php

namespace Application\OCISchema\Database;

/**
 * Description of SessionComplements
 *
 * @author Roman
 */
trait SessionComplements {

    use Table;

    /**
     * Gets the session id of an user
     * 
     * @param type $userId
     * @return NULL|String
     */
    public function getSessionId($userId) {
        $sessionId = null;
        $statement = $this->getProfile($userId);
        if ($statement->getAffectedRows() === 1) {
            $idProfile = $statement->current()['id'];
            $statement = $this->getSessionByProfile($idProfile);
            if ($statement->getAffectedRows() === 1) {
                $sessionId = $statement->current()['id'];
            }
        }
        return $sessionId;
    }

    /**
     * Saves the session of an user
     * 
     * @param String $id
     * @param String $name
     * @param float $modified
     * @param int $lifeTime
     * @param String $auth
     * @param String $data
     * @return NULL|String
     */
    public function saveSession($id, $userId) {
        $sessionId = null;
        $statement = $this->getProfile($userId);
        if ($statement->getAffectedRows() === 1) {
            $idProfile = $statement->current()['id'];
            $statement = $this->getSession($id);
            if ($statement->getAffectedRows() === 1) {
                $sql = "UPDATE sessions SET idProfile=? WHERE id=?";
                $data = array($idProfile, $id);
                $statement = $this->makeStatement($sql, $data);
                $sessionId = $statement->current()['id'];
            } else {
                $sql = "INSERT INTO sessions (id, idProfile)" .
                        " VALUES( ? , ? )";
                $data = array($id, $idProfile);
                $statement = $this->makeStatement($sql, $data);
                $sessionId = $statement->getGeneratedValue();
            }
        }
        return $sessionId;
    }

    /**
     * Deletes a session of an user
     * 
     * @param String $id
     */
    public function deleteSession($id) {
        $sql = "DELETE FROM sessions WHERE id = ?";
        $data = array($id);
        $this->makeStatement($sql, $data);
    }

    /**
     * Gets the profile of an user
     * 
     * @param String $userId
     * @return Object
     */
    private function getProfile($userId) {
        $sql = "SELECT * FROM profile WHERE userId = ?";
        $data = array($userId);
        return $this->makeStatement($sql, $data);
    }

    /**
     * Gets the session by profile id
     * 
     * @param int $id
     * @return Object
     */
    private function getSessionByProfile($id) {
        $sql = "SELECT * FROM sessions WHERE idProfile = ?";
        $data = array($id);
        return $this->makeStatement($sql, $data);
    }

    private function getSession($id) {
        $sql = "SELECT * FROM sessions WHERE id = ?";
        $data = array($id);
        return $this->makeStatement($sql, $data);
    }

}
