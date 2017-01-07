<?php

namespace Application\OCISchema\Database;

/**
 * Description of AdminComplements
 *
 * @author Roman
 */
trait ProfileComplements {

    use Table;

    /**
     * Creates a new profile
     * 
     * @param String $userId
     * @param String $type
     * @param String|NULL $email
     * @param Array $enterprises
     */
    public function createProfile($userId, $type, $email, $enterprises = array()) {
        $statement1 = $this->getRol($type);
        $statement2 = $this->getProfile($userId);
        if ($statement1->getAffectedRows() === 1 && $statement2->getAffectedRows() <= 0) {
            $idRol = $statement1->current()['id'];
            $sql = "INSERT INTO profile (userId, email, type, updatedAt, " .
                    "firstTime) VALUES (?, ?, ?, ?, ?)";
            $data = array($userId, $email, $idRol, date("Y-m-d H:i:s"), 1);
            $statement = $this->makeStatement($sql, $data);
            $idProfile = $statement->getGeneratedValue();
            if ($type == "Gerente" || $type == "Aprovisionador" || $type === "Auditor") {
                if (count($enterprises) > 0 && $statement->getAffectedRows() === 1) {
                    $this->setEnterprisesByProfile($enterprises, $idProfile);
                }
            } else if ($type == "Cliente") {
                if (count($enterprises) == 1 && $statement->getAffectedRows() === 1) {
                    $this->setEnterprisesByProfile($enterprises, $idProfile);
                }
            }
        }
    }

    /**
     * Saves a profile previulsy created
     * 
     * @param String $userId
     * @param String $type
     * @param Array $enterprises
     */
    public function saveProfile($userId, $type, $enterprises = array()) {
        $statement1 = $this->getRol($type);
        $statement2 = $this->getProfile($userId);
        if ($statement1->getAffectedRows() === 1 && $statement2->getAffectedRows() === 1) {
            $idRol = $statement1->current()['id'];
            $idProfile = $statement2->current()['id'];
            $sql = "UPDATE profile SET type=? WHERE id=?";
            $data = array($idRol, $idProfile);
            $this->makeStatement($sql, $data);
            if ($type == "Gerente" || $type == "Aprovisionador" || $type === "Auditor") {
                $oldEnterprises = $this->getArrayNameEnterprises($this->getEnterprisesByProfile($idProfile));
                $filters = $this->filterNameEnterprises($oldEnterprises, $enterprises);
                foreach ($filters["remove"] as $r) {
                    $sql = "DELETE p.* FROM enterprisepermitted as p INNER JOIN " .
                            "enterprise as e ON p.idEnterprise=e.id AND p.idProfile=? WHERE e.name=?";
                    $data = array($idProfile, $r);
                    $this->makeStatement($sql, $data);
                }
                $this->setEnterprisesByProfile($filters["add"], $idProfile);
            } else {
                $sql = "DELETE FROM enterprisepermitted as e WHERE e.idProfile=?";
                $data = array($idProfile);
                $this->makeStatement($sql, $data);
            }
        }
    }

    /**
     * Gets the access with a type and the enterprises permitted
     * 
     * @param String $userId
     * @return Array
     */
    public function getAccess($userId) {
        $result = array();
        $sql = "SELECT profile.id as id, profile.email as email, roles.rol as type, " .
                "IF(firstTime=1 OR DATEDIFF(NOW(), profile.updatedAt)>90, 'true', 'false') as forceReset, " .
                "resetpassword.token as token FROM profile " .
                "INNER JOIN roles ON roles.id=profile.type " .
                "LEFT JOIN resetpassword ON resetpassword.idProfile=profile.id AND " .
                "resetpassword.type=1 WHERE profile.userId=?";
        $data = array($userId);
        $statement = $this->makeStatement($sql, $data);
        if ($statement !== null && $statement->getAffectedRows() >= 1) {
            $result["id"] = $statement->current()['id'];
            $result["type"] = $statement->current()['type'];
            $result["email"] = $statement->current()['email'];
            $result["token"] = $statement->current()['token'];
            $result["forceReset"] = $statement->current()['forceReset'];
            if ($result["forceReset"] == "true") {
                $result["forceReset"] = true;
            } else {
                $result["forceReset"] = false;
            }
            if ($result["type"] == "Supervisor") {
                $result["enterprises"] = $this->getArrayNameEnterprises(
                    $this->getEnterprises()
                );
            } else {
                $result["enterprises"] = $this->getArrayNameEnterprises(
                    $this->getEnterprisesByProfile($result["id"])
                );
            }
        }
        return $result;
    }

    /**
     * Extends the last updating of an user
     * 
     * @param String $userId
     * @param String $passwordEncrypted
     */
    public function extendModification($userId, $passwordEncrypted = '') {
        $statement = $this->getProfile($userId);
        $rol = $this->getRolByAdmin($userId);
        if ($statement->getAffectedRows() === 1) {
            $idProfile = $statement->current()['id'];
            $date = date("Y-m-d H:i:s");
            $sql = "UPDATE profile SET updatedAt=?, firstTime=? WHERE id=?";
            $data = array($date, 0, $idProfile);
            $this->makeStatement($sql, $data);
            $sql = "DELETE FROM resetpassword WHERE idProfile=? AND type=1";
            $data = array($idProfile);
            $this->makeStatement($sql, $data);
            if ($rol === "Usuario" && $passwordEncrypted !== "") {
                $sql = "SELECT id FROM authentication WHERE deviceName = ?";
                $data = array($userId);
                $statement = $this->makeStatement($sql, $data);
                if ($statement->getAffectedRows() === 1) {
                    $sql = "REPLACE INTO authentication SET id = ?, " . "deviceName = ?, password = ?";
                    $data = array($statement->current()['id'], $userId, $passwordEncrypted);
                } else {
                    $sql = "INSERT INTO authentication (deviceName, password) " . "VALUES (?, ?)";
                    $data = array($userId, $passwordEncrypted);
                }
                $this->makeStatement($sql, $data);
            }
        }
    }

    /**
     * Gets rol of an admin
     * 
     * @param type $userId
     * @return String
     */
    public function getRolByAdmin($userId) {
        $result = "";
        $sql = "SELECT r.rol as rol FROM roles as r INNER JOIN profile as p " .
                " ON p.type=r.id WHERE p.userId=?";
        $data = array($userId);
        $statement = $this->makeStatement($sql, $data);
        if ($statement->getAffectedRows() === 1) {
            $result = $statement->current()['rol'];
        }
        return $result;
    }

    /**
     * Gets email of an admin
     * 
     * @param type $userId
     * @return String
     */
    public function getEmailByAdmin($userId) {
        $result = "";
        $sql = "SELECT email FROM profile WHERE userId=?";
        $data = array($userId);
        $statement = $this->makeStatement($sql, $data);
        if ($statement->getAffectedRows() === 1) {
            $result = $statement->current()['email'];
        }
        return $result;
    }

    /**
     * Sets email of an admin
     * 
     * @param String $userId
     * @param String $email
     */
    public function setEmailByAdmin($userId, $email) {
        $sql = "UPDATE profile SET email=? WHERE userId=?";
        $data = array($email, $userId);
        $this->makeStatement($sql, $data);
    }

    /**
     * Resets enterprises permiited to an admin
     * 
     * @param String $userId
     * @param Array $enterprises
     */
    public function resetEnterprisesByAdmin($userId, $enterprises) {
        $statement = $this->getProfile($userId);
        if ($statement->getAffectedRows() === 1) {
            $idProfile = $statement->current()['id'];
            $idRol = $statement->current()['type'];
            $type = "";
            $sql = "SELECT * FROM roles WHERE id=?";
            $data = array($idRol);
            $statement = $this->makeStatement($sql, $data);
            if ($statement->getAffectedRows() === 1) {
                $type = $statement->current()['rol'];
            }
            if ($type == "Gerente" || $type == "Aprovisionador") {
                $oldEnterprises = $this->getArrayNameEnterprises($this->getEnterprisesByProfile($idProfile));
                $filters = $this->filterNameEnterprises($oldEnterprises, $enterprises);
                foreach ($filters["remove"] as $r) {
                    $sql = "DELETE p.* FROM enterprisepermitted as p INNER JOIN " .
                            "enterprise as e ON p.idEnterprise=e.id AND p.idProfile=? WHERE e.name=?";
                    $data = array($idProfile, $r);
                    $this->makeStatement($sql, $data);
                }
                $this->setEnterprisesByProfile($filters["add"], $idProfile);
            }
        }
    }

    /**
     * Deletes an admin from DB
     * 
     * @param String $userId
     */
    public function deleteProfile($userId) {
        $statement = $this->getProfile($userId);
        if ($statement->getAffectedRows() === 1) {
            $idProfile = $statement->current()['id'];
            $sql = "DELETE FROM profile WHERE userId=?";
            $data = array($userId);
            $this->makeStatement($sql, $data);
            $sql = "DELETE FROM enterprisepermitted WHERE idProfile=?";
            $data = array($idProfile);
            $this->makeStatement($sql, $data);
        }
    }

    /**
     * Creates a new registration to resetPassword
     * 
     * @param String $userId
     * @param String $token
     * @param String $date
     * @return Object|Null
     */
    public function createResetPassword($userId, $token, $date) {
        $statement = $this->getProfile($userId);
        $result = null;
        if ($statement->getAffectedRows() === 1) {
            $idProfile = $statement->current()['id'];
            $sql = "DELETE FROM resetpassword WHERE idProfile = ?";
            $data = array($idProfile);
            $this->makeStatement($sql, $data);
            $sql = "INSERT INTO resetpassword (idProfile, token, date, type) " .
                    "VALUES( ?, ?, ?, ? )";
            $data = array($idProfile, $token, $date, 0);
            $result = $this->makeStatement($sql, $data);
        }
        return $result;
    }

    /**
     * Finds a register of resetPassword
     * 
     * @param String $token
     * @return Object|Null
     */
    public function findResetPassword($token) {
        $sql = "SELECT resetpassword.id as id, userId, token, date " .
                "FROM resetpassword INNER JOIN profile ON " .
                "profile.id = resetpassword.idProfile WHERE resetpassword.token=? " .
                "AND resetpassword.type=0 AND DATEDIFF(NOW(), date)=0";
        $data = array($token);
        return $this->makeStatement($sql, $data);
    }

    /**
     * Deletes a register of resetPassword
     * 
     * @param int $id
     */
    public function deleteResetPassword($token) {
        $sql = "DELETE FROM resetpassword WHERE token=? AND type=0";
        $data = array($token);
        $this->makeStatement($sql, $data);
    }

    /**
     * Gets the rol by rol name
     * 
     * @param String $rol
     * @return Object
     */
    private function getRol($rol) {
        $sql = "SELECT * FROM roles WHERE rol=?";
        $data = array($rol);
        return $this->makeStatement($sql, $data);
    }

    /**
     * Gets the profile by userId
     * 
     * @param String $userId
     * @return Object
     */
    private function getProfile($userId) {
        $sql = "SELECT * FROM profile WHERE userId=?";
        $data = array($userId);
        return $this->makeStatement($sql, $data);
    }

    /**
     * Gets all enterprises
     * 
     * @return Object
     */
    private function getEnterprises() {
        $sql = "SELECT * FROM enterprise";
        return $this->makeStatement($sql);
    }

    /**
     * Gets the enterprises permitted by id
     * 
     * @param int $idProfile
     * @return Object
     */
    private function getEnterprisesByProfile($idProfile) {
        $sql = "SELECT e.id as id, e.name as name FROM enterprise as e" .
                " INNER JOIN enterprisepermitted as p ON p.idEnterprise=e.id" .
                " WHERE p.idProfile=?";
        $data = array($idProfile);
        return $this->makeStatement($sql, $data);
    }

    /**
     * Saves the enterprises relationated with user id
     * 
     * @param Array $enterprises
     * @param int $idProfile
     */
    private function setEnterprisesByProfile($enterprises, $idProfile) {
        foreach ($enterprises as $e) {
            $sql = "SELECT * FROM enterprise WHERE name=?";
            $data = array($e);
            $statement = $this->makeStatement($sql, $data);
            if ($statement->getAffectedRows() === 1) {
                $idEnterprise = $statement->current()['id'];
                $sql = "INSERT INTO enterprisepermitted (idEnterprise, idProfile) VALUES (?, ?)";
                $data = array($idEnterprise, $idProfile);
                $this->makeStatement($sql, $data);
            }
        }
    }

    /**
     * Gets an array of name enterprises
     * 
     * @param Object $statement
     * @return Array
     */
    private function getArrayNameEnterprises($statement) {
        $result = array();
        foreach ($statement as $s) {
            $result[] = $s["name"];
        }
        return $result;
    }

    /**
     * Gets an array with the name enterprises for add or remove
     * 
     * @param Array $oldEnterprises
     * @param Array $newEnterprises
     * @return Array
     */
    private function filterNameEnterprises($oldEnterprises, $newEnterprises) {
        $result = array(
            "add" => array(),
            "remove" => array()
        );
        foreach ($oldEnterprises as $old) {
            if (!\in_array($old, $newEnterprises)) {
                $result["remove"][] = $old;
            }
        }
        foreach ($newEnterprises as $new) {
            if (!\in_array($new, $oldEnterprises)) {
                $result["add"][] = $new;
            }
        }
        return $result;
    }

}
