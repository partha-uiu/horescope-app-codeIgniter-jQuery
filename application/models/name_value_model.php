<?php

class Name_value_model extends App_model {

    public $_table_name = 'name_value';
    public $_alias = 'name_value';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>