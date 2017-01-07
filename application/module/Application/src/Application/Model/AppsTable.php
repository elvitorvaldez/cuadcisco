<?php

namespace Application\Model;

 use Zend\Db\TableGateway\TableGateway;
  use Zend\Db\ResultSet\ResultSet; 
   use Zend\Db\Sql\Sql;

 class AppsTable
 {
     protected $tableGateway;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }

     public function fetchAll()
     {
         $adapter = $this->tableGateway->getAdapter();        
         $sql = new Sql($adapter);
         $select = $sql->select();
         $select->columns(array('idApp','app_name'));               
         $select->from('apps');    
         $statement = $sql->prepareStatementForSqlObject($select);   
         // echo $select->getSqlString();
         $results = $statement->execute();
         
            $resultSet = new ResultSet();   
         //covertir el objeto ResultSet a arreglo
 
         $resultSet->initialize($results);
         return $resultSet->toArray();   
   
     }

     public function getUserByUsername($id)
     {
         $rowset = $this->tableGateway->select(array('username' => $id));
         
         $row = $rowset->current();
         $retorno=true;
         if (!$row) {
             //throw new \Exception("Could not find row $id");
             $retorno=false;
         }
          
         return $retorno;
     }
     
     
 }