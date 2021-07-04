<?php

class Data_female_model extends App_model {

    public $_table_name = 'female';
    public $_alias = 'Female';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>