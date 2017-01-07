<?php

 namespace Application\Model;

 class Usersapp
 {
    public $id;
    public $user;
    public $app;
    
    public $name;
    public $link;
    

     public function exchangeArray($data)
     {
        $this->id	=(!empty($data['id'])) ? $data['id'] : null;
        $this->user	=(!empty($data['user'])) ? $data['user'] : null;
        $this->app	=(!empty($data['app'])) ? $data['app'] : null;
        
        $this->name	=(!empty($data['name'])) ? $data['name'] : null;
        $this->link	=(!empty($data['link'])) ? $data['link'] : null;
     }
 }

