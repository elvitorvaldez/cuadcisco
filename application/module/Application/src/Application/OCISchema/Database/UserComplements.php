<?php

namespace Application\OCISchema\Database;

/**
 * User's complements trait
 *
 * THis file implements the required complements for the User class.
 *
 * PHP version 5
 *
 * @author		Alejandro Cedeño Quintero <acedeno@vsys.com>
 * @version		2.0
 * @modified            Carlos Román <croman@vsys.com> 
 */
trait UserComplements {

    use Table;

    /**
     * Blocks FACs
     *
     * Saves the used FACs in the DB in order to avoid duplicates.
     *
     * @param string $fac			a string with the fac.
     * @param integer $enterpriseId an integer with the enterprise's id.
     * @param integer $userId		an integer with the user's id.
     *
     * @return object	an object with the result of the query.
     *
     * @access public
     */
    public function blockFac($fac, $enterpriseId, $userId) {
        $sql = "INSERT INTO facblacklist (user, fac, enterpriseName) VALUES " . "(?, ?, ?)";
        $data = array($userId, $fac, $enterpriseId);
        $this->makeStatement($sql, $data);
    }

    /**
     * Deletes FAC
     *
     * Deletes a specific FAC from the DB (it happens when the FAC is changed or
     * disabled).
     *
     * @param integer $enterpriseId an integer with the enterprise's id.
     * @param integer $userId an integer with the user's id.
     *
     * @return object an object with the result of the query.
     *
     * @access public
     */
    public function deleteFac($enterpriseId, $userId) {
        $sql = "DELETE FROM facblacklist WHERE enterpriseName = ? AND " . "user = ?";
        $data = array($enterpriseId, $userId);
        $this->makeStatement($sql, $data);
    }

    /**
     * Checks FAC
     *
     * Checks if exists a specific FAC in the DB
     *
     * @param integer $userId an integer with the user's id.
     *
     * @return object an object with the result of the query.
     *
     * @access public
     */
    public function checkFac($userId) {
        $sql = "SELECT fac FROM facblacklist WHERE user = ?";
        $data = array($userId);
        return $this->makeStatement($sql, $data);
    }

    /**
     * Gets the blocked FACs
     *
     * Gets the saved FACs in the DB.
     *
     * @param integer $enterpriseId an integer with the enterprise's id.
     *
     * @return object an object with the result of the query.
     *
     * @access public
     */
    public function getBlockedFacs($enterpriseId) {
        $sql = "SELECT fac FROM facblacklist WHERE enterpriseName = ? OR " . "enterpriseName = 'DEFAULT'";
        $data = array($enterpriseId);
        return $this->makeStatement($sql, $data);
    }

    /**
     * Quarantine
     *
     * Puts a FAC in quarantine
     *
     * @param integer $userId		an integer with the user's id.
     *
     * @return object	an object with the result of the query.
     *
     * @access public
     */
    public function quarantine($userId) {
        $sql = "UPDATE facblacklist SET user = 'blocked', date = now() " . "WHERE user = ?";
        $data = array($userId);
        return $this->makeStatement($sql, $data);
    }

    /**
     * Creates the device's password
     *
     * Creates/updates the current device's password.
     *
     * @param string $deviceName	a string with the device's name.	
     * @param string $password a string with the device's password.
     *
     * @return object an object with the result of the query.
     *
     * @access public
     */
    public function createPasswordDevice($deviceName, $password) {
        $sql = "SELECT id FROM authentication WHERE deviceName = ?";
        $data = array($deviceName);
        $statement = $this->makeStatement($sql, $data);
        if ($statement->getAffectedRows() === 1) {
            $sql = "REPLACE INTO authentication SET id = ?, " . "deviceName = ?, password = ?";
            $data = array($statement->current()['id'], $deviceName, $password);
        } else {
            $sql = "INSERT INTO authentication (deviceName, password) " . "VALUES (?, ?)";
            $data = array($deviceName, $password);
        }
        $this->makeStatement($sql, $data);
    }

    /**
     * Reads the device's password
     *
     * Reads the password from the DB.
     *
     * @param string $deviceName	a string with the device's name.
     *
     * @return object an object with the result of the query.
     *
     * @access public
     */
    public function readPasswordDevice($deviceName) {
        $sql = "SELECT password FROM authentication WHERE deviceName = ?";
        $data = array($deviceName);
        return $this->makeStatement($sql, $data);
    }

}
