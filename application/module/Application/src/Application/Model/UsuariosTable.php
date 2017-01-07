<?php

namespace Application\Model;

 use Zend\Db\TableGateway\TableGateway;
   use Zend\Db\ResultSet\ResultSet; 
   use Zend\Db\Sql\Sql;

 class UsuariosTable
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
         $select->columns(array('id','username'));               
         $select->from('users');    
         $select->order(array('username ASC'));
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
     
     //obtener el rol específico (no el group id)
      public function getRoleByUsername($id)
     {
         $rowset = $this->tableGateway->select(array('username' => $id));
         
         $row = $rowset->current();
         return $row;
     }
     
     
     
     
     public function getAttempByUsername($id)
     {
         $rowset = $this->tableGateway->select(array('username' => $id));         
         $row = $rowset->current();
    
         if (!$row) {
             throw new \Exception("Could not find row $id");
             //$retorno=false;
         }
          
         return $row;
     }
     
    public function addAttemp($id, $attemp)
    {
     
              $data = array(
            'attemps'	=> $attemp,
            );
      $this->tableGateway->update($data, array('id' => $id));
    }

     public function saveMail($session,$isUser)
     {
      $user_group = ($session->user_group=="Operators") ? 1 : 2;
      $role = ($session->role=="User") ? 1 : 2;
         $data = array(
             'name' => $session->name,
             'email'  => $session->emailAddress,
             'username'  => $session->username,
             'user_group'  => $user_group,
             'role'  => $role,
         );

      
     
         if ($isUser) {
            
              //$this->tableGateway->update($data, array('username' => $session->username));
          
         } else {
  
           $this->tableGateway->insert($data);
         
         }
     }

     public function deleteUser($id)
     {
         $this->tableGateway->delete(array('id' => (int) $id));
     }
 }