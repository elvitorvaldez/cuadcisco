<?php
namespace Application\Model;

 use Zend\Db\TableGateway\TableGateway;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\ResultSet; 
 
 class UsersappTable
 {
     protected $tableGateway;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }

     public function fetchAll()
     {
         $resultSet = $this->tableGateway->select();
         return $resultSet;
     }

     public function clearByUser($user)
     {
       $this->tableGateway->delete(array('user' => $user));    
     }
     
     public function addUserApp($user,$app)
     {
         $adapter = $this->tableGateway->getAdapter();   
         $sql     = new \Zend\Db\Sql\Sql($adapter);  
         $app=$app*1;
         $data = array(
            'user'	=> $user,
            'app'	=> $app            
         );
       $insert = $sql->insert('usersapp');
       $insert->values($data);
       $selectString = $sql->prepareStatementForSqlObject($insert);  
       $result= $selectString->execute(); 
       return $result;
     }

     public function getAppsByUser($username)
     {
		 
         $adapter = $this->tableGateway->getAdapter();        
         $sql = new Sql($adapter);
         $select = $sql->select();
         $select->columns(array('app','user'));               
         $select->from('usersapp');    
         $select->join('apps', "apps.idApp = usersapp.app", array('app_name'), 'inner');     
         $select->where("usersapp.user = '$username'");    
         $statement = $sql->prepareStatementForSqlObject($select);   
       //  echo $select->getSqlString();
       
         $results = $statement->execute();
 
         //convertir el resultado a objeto ResultSet
         $resultSet = new ResultSet();   
         //covertir el objeto ResultSet a arreglo
 
         
         $resultSet->initialize($results);
         return $resultSet->toArray();      
         

     }
     
     
 }