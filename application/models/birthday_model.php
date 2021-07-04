<?php

class Birthday_model extends App_model {

    public $_table_name = 'birthday';
    public $_alias = 'Birthday';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }

}

?>