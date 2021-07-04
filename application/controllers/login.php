<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Login extends My_Controller {
	
	public function __construct() {
        parent::__construct();
        $this->load->model('user_model', 'User');
//        $this->load->model('products_model', 'Product');
//	$this->load->model('type_model', 'Type');
//	$this->load->model('product_settings_model', 'Common');  
    }

	public function index() {
        $this->title_for_layout = 'Login';
        $this->params['TNActive'] = 'TNLogin'; //active top menu;
        $this->layout = 'index';
    }


    public function login(){
    	$this->title_for_layout = 'Login';
    }

    public function check_login(){
        $this->layout = FALSE;
        
      $email = $this->ahrform->get('email');
      $password = md5($this->ahrform->get('password'));
      $check = $this->User->row(array('conditions'=>array('email'=>$email,'password'=>$password)));
      
      if($check){  
          $id= $check->id;
          $data1 = array(
                    'user_id' => $check->id,
                    'user_name' => $check->fname,
                    'e_mail' => $check->email,
                    'user_type' => $check->user_type
                    );
            $this->session->set_userdata($data1);
            
            $file_path = str_replace('\\', '/',  realpath(APPPATH .'../')) .'/assets/uploads/report';
            $file_name = 'Report_TechofBliss_'.$id.'.pdf';
            $full_path = $file_path. '/' .$file_name;

            if (!file_exists($full_path)) {

                redirect('home/getReport'.'/'.$id);
            }
            else {

                redirect('home/your_report'.'/'.$id);
            }
//            redirect('home');
      }
      else{
//          exit(json_encode(array('status'=>false,'msg'=>'error')));
          redirect('login');
      }
             

    }

    public function logout() {
        $sess_array1 = array(
                        'id' => '',
                        'user_name' => '',
                        'e_mail' => '',
                        'user_type' => ''
                        );
        $this->session->unset_userdata($sess_array1);
        redirect('home');
    }

}
?>