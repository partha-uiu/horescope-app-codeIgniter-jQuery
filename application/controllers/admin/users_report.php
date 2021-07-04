<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Users_report extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->ahruser->Admin('RequireAccess');
        $this->load->model('user_model', 'User');
        $this->load->model('trait_model','Trait');
    }


    public function index() {
        $this->title_for_layout = $this->params['hTitle'] = 'Users Report';
        $this->params['items'] = $this->User->result(array('conditions' => array('user_type' =>2)));
//        pr($this->params['items']);
//        exit();
    }
    
    public function download($userId){
        $this->title_for_layout = 'Download';
        $this->params['TNActive'] = 'Success'; //active top menu;
        
        $file_path = str_replace('\\', '/',  realpath(APPPATH .'../')) .'/assets/uploads/report';
        $file_name = 'Report_TechofBliss_'.$userId.'.pdf';
        $full_path = $file_path. '/' .$file_name;
        
        if (file_exists($full_path)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($full_path));
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($full_path));
                readfile($full_path);
//                exit;
            }
  }


}
