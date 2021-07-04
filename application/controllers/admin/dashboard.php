<?php if (!defined('BASEPATH'))    
    exit('No direct script access allowed');

class Dashboard extends MY_Controller {  

    public function __construct() {                
        parent::__construct();        
        $this->ahruser->Admin('RequireAccess');        
        $this->load->model('user_model', 'User');        
//        $this->load->model('products_model', 'Products');
//        $this->load->model('category_model', 'Category');    
//        $this->load->model('funds_model', 'Funds');
//        $this->load->model('project_updates_model', 'Update');
    }        

    public function index() {        
        $this->title_for_layout = $this->params['hTitle'] = 'Dashboard';        
//        $this->params['campaigns'] = $this->Campaign->result(array(            
//            'conditions' => array(),            
//            'order' => array('id' => "DESC"),            
//            'limit' => 10));    
//        $this->params['items'] = $this->Products->result(array(
//           'conditions' => array(),
//           'order' => array('id' => "DESC"),
//           'limit' => 10));
//        $this->params['funds'] = $this->Funds->result(array(            
//            'conditions' => array(),            
//            'order' => array('id' => "DESC"),            
//            'limit' => 10));    
//        $this->params['update'] = $this->Update->result(array(            
//            'conditions' => array(),            
//            'order' => array('id' => "DESC"),            
//            'limit' => 10));    
   }
}