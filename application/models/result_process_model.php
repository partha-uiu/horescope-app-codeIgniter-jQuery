<?php

class Result_process_model extends App_model {

    public $_table_name = 'result_process';
    public $_alias = 'Result';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>