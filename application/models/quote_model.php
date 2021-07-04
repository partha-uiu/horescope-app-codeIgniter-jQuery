<?php

class Quote_model extends App_model {

    public $_table_name = 'quote';
    public $_alias = 'Quote';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>