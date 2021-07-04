<?php

class Order_model extends App_model {

    public $_table_name = 'order';
    public $_alias = 'order';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>