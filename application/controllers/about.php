<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class About extends My_Controller {
	
	public function __construct() {
        parent::__construct();
        $this->load->model('user_model', 'User');
         $this->load->model('about_model', 'About');
 
    }

	public function index() {
        $this->title_for_layout = 'About';
        $this->params['TNActive'] = 'TNAbout'; //active top menu;
        $this->layout = 'index';
        $this->params['items'] = $this->About->result(array('About' => array('id'=>"ASC")));
    }

}
?>