<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  dependency:
 * "ahrsession" class
  Note: set "loginID" on config file either systen auto set "loginID"
 */

class Ahruser {

    var $getLogedinUser = null;
    var $loginID = '';
    var $loginURL = '';
    var $loginSuccessURL = '';
    var $logoutURL = '';
    var $validateUserType = '';
    var $getDisplayName = ''; //first_name + middle_name + last_name

    public function __construct() {
        $CI = &get_instance();

        if (defined('CPREFIX') && CPREFIX) {
            $prefix = strtolower(CPREFIX);
            $this->validateUserType = 'Administrator'; // check is admin
            $this->loginID = ($loginID = $CI->config->item($prefix . 'LoginID')) ? $loginID : ('_' . $prefix . '_' . $_SERVER['SERVER_NAME']);
            $this->loginURL = ($loginURL = $CI->config->item($prefix . 'LoginURL')) ? $loginURL : $prefix;
            $this->loginSuccessURL = ($loginSuccessURL = $CI->config->item($prefix . 'LoginSuccessURL')) ? $loginSuccessURL : $prefix . '/dashboard';
            $this->logoutURL = ($logoutURL = $CI->config->item($prefix . 'LogoutURL')) ? $logoutURL : $prefix . '/logout';
        } else {
            $this->loginID = ($loginID = $CI->config->item('loginID')) ? $loginID : ('__' . $_SERVER['SERVER_NAME']);
            $this->loginURL = ($loginURL = $CI->config->item('loginURL')) ? $loginURL : ''; //login
            $this->loginSuccessURL = ($loginSuccessURL = $CI->config->item('loginSuccessURL')) ? $loginSuccessURL : 'myaccount';
            $this->logoutURL = ($logoutURL = $CI->config->item('logoutURL')) ? $logoutURL : 'logout';
        }
    }

    public function logout() {
        $CI = &get_instance();
        return ($CI->ahrsession->delete($this->loginID)) ? true : false;
        //return $CI->ahrsession->destroy();
    }

    function password($pass) {
        return $password = SHA1($pass);
    }

    /*
     * @method IsUser($action = 'Loggedin')
     * @method IsUser($action = 'Loggedin')
     */

    function IsUser($action = 'Loggedin') {
        $CI = &get_instance();
        $response = false;
        $action = (@$action);
        $numargs = func_get_args();
        switch ($action) {

            case 'Panel':
                $response = (defined('CPREFIX') && 'admin' !== strtolower(CPREFIX));
                break;

            case 'Loggedin':
                $response = ($this->IsUser('Panel') && $CI->ahrsession->get($this->loginID));
                break;

            case 'Disabled':
                $user_id = @$numargs[1];
//                        $this->User('RequireAccess');
                !empty($user_id) OR $user_id = $this->User('id');
                if (!$user_id)
                    return TRUE;

                $CI->load->model('User_model', 'UserModel');
                //$CI->load->model('Payment_model', 'PaymentModel');

                $response = $row = $CI->PaymentModel->row(array(
                    'conditions' => array(
                        'reference_id' => $user_id,
//                        'payment_for' => 'bar_monthly_subscription',
//                        'status' => 'success',
//                                "DATE_FORMAT(created, '%m-%Y') = DATE_FORMAT(CURDATE(), '%m-%Y')"
                    ),
                    'order' => array('id' => 'DESC')
                ));


                if (empty($response)) {
                    $user = $CI->UserModel->row($user_id);
                    // Return the number of days between the two dates:
                    $dateDiff = round(abs(time() - strtotime($user->created)) / 86400);

                    return ($dateDiff > 7 ? true : false);
                }

                return false;

//                        pr($CI->PaymentModel->get_last_query());
//                        pr($response);

                break;

            case 'TrialPeriod':
                $user_id = @$numargs[1];
                $this->User('RequireAccess');

                !empty($user_id) OR $user_id = $this->User('id');
                if (!$user_id)
                    return TRUE;

                $CI->load->model('User_model', 'UserModel');
               // $CI->load->model('Payment_model', 'PaymentModel');

                $response = $row = $CI->PaymentModel->row(array(
                    'conditions' => array(
                        'reference_id' => $user_id,
//                        'payment_for' => 'bar_monthly_subscription',
//                        'status' => 'success',
//                                "DATE_FORMAT(created, '%m-%Y') = DATE_FORMAT(CURDATE(), '%m-%Y')"
                    ),
                    'order' => array('id' => 'DESC')
                ));


                if (empty($response)) {
                    $user = $CI->UserModel->row($user_id);
                    // Return the number of days between the two dates:
                    $dateDiff = round(abs(time() - strtotime($user->created)) / 86400);

                    return ($dateDiff <= 7 ? true : false);
                }

                return false;
                break;
        }
        return (bool) $response;
    }

    function User($action = 'Login') {
        $CI = &get_instance();
        $response = false;
        $action = (@$action);
        $numargs = func_get_args();

        

        switch ($action) {
            case 'LoginRedirect':
                redirect(site_url($this->loginSuccessURL));
                break;
            case 'Logout':
                $CI->ahrsession->delete($this->loginID);
                redirect(site_url($this->loginURL));
                break;
            case 'RequireAccess':
                if (!$CI->ahrsession->get($this->loginID))
                    redirect(site_url($this->loginURL));
                break;
            case 'RequireASite':
                $CI->load->model('User_site_model', 'UserSite');
                $user_id = $this->User('id');
                $exist = $CI->UserSite->exist(array('conditions' => array('user_id' => $user_id)));
                $exist ? true : redirect('user_sites/add');
                break;
            case 'Login':
                if (@$numargs[1]) {
                    $userID = $numargs[1];
                    $CI->load->model('User_model', 'UserModel');
                    //save last loggedin info
                    $CI->UserModel->save(array('last_loggedin' => date('Y-m-d h:i:s'), 'id' => $userID), false, array('last_loggedin'));
                    $LoginInfo = array('Token' => uniqid(), 'User' => $CI->User->row($userID), "LoggedAt" => date('Y-m-d h:i:s'));
                    $CI->ahrsession->set('CurrentLoggedInInfo_' . $this->loginID, $LoginInfo);
                    $CI->ahrsession->set($this->loginID, $userID);
                    return true;

                }
                return FALSE;
                break;
            case 'id':
            case 'ID':
                return $response = $CI->ahrsession->get($this->loginID);
                break;

            case 'ResetKey':
                if (@$numargs[1]) {
                    if (is_array($numargs[1]))
                        $numargs[1] = (object) $numargs[1];

                    $response = @md5(@$numargs[1]->id, $numargs[1]->email);
                    return $response;
                }
                break;
            case 'getLoggedinUser':
                $CI->load->model('User_model', 'UserModel');
                $user_id = $this->User('id');
                return $CI->UserModel->row($user_id);
                break;
        }
    }

    /*
     * @method IsAdmin('Panel')
     * @method IsAdmin('Loggedin')
     */

    function IsAdmin($action = 'Panel') {
        $CI = &get_instance();
        $response = false;
        $action = (@$action);
        switch ($action) {
            case 'Panel':
                $response = (defined('CPREFIX') && 'admin' == strtolower(CPREFIX));
                break;
            case 'Loggedin':
                $response = ($this->IsAdmin('Panel') && $CI->ahrsession->get($this->loginID));
                break;
        }
        return (bool) $response;
    }

    /*
     * @method Admin('LoginRedirect')
     */

    function Admin($action = 'Panel') {
        $CI = &get_instance();
        $response = false;
        $action = (@$action);
        $numargs = func_get_args();

        switch ($action) {
            case 'LoginRedirect':
                redirect(site_url($this->loginSuccessURL));
                break;
            case 'Logout':
                $CI->ahrsession->delete($this->loginID);
                redirect(site_url($this->loginURL));
                break;
            case 'RequireAccess':
                if (!$this->IsAdmin('Loggedin')) {
                    redirect(site_url($this->loginURL));
                }
                break;
            case 'Login':
                if (@$numargs[1]) {
                    $userID = $numargs[1];
                    return $CI->ahrsession->set($this->loginID, $userID);
                }
                break;
            case 'GroupIDForAccess':
                $prefixes = $CI->config->item('prefixes');
                $response = (!empty($prefixes['admin']['group_id_for_access']) ? $prefixes['admin']['group_id_for_access'] : array(1, 2));
                return $response;
                break;
        }
    }

}
