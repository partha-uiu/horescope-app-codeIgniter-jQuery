<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Home extends My_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->model('user_model', 'User');
        $this->load->model('name_value_model', 'name_value');
        $this->load->model('birthday_model', 'Birthday');
//        $this->load->model('data_male_model', 'Male');
//        $this->load->model('data_female_model', 'Female');
        $this->load->model('trait_header_model', 'THeader');
        $this->load->model('trait_details_model', 'TDetails');
        $this->load->model('result_process_model', 'Result');
        $this->load->model('final_result_model', 'Final');
        $this->load->model('testimonial_model', 'Testimonial');
    }

    public function index() {

        $this->layout = "index";
        $this->title_for_layout = 'Home';
        $this->params['TNActive'] = 'TNHome'; //active top menu;
        if($this->ahruser->User('id')){
            $this->params['user_details'] = $this->User->row(array('conditions'=>array('id'=>$this->ahruser->User('id'))));
        }
        $this->params['testimonial'] = $this->Testimonial->result();
               
  }

    public function getReport($user_id=null) {

//        $this->layout = "index";
        $this->title_for_layout = 'Get Report';
        $this->params['TNActive'] = 'TNReport'; //active top menu;
        
        if($user_id){
            $this->params['userid'] = $user_id;
            $user_details = $this->User->row(array('conditions'=>array('id'=>$user_id))); 
            $this->params['fname'] = $user_details->fname; 
            $this->params['gender'] = $user_details->gender; 
            $for_birthday = explode("/",$user_details->birthday);
            $this->params['month'] = $for_birthday[0];
            $this->params['day'] = $for_birthday[1];
            $this->params['year'] = $for_birthday[2];
        }
        else {
             $this->params['userid'] = null;
            $this->params['fname'] = null; 
            $this->params['gender'] = 2; 
            $this->params['month'] = null;
            $this->params['day'] = null;
            $this->params['year'] = null;
        }
//        pr($for_birthday);
//        exit();
  }
    public function getReportdata($user_id=null) {
        
        $this->load->helper('pdf_helper');
//        $this->layout = "index";
        $this->title_for_layout = 'Get Report Data';
        $this->params['TNActive'] = 'TNReport'; //active top menu;
        
        $this->params['firstname'] = $firstname = strtolower($this->ahrform->get('firstname'));
//        $this->params['lastname'] =$lastname = $this->ahrform->get('lastname');
        $username = $firstname;
//        $this->params['country'] =$country = $this->ahrform->get('country');
//        $this->params['city'] =$city = $this->ahrform->get('state');
        $this->params['birthday'] =$birthday = $this->ahrform->get('month').'/'.$this->ahrform->get('day').'/'.$this->ahrform->get('year');
        $this->params['sex'] =$sex = $this->ahrform->get('sex');
        $split = str_split($firstname);
//        pr($firstname);
//        pr($birthday);
//        pr($sex);
//        pr($split);
//        exit();
        if($user_id){
          $this->params['user_id'] = $user_id;
        }
        else {           
            $this->User->set('fname', $firstname);
            $this->User->set('username', $username);
            $this->User->set('birthday', $birthday);
            $this->User->set('user_type', 0);

            $this->User->save();
            $this->params['user_id'] = $user_id = $this->User->id;
        }
        
 //-----------for key calculation----------------       
        $test=array();
        $sum=0;
        for($m=0;$m < count($split);$m++) {
        
        $get_number_details = $this->name_value->row(array('conditions'=>array('alphabet'=>$split[$m])));
        array_push($test,$get_number_details->number);
        $sum= $sum+$test[$m];
        
        }
//        pr($test);
//        pr($sum);
//
//        echo 'sum of all numbers: '.$sum;
        
        $sum_split =str_split($sum);
//        pr($sum_split);
        
        $total=0;
        for($i=0;$i<strlen($sum);$i++)
        {
            $total = $total + $sum_split[$i];
        }
//        echo 'key: '.$total;
        
        $total_sum_split = str_split($total);
//        pr($total_sum_split);
        
        $get_length = strlen($total);
//        pr($get_length);
        
        if($get_length == 1){
            $grand_total= $total;
        }
        else {
            $grand_total=0;
            for($i=0;$i<$get_length;$i++)
                {
                    $grand_total = $grand_total + $total_sum_split[$i];
                }
        }
//        echo 'grand_total for number: '.$grand_total .'<br>';
        
//  --------------for vowel calculation ------------ 
        $test1=array();
        $sum1=0;
        for($m=0;$m < count($split);$m++) {
        
        $get_vowel_details = $this->name_value->row(array('conditions'=>array('alphabet'=>$split[$m],'vowel'=>1)));
        if($get_vowel_details){
            array_push($test1,$get_vowel_details->number);
            $sum1= $sum1+$test1[$m];
        }else{
            array_push($test1,0);
            $sum1= $sum1+$test1[$m];
        }
        
        }

//        echo 'sum of all vowels: '.$sum1;
        
        $sum_split1 =str_split($sum1);
//        pr($sum_split1);
        $total1=0;
        for($i=0;$i<strlen($sum1);$i++)
        {
            $total1 = $total1 + $sum_split1[$i];
        }
//        echo 'vow: '.$total1;
        
        $total_vow_split = str_split($total1);
//        pr($total_vow_split);
        
        $get_length = strlen($total1);
//        pr($get_length);
        
        if($get_length == 1){
            $grand_total1= $total1;
        }
        else {
            $grand_total1=0;
            for($i=0;$i<$get_length;$i++)
                {
                    $grand_total1 = $grand_total1 + $total_vow_split[$i];
                }
        }
//        echo 'grand_total for vowel: '.$grand_total1;
//--------------------------------
        

//for trait header and trait value
        $t_val =array();
        $get_trait_header = $this->THeader->result(array('conditions'=>array('type'=>$sex)));
        foreach($get_trait_header as $h)
        {
           array_push($t_val,$h->trait_id); 
        }
        $total_t_val = count($t_val);
//        pr($t_val);
//        exit();
        
// ---------------------
        
//for birthday value

$birth_value = array(); 
$get_birth = $this->THeader->result(array('conditions'=>array('type'=>$sex)));

        foreach($get_birth as $b)
        {
            $b_val= $b->birth_value;
            if($b_val==111){
               array_push($birth_value,$grand_total1); 
            }
            elseif($b_val==222){
               array_push($birth_value,$grand_total); 
            }
            else {                
                $get_birthday_value = $this->db->query("SELECT val_$b_val from hr_birthday where val_1='$birthday'")->row(); 
                $var= 'val_'.$b_val;
                array_push($birth_value,$get_birthday_value->$var); 
            }
        }

        $total_birth_value = count($birth_value);
//        pr($birth_value);
//        pr($total_birth_value);
//        exit();
       
// ---------------------
// data insertion in data processing table
        $this->db->truncate('hr_result_process');
        for($i=0;$i<$total_birth_value;$i++){
            $data = array(     
                            'birth_return_value' => $birth_value[$i], 
                            'trait_value' => $t_val[$i]
                           ); 
                       $this->db->insert('hr_result_process', $data);
        }
        
        $get_process_data = $this->Result->result();
        $final_result = $this->Final->result(array('conditions'=>array('user_id'=>$user_id)));
      if(!$final_result){
        foreach($get_process_data as $p)
        {
//            $get_header_id = $this->THeader->row(array('conditions'=>array('trait_id'=>$p->trait_value,'type'=>$sex)));
        
           $get_header_id =  $this->THeader->query("SELECT id,header_value,category FROM hr_trait_header WHERE trait_id=$p->trait_value AND type=$sex")->row() ;
           
           $get_trait_details =  $this->TDetails->query("SELECT return_details FROM hr_trait_details WHERE t_header_id=$get_header_id->id AND birth_return_val=$p->birth_return_value")->row() ;

           if($get_trait_details) {
               $r_value = $get_trait_details->return_details;
           }
           else {
               $r_value = 'Null';
           }           
           
              $data1 = array(     
                            'user_id' => $user_id, 
                            'category' => $get_header_id->category, 
                            'header_value' => $get_header_id->header_value, 
                            'return_details' => $r_value
                           ); 
                    $this->db->insert('hr_final_result', $data1);
           }
                    

//           pr($get_header_id);
//           pr($get_trait_details);
//           exit();
//           pr($get_header_id);
//           pr($p->birth_return_value);
//           pr($get_trait_details);
        }
//           exit();

         $h1 = $this->Final->result(array('conditions'=>array('user_id'=>$user_id,'category'=>1)));        
         $this->params['head1'] = $h1[0]->header_value;
         $this->params['d1'] = $h1[0]->return_details;
         array_shift($h1);
         $this->params['deep1'] =  $h1;
         
         $this->params['head2'] = $h1[1]->header_value;
         $this->params['d2'] = $h1[1]->return_details;
         
         $h2 = $this->Final->result(array('conditions'=>array('user_id'=>$user_id,'category'=>2)));         
         $this->params['head3'] = $h2[0]->header_value;
         $this->params['d3'] = $h2[0]->return_details;
         array_shift($h2);
         $this->params['deep2'] =  $h2;
         
         $h3  = $this->Final->result(array('conditions'=>array('user_id'=>$user_id,'category'=>3)));         
         $this->params['head4'] = $h3[0]->header_value;
         $this->params['d4'] = $h3[0]->return_details;
         array_shift($h3);
         $this->params['deep3'] =  $h3;
         
         $h4 = $this->Final->result(array('conditions'=>array('user_id'=>$user_id,'category'=>4)));         
         $this->params['head5'] = $h4[0]->header_value;
         $this->params['d5'] = $h4[0]->return_details;
         array_shift($h4);
         $this->params['deep4'] = $h4;
         
//        pr($ab->header_value);
//        exit();
        
       
//        ---------------------------------
        
                          
  }
  public function getpdf($userId){
      $this->Template = False;
      $this->layout = False;
      
//      ---pdf generation-----
      $this->load->helper('pdf_helper');
      $grand_result = $this->Final->result(array('conditions'=>array('user_id'=>$userId)));
      $user_info = $this->User->row(array('conditions'=>array('id'=>$userId)));
      $h1 = $this->Final->result(array('conditions'=>array('user_id'=>$userId,'category'=>1)));
      $head1 = $h1[0]->header_value;
      $d1 = $h1[0]->return_details;
      
      $h2= $this->Final->result(array('conditions'=>array('user_id'=>$userId,'category'=>2)));
      $head2 = $h2[0]->header_value;
      $d2 = $h2[0]->return_details;

      $h3 = $this->Final->result(array('conditions'=>array('user_id'=>$userId,'category'=>3)));
      $head3 = $h3[0]->header_value;
      $d3 = $h3[0]->return_details;
      
      $h4 =  $this->Final->result(array('conditions'=>array('user_id'=>$userId,'category'=>4)));
      $head4 = $h4[0]->header_value;
      $d4 = $h4[0]->return_details;

      ob_start();
      echo "<html> 
                <head><title>Your Report</title></head>
                <body>
                    <div style=\"width: 80%;text-align: center;background-color: skyblue;font-size: 20px;\">Input</div>
                    <table style=\"width: 100%;border: 1px solid skyblue;margin-bottom: 10px;\">
                    <tr style=\"width: 100%;border-bottom: 1px solid skyblue;\">
                        <td style=\"width: 30%;border-bottom: 1px solid skyblue;\">First Name</td>
                        <td style=\"width: 70%;border-bottom: 1px solid skyblue;\">".$user_info->fname."</td>
                    </tr>
                    <tr style=\"width: 100%;border-bottom: 1px solid skyblue;\">
                        <td style=\"width: 30%;border-bottom: 1px solid skyblue;\">Male/Female</td>
                        <td style=\"width: 70%;border-bottom: 1px solid skyblue;\">".(($user_info->gender==0)?'Male':'Female')." 
                        </td>
                    </tr>
                    <tr style=\"width: 100%;border-bottom: 1px solid skyblue;\">
                        <td style=\"width: 30%;border-bottom: 1px solid skyblue;\">Birthday(mm/dd/yyyy)</td>
                        <td style=\"width: 70%;border-bottom: 1px solid skyblue;\">".$user_info->birthday."</td>
                    </tr>
                    </table>
                    <div style=\"width: 80%;text-align: center;background-color: skyblue;font-size: 20px;\">Highlights</div>                   
                    <table style=\"width: 100%;border: 1px solid skyblue;margin-bottom: 10px;\">
                       <tr style=\"width: 100%;border-bottom: 1px solid skyblue;\">
                          <td style=\"width: 30%;border-bottom: 1px solid skyblue;\">".$head1."</td>
                          <td style=\"width: 70%;border-bottom: 1px solid skyblue;\">".$d1."</td>
                      </tr>
                       <tr style=\"width: 100%;border-bottom: 1px solid skyblue;\">
                          <td style=\"width: 30%;border-bottom: 1px solid skyblue;\">".$head2."</td>
                          <td style=\"width: 70%;border-bottom: 1px solid skyblue;\">".$d2."</td>
                      </tr>
                       <tr style=\"width: 100%;border-bottom: 1px solid skyblue;\">
                          <td style=\"width: 30%;border-bottom: 1px solid skyblue;\">".$head3."</td>
                          <td style=\"width: 70%;border-bottom: 1px solid skyblue;\">".$d3."</td>
                      </tr>
                       <tr style=\"width: 100%;border-bottom: 1px solid skyblue;\">
                          <td style=\"width: 30%;border-bottom: 1px solid skyblue;\">".$head4."</td>
                          <td style=\"width: 70%;border-bottom: 1px solid skyblue;\">".$d4."</td>
                      </tr>
                      </table>
                      
                    <div style=\"width: 80%;text-align: center;background-color: skyblue;font-size: 20px;\">Going Deeper</div>
                    <table style=\"width: 100%;border: 1px solid skyblue;margin-bottom: 10px;\">";

                    foreach ($grand_result as $r) {
                    echo "<tr style=\"width: 100%;border-bottom: 1px solid skyblue;\">
                                          <td style=\"width: 30%;border-bottom: 1px solid skyblue;\">".$r->header_value."</td>
                                          <td style=\"width: 70%;border-bottom: 1px solid skyblue;\">".$r->return_details."</td>
                                      </tr>";
                    }

                    echo "</table>
                </body>
            </html>";
      
        $content = ob_get_contents();
        ob_end_clean();
        $file_path = str_replace('\\', '/',  realpath(APPPATH .'../')) .'/assets/uploads/report';
        if(!file_exists($file_path)){
            @mkdir($file_path, 0777, true);
        }


        $file_name = 'Report_TechofBliss_'.$userId.'.pdf';
        $full_path = $file_path. '/' .$file_name;
        if(!file_exists($full_path)){

        tcpdf();
        $obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $obj_pdf->SetCreator(PDF_CREATOR);
        $title = "Your Report";
        $obj_pdf->SetTitle($title);
        $obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);
        $obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $obj_pdf->SetDefaultMonospacedFont('helvetica');
        $obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $obj_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $obj_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $obj_pdf->SetFont('helvetica', '', 9);
        $obj_pdf->setFontSubsetting(false);
        $obj_pdf->AddPage();
        $obj_pdf->writeHTML($content, true, false, true, false, '');

        ////$obj_pdf->Output($_SERVER['DOCUMENT_ROOT'].'/'.'Invoice'.$transactions->invoice_no.'.pdf', 'FD');
        //$obj_pdf->Output($_SERVER['DOCUMENT_ROOT'].'/'.'Invoice.pdf', 'FD');
        $obj_pdf->Output($full_path, 'F');
        
       }
      
//      ---end pdf generation-----
      
      if($this->session->userdata('user_id') && $this->session->userdata('user_type')==2){ 
       redirect('home/your_report'.'/'.$this->session->userdata('user_id'));
      }
      else {
          redirect('login');
      }
//       exit();
      
  }
  public function your_report($userId){
        $this->title_for_layout = 'Download';
        $this->params['TNActive'] = 'Success'; //active top menu;
        
        $file_path = str_replace('\\', '/',  realpath(APPPATH .'../')) .'/assets/uploads/report';
        $file_name = 'Report_TechofBliss_'.$userId.'.pdf';
        $full_path = $file_path. '/' .$file_name;
        
        if (!file_exists($full_path)) {
                
            redirect('getReport');
        }
        else {
            
            $this->params['user_id'] = $userId;
        }
  }
  public function download($userId){
        $this->title_for_layout = 'Download';
        $this->params['TNActive'] = 'Success'; //active top menu;
        
        $file_path = str_replace('\\', '/',  realpath(APPPATH .'../')) .'/assets/uploads/report';
        $file_name = 'Report_TechofBliss_'.$userId.'.pdf';
        $full_path = $file_path. '/' .$file_name;
        
        if (file_exists($full_path)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($full_path));
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($full_path));
                readfile($full_path);
//                exit;
            }
  }
    

}
