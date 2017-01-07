<?php

namespace Application\OCISchema\Database;

/**
 * Description of EnterpriseComplements
 *
 * @author Roman
 */
trait EnterpriseComplements {

    use Table;

    /**
     * Saves an enterprise
     * 
     * @param String $enterprise
     */
    public function setEnterprise($enterprise) {
        $sql = "SELECT * FROM enterprise WHERE name=?";
        $data = array($enterprise);
        $statement = $this->makeStatement($sql, $data);
        if ($statement->getAffectedRows() === 1) {
            $sql = "REPLACE INTO enterprise SET id = ?, name = ?";
            $data = array($statement->current()['id'], $enterprise);
        } else {
            $sql = "INSERT INTO enterprise (name) VALUES (?)";
            $data = array($enterprise);
            $this->makeStatement($sql, $data);
        }
    }

}
