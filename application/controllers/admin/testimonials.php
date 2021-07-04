<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Testimonials   extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->ahruser->Admin('RequireAccess');
        $this->load->model('user_model', 'User');
        $this->load->model('trait_model','Trait');
        $this->load->model('quote_model', 'Quote');
        $this->load->model('testimonial_model', 'Testimonial');
        
    }


    public function add_testimonial($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Add Testimonial';
        $this->params['index'] = $id;


        if (isset($_POST['data']) || (!empty($id))) {
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' Testimonial';
            $validationsRoles = array(
                array(
                    'field' => 'testimonial',
                    'label' => 'Testimonial',
                    'rules' => 'required'
                )
            );

            if ($validate = $this->Testimonial->load_input_value()->validate($validationsRoles)) {
                $date = date("Y_m_d_H_i_s");


                if (empty($id)) {
                    $this->Testimonial->created_date = $date;
                    $this->Testimonial->modified_date = $date;
                } else {
                    $this->Testimonial->modified_date = $date;
                }

                if ($this->Testimonial->save(NULL, false)) {

//                pr($_POST);
                    $id = $this->Testimonial->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.', 'default', array(), 'success');

                        redirect(site_url(CPREFIX . '/testimonials/testimonial_list/'));
                    
                } else {
                    $this->ahrsession->set_flash(' Information could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($this->Testimonial->row($id));
            }
            
        }

    }


    public function testimonial_list() {
        $this->title_for_layout = $this->params['hTitle'] = 'Testimonial List';
//        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>0), 'order' => array('trait_id'=>"ASC")));
        $this->params['items'] = $this->Testimonial->result(array('Testimonial' => array('id'=>"ASC")));
//        pr($this->params['items']);

    }


    public function delete_testimonial($id = null) {
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