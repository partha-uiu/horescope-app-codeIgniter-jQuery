<?php

class Trait_model extends App_model {

    public $_table_name = 'trait_header';
    public $_alias = 'trait_header';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>