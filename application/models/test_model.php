<?php

class Test_model extends App_model {

    public $_table_name = 'test_upload';
    public $_alias = 'Test';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }

}

?>