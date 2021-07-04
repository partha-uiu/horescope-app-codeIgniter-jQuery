<?php

class User_model extends App_model {

    public $_table_name = 'users';
    public $_alias = 'users';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }
	
}

?>