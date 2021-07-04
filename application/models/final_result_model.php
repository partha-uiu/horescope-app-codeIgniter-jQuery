<?php

class Final_result_model extends App_model {

    public $_table_name = 'final_result';
    public $_alias = 'Final';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>