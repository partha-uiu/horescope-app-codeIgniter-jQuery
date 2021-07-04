<?php

class Data_male_model extends App_model {

    public $_table_name = 'male';
    public $_alias = 'Male';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>