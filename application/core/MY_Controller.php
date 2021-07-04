<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    var $siteID = '';

    public function __construct() {
        parent::__construct();


        $debug = $this->config->item('debug');
        if ($debug == true)
            $this->output->enable_profiler(true);

        //maked DB Table list without prefix
        $tables = $this->db->list_tables();
        $this->db->tables = new stdClass();
        if ($tables) {
            foreach ($tables as $table) {
                $tableWithoutPrefix = preg_replace("/^{$this->db->dbprefix}/", '', $table);
                $this->db->tables->$tableWithoutPrefix = $table;
            }
        }


        //autoload options by settings
        $this->load->model("Option_model", 'Option');
        $autoLoads = $this->Option->getOptions(NULL, true);
        $this->sitesettings->set($autoLoads);



        /** admin auth checking */
        /*       if (!empty($this->router->current_prefix_inherited) && $this->router->current_prefix_inherited == 'admin') {
          //public user class
          if ($this->router->class == 'pusers') {

          } else {
          if (!$this->ahruser->isLoged())
          redirect(site_url($this->ahruser->loginURL));
          }
          } */
    }
    function randomAlphaNumber($length = 4, $case = "both") {
        $original_string = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $original_string = implode("", $original_string);

        $case = strtolower($case);
        if ($case == 'lower')
            $original_string = strtolower($original_string);
        if ($case == 'upper')
            $original_string = strtoupper($original_string);

        return substr(str_shuffle($original_string), 0, $length);
    }

    function randomNumber($length = 4) {
        $original_string = array_merge(range(0, 9), range(0, 9), range(0, 9));
        $original_string = implode("", $original_string);
        return substr(str_shuffle($original_string), 0, $length);
    }



    /*
     * Common controller function
     */
    public function index(){
        
    }
}