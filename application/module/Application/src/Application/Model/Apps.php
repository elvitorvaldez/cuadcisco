<?php

 namespace Application\Model;

 class Apps
 {
    public $id;
    public $name;
    public $link;
    

     public function exchangeArray($data)
     {
        $this->id	=(!empty($data['id'])) ? $data['id'] : null;
        $this->name	=(!empty($data['name'])) ? $data['name'] : null;
        $this->link	=(!empty($data['link'])) ? $data['link'] : null;
     }
 }

