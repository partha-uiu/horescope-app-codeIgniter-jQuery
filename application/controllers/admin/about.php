<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class About  extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->ahruser->Admin('RequireAccess');
        $this->load->model('user_model', 'User');
        $this->load->model('quote_model', 'Quote');
        $this->load->model('about_model', 'About');
        
    }


    public function add_about($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Add About Text';
        $this->params['index'] = $id;


        if (isset($_POST['data']) || (!empty($id))) {
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' About';
            $validationsRoles = array(
                array(
                    'field' => 'description',
                    'label' => 'About Text',
                    'rules' => 'required'
                )
            );

            if ($validate = $this->About->load_input_value()->validate($validationsRoles)) {
                $date = date("Y_m_d_H_i_s");


                if (empty($id)) {
                    $this->About->created_date = $date;
                    $this->About->modified_date = $date;
                } else {
                    $this->About->modified_date = $date;
                }

                if ($this->About->save(NULL, false)) {

//                pr($_POST);
                    $id = $this->About->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.', 'default', array(), 'success');

                        redirect(site_url(CPREFIX . '/about/about_list/'));
                    
                } else {
                    $this->ahrsession->set_flash(' Information could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($this->About->row($id));
            }
            
        }

    }


    public function about_list() {
        $this->title_for_layout = $this->params['hTitle'] = 'About Us';
//        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>0), 'order' => array('trait_id'=>"ASC")));
        $this->params['items'] = $this->About->result(array('About' => array('id'=>"ASC")));
//        pr($this->params['items']);

    }


    public function delete_about($id = null) {
        $this->layout = FALSE;
        $this->template = FALSE;

        $redirect = site_url('/' . CPREFIX . '/about/about_list');
        //get id/IDs from ajax request
        $id = $this->ahrform->get('id') ? $this->ahrform->get('id') : $id;

        if (empty($id) && !$this->input->is_ajax_request()) {
            $this->ahrsession->set_flash('Invalid id. Information could not delete. Please try again', 'default', array(), 'warning');
            redirect($redirect);
        }
        $this->Quote->deleteAll(array('conditions' => array('id' => $id)));

        if ($this->input->is_ajax_request()) {
            exit(json_encode(array('status' => true, 'msg' => "Information has been deleted")));
        }
        redirect($redirect);
    }


}