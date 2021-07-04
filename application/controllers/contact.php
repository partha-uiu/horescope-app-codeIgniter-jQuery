<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Contact extends My_Controller {
	
	public function __construct() {
        parent::__construct();
        $this->load->model('user_model', 'User');
//        $this->load->model('products_model', 'Product');
//	$this->load->model('type_model', 'Type');
//	$this->load->model('product_settings_model', 'Common');  
    }

	public function index() {
        $this->title_for_layout = 'Contact';
        $this->params['TNActive'] = 'TNContact'; //active top menu;
        $this->layout = 'index';
    }

}
?>