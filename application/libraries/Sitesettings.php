<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sitesettings {

    var $general = '';
    var $email = '';
    var $payment;
    var $site_id;
    var $asset_file_url;
    private $isInit = false;

    public function __construct() {
        if ($this->isInit === false) {
            $this->payment = new stdClass();
            $this->initialize();
        }
    }

    public function initialize($params = '') {

        //set url "get method" value inthe form
        $CI = & get_instance(); //codeigneter instance. project instance define personaly
        $CI->lang->load('default', 'language');
        $CI->load->library('fn');

        $this->site_id = $CI->config->item('site_id');
        $this->asset_file_url = site_url("assets/files/{$this->site_id}");

    


    }

    public function get($name, $print = false) {
        if (!property_exists($this, $name))
            return false;
        if ($print)
            echo $this->{$name};
        else
            return $this->{$name};
    }

    public function set($name, $value = '') {
        if (empty($name))
            return false;
        else if (is_array($name) || is_object($name)) {
            $resetParams = $name;
            foreach ($resetParams as $key => $val)
                $this->{$key} = $val;
        }
        else
            $this->{$name} = $value;
    }

    public function delete($name, $print = false) {
        if (!property_exists($this, $name))
            return false;
        unset($this->{$name});
        return TRUE;
    }

}