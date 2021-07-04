<?php

class Trait_details_model extends App_model {

    public $_table_name = 'trait_details';
    public $_alias = 'TDetails';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>