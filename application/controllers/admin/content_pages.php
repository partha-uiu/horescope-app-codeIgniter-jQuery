<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Content_pages extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->ahruser->Admin('RequireAccess');
        $this->load->model('user_model', 'User');
        $this->load->model('affiliate_model', 'Affiliate');
        $this->load->model('faqs_model', 'Faqs');
    }

    public function index() {
        $this->layout = FALSE;
        $this->template = FALSE;
        $this->title_for_layout = $this->params['hTitle'] = 'Content pages';    
        //echo 'This is FAQ';
        redirect(CPREFIX .'/content_pages/faqs');
    }
    public function faqs($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'FAQs';    
        $this->params['index'] = $id;
        $this->params['faqs'] = $this->Faqs->result(array('conditions'=>array(), 'order' => array('sort_order' => 'ASC')));
        
//        pr($_POST);
//        exit();
        
        if(isset($_POST['data']) || !empty($id)){
            $validationsRoles = array();
            if(!$this->ahrform->get('id')){
                $validationsRoles = array(
                   array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => 'required'
                    ),
                   array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => 'required'
                    ),
                   array(
                        'field' => 'sort_order',
                        'label' => 'Order',
                        'rules' => 'required'
                    )
                );
            }
            
            if ($validate = $this->Faqs->load_input_value()->validate($validationsRoles)) {
                $date = date("Y_m_d_H_i_s");                        
                $this->Faqs->modified = $date;                

                if ($this->Faqs->save(NULL, false)) {
                    $id = $this->Faqs->id;

                    $this->params['success'] = 'Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.';
                    redirect(CPREFIX . '/content_pages/faqs');
                } else {
                    $this->params['success'] = 'Informations could not saved. Please try again.';
                }
            }
            if (!empty($id)) {
                $this->ahrform->set($faqs = $this->Faqs->row($id));
            }
        }
    }
    
    function check_sort_order() {
        $order = $this->ahrform->get('sort_order');
        $response = $this->Faqs->is_field_exist(array('conditions' => array_filter(array(
                'sort_order' => $order,
                'id !=' => $this->ahrform->get('id') ? $this->ahrform->get('id') : ''
            ))));
        if ('jquery_validator' == ($requestby = $this->ahrform->get('requestby'))) {
            exit($response ? 'false' : 'true');
        }else{
            $this->form_validation->set_message('check_sort_order', 'This order already exist.');
            return $response ? false : true;
        }
        
    }
    
    public function delete($id = null) {
        $this->layout = FALSE;
        $this->template = FALSE;

        $redirect = site_url('/' . CPREFIX . '/content_pages');
        //get id/IDs from ajax request
        $id = $this->ahrform->get('id') ? $this->ahrform->get('id') : $id;

        if (empty($id) && !$this->input->is_ajax_request()) {
            $this->ahrsession->set_flash('Invalid id. Information could not delete. Please try agin', 'default', array(), 'warning');
            redirect($redirect);
        }
        $this->Faqs->deleteAll(array('conditions' => array('id' => $id)));		
        if ($this->input->is_ajax_request()) {
            exit(json_encode(array('status' => true, 'msg' => "Information has been deleted")));
        }
        redirect($redirect);
    }

}

