<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Output extends CI_Output {

//	var $suffix = '_controller';
    var $suffix = '';

    function __construct() {
        parent::__construct();
    }

    function _display($output = '') {
        
        global $BM, $CFG;

        //render layout and view
        require_once rtrim(realpath(APPPATH), '/') . "/core/MY_Auto_layout_and_view.php";
        $CI = & get_instance();
        $CI->ALV = new MY_Auto_layout_and_view();
        $CI->ALV->render_layout_view();

        parent::_display($output);
    }

}