<?php

class Controller {

    protected $db; 

    function Controller() {
       $this->db = Doctrine_Manager::connection('mysql://kameleontti:olohuone@localhost/kauniskameleontti');
   }
}