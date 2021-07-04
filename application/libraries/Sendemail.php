<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class SendEmail {

    public function __construct() {

    }

    function contactusEmail($id = '') {
        if (empty($id))
            return false;

        $ci = & get_instance();
        $item = $ci->db->select("u.*", FALSE)->from('contactus u')->where('u.site_id', $ci->sitesettings->siteID)->where('u.id', $id)->get()->row();

        $ci->load->library('email');
        $body = "Hello, admin you have new inquire from {$item->name} <br/>
         Subject:
         {$item->subject}<br/>
         Email:
         {$item->email}<br/>
         Message:
         {$item->message}
         ";


        $email = & $ci->email;
        $email->mailtype = 'html';
        $email->from($item->email, $item->name);
        $email->to($ci->sitesettings->email->get("contactus")); //contact us email
        $email->subject($subject = 'Inquire email of ' . $_SERVER["SERVER_NAME"]);
        $email->message(nl2br($body));
        @$email->send();
    }

    function FanSignUpEmail($id, $row) {
        $password = '';

        $ci = & get_instance();
        $ResetKey = $ci->ahruser->User('ResetKey', $row);
        $signup_confirmation_link = site_url("signup_confirmation/$ResetKey");
        $mailTemplate = $ci->load->view($this->theme->get('loadViewPath') . '/email/signup_confirmation', compact('signup_confirmation_link'), true);


        $subject = "Sign Up confirmation of " . $_SERVER['SERVER_NAME'];

//        $body = "
//Hi, $row->first_name <br/><br/>
//Thank you for joining PalmBlade.Com
//<br/><br/>
//Your login informaion:<br/>
//Email: {$row->email_address}<br/>
//Password: {$password}<br/>
//<br/><br/>
//Thanks, <br/>
//The {$_SERVER["SERVER_NAME"]} Team
//    ";
$body = $mailTemplate;

        $ci->load->library('email');
        $ci->email->mailtype = 'html';
        $ci->email->from($ci->config->item('SiteEmail'));
        $ci->email->to($row->email_address);
        $ci->email->subject($subject);
        $ci->email->message($body);
        @$ci->email->send();
    }

}