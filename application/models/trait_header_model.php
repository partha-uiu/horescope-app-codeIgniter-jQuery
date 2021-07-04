<?php

class Trait_header_model extends App_model {

    public $_table_name = 'trait_header';
    public $_alias = 'THeader';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>