<?php

 namespace Application\Model;

 class Usuarios
 {
    public $id;
    public $name;
    public $user_group;
    public $role;
    public $email;
    public $username;


     public function exchangeArray($data)
     {
        $this->id	=(!empty($data['id'])) ? $data['id'] : null;
        $this->name	=(!empty($data['name'])) ? $data['name'] : null;
        $this->user_group	=(!empty($data['user_group'])) ? $data['user_group'] : null;
        $this->role	=(!empty($data['role'])) ? $data['role'] : null;
        $this->email	=(!empty($data['email'])) ? $data['email'] : null;
        $this->username	=(!empty($data['username'])) ? $data['username'] : null;
     }
 }

