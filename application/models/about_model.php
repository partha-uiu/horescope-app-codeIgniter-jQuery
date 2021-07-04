<?php

class About_model extends App_model {

    public $_table_name = 'about';
    public $_alias = 'About';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>