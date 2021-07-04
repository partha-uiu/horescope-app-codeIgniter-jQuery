<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Users extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->ahruser->Admin('RequireAccess');
        $this->load->model('user_model', 'User');
        $this->load->model('groups_model', 'Groups');
        $this->load->model('projects_model', 'Campaign');
        $this->load->model('user_project_model', 'User_project');
        $this->load->model('project_funds_model', 'Project_funds');
        $this->load->model('project_updates_model', 'Updates');
        $this->load->model('user_wepay_model', 'User_wepay');
    }

    public function index() {
        $this->title_for_layout = $this->params['hTitle'] = 'User';
        // $allowUserToFindList = array_keys($this->Group->getAllowUserGroupsToFindList());
        $this->params['items'] = $this->User->result(array('conditions' => array(), 'order' => array('id' => "DESC")));
    }
    public function administrators($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Administrator';
        // $allowUserToFindList = array_keys($this->Group->getAllowUserGroupsToFindList());
        $this->params['items'] = $this->User->result(array('conditions' => array('group_id' => 1), 'order' => array('id' => "DESC")));
        $this->params['index'] = $id;
//        $this->params['totalActiveUsers'] = $this->User->result(array('conditions' => array('group_id !=' => 1,'is_active' => 1)));
//        $this->params['totalInctiveUsers'] = $this->User->result(array('conditions' => array('group_id !=' => 1,'is_active !=' => 1)));
//        $this->params['totalFacebookUsers'] = $this->User->result(array('conditions' => array('group_id !=' => 1,'oauth_provider' => 'facebook')));
//        $this->params['totalEmailUsers'] = $this->User->result(array('conditions' => array('group_id !=' => 1)));
        if(isset($_POST['data']) || !empty($id)){
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' User';
            $validationsRoles = array(
                array(
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => 'required|valid_email'
                )
            );

            if (!$this->ahrform->get('id') && empty($id)) {
                $validationsRoles[] = array(
                    'field' => 'password',
                    'label' => 'Password',
                    'rules' => 'required|min_length[6]'
                );
                $validationsRoles[] = array(
                    'field' => 'username',
                    'label' => 'User Name',
                    'rules' => 'required|callback_check_username'
                );
            }
            if ($validate = $this->User->load_input_value()->validate($validationsRoles)) {
                if (!$this->ahrform->get('id') && empty($id)) {
                    $password = $this->ahruser->password($this->ahrform->get('password'));
                    $this->User->set('password', $password);
                    $this->User->set('created_at', date("Y_m_d_H_i_s"));
                    $this->User->set('modified_at', date("Y_m_d_H_i_s"));
                    $ip = empty($_SERVER['REMOTE_ADDR']) ? '127.0.0.0' : $_SERVER['REMOTE_ADDR'];
                    $this->User->set('user_ip', $ip);
                } else {
                    $this->User->set('modified_at', date("Y_m_d_H_i_s"));
                }

                if ($this->User->save(NULL, false)) {

                    $id = $this->User->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated ')  . ' successfully.', 'default', array(), 'success');

                    redirect(site_url(CPREFIX . '/users/administrators'));
                } else {
                    $this->ahrsession->set_flash('Informations could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($user = $this->User->row($id));
            }
            $this->params['Groups'] = $this->Groups->get_list(array('conditions' => array(), 'fields' => array('id', 'name')));
        }
    }
    public function user_list($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'User List';
        // $allowUserToFindList = array_keys($this->Group->getAllowUserGroupsToFindList());
        $project_table = $this->db->dbprefix('projects');
        $this->params['items'] = $this->User->result(array(
            'fields' => array(
                '*',
                "(SELECT COUNT(*) FROM $project_table as project where project.user_id = users.id) AS campaign_count",
                "(SELECT SUM(collected_amount) FROM $project_table as project where project.user_id = users.id) AS donation_count"
                ),
            'conditions' => array('group_id !=' => 1), 
            'order' => array('id' => "DESC"),            
//            'groupby' => array('users.id')
            ));
        
       // pr( $this->Campaign->get_last_query() );
        //pr($this->params['items']);
        
        $this->params['index'] = $id;
        $this->params['totalActiveUsers'] = $this->User->result(array('conditions' => array('group_id !=' => 1,'is_active' => 1)));
        $this->params['totalInctiveUsers'] = $this->User->result(array('conditions' => array('group_id !=' => 1,'is_active !=' => 1)));
        $this->params['totalFacebookUsers'] = $this->User->result(array('conditions' => array('group_id !=' => 1,'oauth_provider' => 'facebook')));
        $this->params['totalEmailUsers'] = $this->User->result(array('conditions' => array('group_id !=' => 1)));
        if(isset($_POST['data']) || !empty($id)){
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' User';
            $validationsRoles = array(
                array(
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => 'required|valid_email'
                )
            );

            if (!$this->ahrform->get('id') && empty($id)) {
                $validationsRoles[] = array(
                    'field' => 'password',
                    'label' => 'Password',
                    'rules' => 'required|min_length[6]'
                );
                $validationsRoles[] = array(
                    'field' => 'username',
                    'label' => 'User Name',
                    'rules' => 'required|callback_check_username'
                );
            }
            if ($validate = $this->User->load_input_value()->validate($validationsRoles)) {
                if (!$this->ahrform->get('id') && empty($id)) {
                    $password = $this->ahruser->password($this->ahrform->get('password'));
                    $this->User->set('password', $password);
                    $this->User->set('created_at', date("Y_m_d_H_i_s"));
                    $this->User->set('modified_at', date("Y_m_d_H_i_s"));
                    $ip = empty($_SERVER['REMOTE_ADDR']) ? '127.0.0.0' : $_SERVER['REMOTE_ADDR'];
                    $this->User->set('user_ip', $ip);
                } else {
                    $this->User->set('modified_at', date("Y_m_d_H_i_s"));
                }

                if ($this->User->save(NULL, false)) {

                    $id = $this->User->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated ') . ' successfully.', 'default', array(), 'success');

                    redirect(site_url(CPREFIX . '/users/user_list'));
                } else {
                    $this->ahrsession->set_flash('Informations could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($user = $this->User->row($id));
            }
            $this->params['Groups'] = $this->Groups->get_list(array('conditions' => array(), 'fields' => array('id', 'name')));
        }
    }
    

    public function add($id = null) {
        $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' User';
        $validationsRoles = array(
            
            array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'required|valid_email'
            )
        );

        if (!$this->ahrform->get('id') && empty($id)) {
            $validationsRoles[] = array(
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|min_length[6]'
            );
            $validationsRoles[] = array(
                'field' => 'username',
                'label' => 'User Name',
                'rules' => 'required|callback_check_username'
            );
        }
        if ($validate = $this->User->load_input_value()->validate($validationsRoles)) {
            if (!$this->ahrform->get('id') && empty($id)) {
                $password = $this->ahruser->password($this->ahrform->get('password'));
                $this->User->set('password', $password);
                $this->User->set('created_at', date("Y_m_d_H_i_s"));
                $this->User->set('modified_at', date("Y_m_d_H_i_s"));
                $ip = empty($_SERVER['REMOTE_ADDR']) ? '127.0.0.0' : $_SERVER['REMOTE_ADDR'];
                $this->User->set('user_ip', $ip);
            }else{
                $this->User->set('modified_at', date("Y_m_d_H_i_s"));               
            }  

            if ($this->User->save(NULL, false)) {

                $id = $this->User->id;
                $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated ') . ' ' . $msg . ' successfully.', 'default', array(), 'success');

                redirect(site_url(CPREFIX . '/users'));
            } else {
                $this->ahrsession->set_flash('Informations could not saved. Please try again.', 'default', array(), 'warning');
            }
        }

        if (!empty($id)) {
            $this->ahrform->set($user = $this->User->row($id));
        }
        $this->params['Groups'] = $this->Groups->get_list(array('conditions' => array(), 'fields' => array('id', 'name')));
    }

    public function change_password($id = null) {
        $this->title_for_layout = $this->params['hTitle'] = "Change Password";
        $validationsRoles = array(
            array(
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|min_length[6]'
            )
        );
        $this->ahrform->set('id', $id);

        $gid = $this->User->row($id)->group_id;
        $uref = '';
        if ($gid == 1) {
            $uref = 'administrators';
        } else {
            $uref = 'user_list';
        }
        
        if ($validate = $this->User->load_input_value()->validate($validationsRoles)) {
            $password = $this->ahruser->password($this->ahrform->get('password'));
            if ($this->User->set('id', $id)->set('password', $password)->save(NULL, false, array('password'))) {
                $this->ahrsession->set_flash('Password has been changed successfully.', 'default', array(), 'success');
                redirect(site_url(CPREFIX . '/users/'.$uref));
            } else {
                $this->ahrsession->set_flash('Password could not changed. Please try again.', 'default', array(), 'warning');
            }
        }
    }

    public function delete($id = null) {
        $this->layout = FALSE;
        $this->template = FALSE;

        $redirect = site_url('/' . CPREFIX . '/users/user_list');
        //get id/IDs from ajax request
        $id = $this->ahrform->get('id') ? $this->ahrform->get('id') : $id;

        if (empty($id) && !$this->input->is_ajax_request()) {
            $this->ahrsession->set_flash('Invalid id. Information could not delete. Please try agin', 'default', array(), 'warning');
            redirect($redirect);
        }

        $this->email_template->send_mail('admin-user-delete',$id);
        
        $projects = $this->User_project->result(array('conditions' => array('user_id' => $id)));
        foreach ($projects as $project){
            $this->Project_funds->deleteAll(array('conditions' => array('project_id' =>$project->project_id)));
        }
        $this->Updates->deleteAll(array('conditions' => array('user_id' => $id)));
        $this->User_wepay->deleteAll(array('conditions' => array('fmp_user_id' => $id)));
        $this->Campaign->deleteAll(array('conditions' => array('user_id' => $id)));
        $this->User_project->deleteAll(array('conditions' => array('user_id' => $id)));
        $this->User->deleteAll(array('conditions' => array('id' => $id)));
			
        if ($this->input->is_ajax_request()) {
            exit(json_encode(array('status' => true, 'msg' => "Information has been deleted")));
        }
        redirect($redirect);
    }

    function check_username() {
        $response = $this->User->is_field_exist(array('conditions' => array_filter(array(
                'username' => $this->ahrform->get('username'),
                'id !=' => $this->ahrform->get('id') ? $this->ahrform->get('id') : '',
            ))));

        if ('jquery_validator' == ($requestby = $this->ahrform->get('requestby'))) {
            exit($response ? 'false' : 'true');
        }
        $this->form_validation->set_message('check_username', 'Username address already exist.');
        return $response ? false : true;
    }

    public function has_username($val) {
        return $this->User->has_username($val);
    }

    public function has_email($val) {
        return $this->User->has_email($val);
    }

}

