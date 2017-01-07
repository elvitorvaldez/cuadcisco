<?php

namespace Application\OCISchema;

use Application\OCISchema\Base\BaseSchema;

/**
 * Description of Enterprise
 *
 * @author Roman
 */
class Enterprise extends BaseSchema {

    /**
     * Construct of Enterprise
     * 
     * @param SoapClient $client
     * @param String $sessionId
     */
    public function __construct($client, $sessionId) {
        parent::__construct2($client, $sessionId);
    }

    /**
     * Gets the users of an enterprise
     * 
     * @param String $serviceProviderId
     * @param int $responseSizeLimit
     * @return Array|Null
     */
    public function getUserList($serviceProviderId, $responseSizeLimit = 1000) {
        $tags = array('serviceProviderId', 'responseSizeLimit');
        $data = array($serviceProviderId, $responseSizeLimit);
        return $this->executeCall($data, "UserGetListInServiceProviderRequest", $tags, true);
    }

}
