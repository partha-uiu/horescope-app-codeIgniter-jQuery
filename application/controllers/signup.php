<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Signup extends My_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model', 'User');
    }

    public function index() {
//        $this->layout = 'index';
//        $this->title_for_layout = 'SignUp';
        $this->layout = False;
        $this->Template = False;
        
        $firstname = strtolower($this->ahrform->get('firstname'));
        $lastname = strtolower($this->ahrform->get('lastname'));
        $username = $firstname;
        $birthday = $this->ahrform->get('month').'/'.$this->ahrform->get('day').'/'.$this->ahrform->get('year');
        $sex = $this->ahrform->get('sex');
        $email = $this->ahrform->get('email');
        $password = md5($this->ahrform->get('password'));
        
        $check_user = $this->User->row(array('conditions'=>array('birthday'=>$birthday,'fname'=>$firstname)));
        if($check_user){
          $data = array(
               'fname' => $firstname,
               'lname' => $lastname,
               'username' => $username,
               'birthday' => $birthday,
               'gender' => $sex,
               'email' => $email,
               'password' => $password,
               'user_type' => 2
            );
          
          $this->db->where('id', $check_user->id);
          $this->db->update('hr_users', $data);
          redirect('login');
        }
        else{
          $this->User->set('fname', $firstname);
          $this->User->set('lname', $lastname);
          $this->User->set('username', $username);
          $this->User->set('email', $email);
          $this->User->set('password', $password);
          $this->User->set('birthday', $birthday);
          $this->User->set('gender', $sex);
          $this->User->set('user_type', 2);
          
          $this->User->save();
          redirect('login');
        }

    }
    
//    public function sign_up_user() {
//
//           $this->layout = FALSE;      
//           
//   // --------  insert into user table --------------    
//           
//          $fname = $this->ahrform->get('fname');
//          $lname = $this->ahrform->get('lname');
//          $username = $this->ahrform->get('fname').' '.$this->ahrform->get('lname');
//          $company_name = $this->ahrform->get('company_name');
//          $address = $this->ahrform->get('address');
//          $postcode = $this->ahrform->get('postcode');
//          $email = $this->ahrform->get('email');
//          $phone = $this->ahrform->get('phone');
//          $password = md5($this->ahrform->get('password'));
//         
//          $this->User->set('fname', $fname);
//          $this->User->set('lname', $lname);
//          $this->User->set('username', $username);
//          $this->User->set('company_name', $company_name);
//          $this->User->set('address', $address);
//          $this->User->set('postcode', $postcode);
//          $this->User->set('email', $email);
//          $this->User->set('password', $password);
//          $this->User->set('phone', $phone);
//          $this->User->set('group_id', 2);
//          
//          $this->User->save();
//          
//          exit(json_encode(array('status'=>true,'msg'=>'success')));
//
//       }
}
