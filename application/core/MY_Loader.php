<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

/* load the MX_Loader class */

//require APPPATH."third_party/MX/Loader.php";
//
//class MY_Loader extends MX_Loader {}
class MY_Loader extends CI_Loader {

    /**
     * Initialize the Loader
     *
     * This method is called once in CI_Controller.
     *
     * @param 	array
     * @return 	object
     */
    public function initialize() {

        parent::initialize();

//        $this->helper('url');
//        $this->database();
//
//
//
        require_once rtrim(realpath(APPPATH), '/') . "/core/MY_Theme.php";

        global $BM, $CFG;
        $CI = & get_instance();
        $CI->theme = new MY_Theme();

//        $co = ucwords(strtolower($CI->theme->current_prefix));
//        $GLOBALS['App_Controller'] = "MY_" . (!empty($co) ? $co . '_controller' : 'Controller') . '';

        return $this;
    }

    public function view($view, $vars = array(), $return = FALSE) {
        $CI = & get_instance();

        //if use auto layout & view
        if (isset($CI->ALV) && method_exists($CI->ALV, 'getParams')) {
            $params = $CI->ALV->getParams();
            $params = empty($params) ? array() : $params;
            $vars = array_merge($vars, $params);
        }
        
        //resolve viewpath
        if (is_string($view)) {
            $view = (trim(str_replace((trim(APPPATH, '/') . '/views'), '', $view), '/'));
        }
        
        return parent::view($view, $vars, $return);
    }

}