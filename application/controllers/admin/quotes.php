<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Quotes   extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->ahruser->Admin('RequireAccess');
        $this->load->model('user_model', 'User');
        $this->load->model('trait_model','Trait');
        $this->load->model('quote_model', 'Quote');
        
    }


    public function add_quotes($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Add Quotes';
        $this->params['index'] = $id;


        if (isset($_POST['data']) || (!empty($id))) {
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' Quote';
            $validationsRoles = array(
                array(
                    'field' => 'quote',
                    'label' => 'Quotes',
                    'rules' => 'required'
                )
            );

            if ($validate = $this->Quote->load_input_value()->validate($validationsRoles)) {
                $date = date("Y_m_d_H_i_s");


                if (empty($id)) {
                    $this->Quote->created_date = $date;
                    $this->Quote->modified_date = $date;
                } else {
                    $this->Quote->modified_date = $date;
                }

                if ($this->Quote->save(NULL, false)) {

//                pr($_POST);
                    $id = $this->Quote->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.', 'default', array(), 'success');

                        redirect(site_url(CPREFIX . '/quotes/quotes_list/'));
                    
                } else {
                    $this->ahrsession->set_flash(' Information could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($this->Quote->row($id));
            }
            
        }

    }


    public function quotes_list() {
        $this->title_for_layout = $this->params['hTitle'] = 'Quote List';
//        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>0), 'order' => array('trait_id'=>"ASC")));
        $this->params['items'] = $this->Quote->result(array('Quote' => array('id'=>"ASC")));
//        pr($this->params['items']);

    }


    public function delete_quotes($id = null) {
        $this->layout = FALSE;
        $this->template = FALSE;

        $redirect = site_url('/' . CPREFIX . '/quotes/quotes_list');
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