<?php

class Testimonial_model extends App_model {

    public $_table_name = 'testimonial';
    public $_alias = 'Testimonial';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }



}

?>