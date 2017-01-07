<?php

namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Application\Document\Base\DocumentAdapter;

/** @ODM\Document(db="logs", collection="gestion_cuad") */
class Log extends DocumentAdapter{
    
    /** @ODM\Id */
    protected $id;
    
    /** @ODM\Field(type="date") */
    protected $timestamp;
    
    /** @ODM\Field(type="string") */
    protected $kind;
    
    /** @ODM\Field(type="string") */
    protected $ip;
    
    /** @ODM\Field(type="string") */
    protected $browser;
    
    /** @ODM\Field(type="string") */
    protected $version;
    
    /** @ODM\Field(type="string") */
    protected $os;
    
    /** @ODM\Field(type="string") */
    protected $request;
    
    /** @ODM\Field(type="string") */
    protected $description;
    
    /** @ODM\Field(type="string") */
    protected $comment;

    
}