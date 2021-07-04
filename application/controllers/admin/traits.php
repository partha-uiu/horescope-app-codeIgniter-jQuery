<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Traits   extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->ahruser->Admin('RequireAccess');
        $this->load->model('user_model', 'User');
        $this->load->model('trait_model','Trait');
        $this->load->model('trait_details_model','TraitDetails');
        $this->load->model('trait_header_model', 'THeader');
        $this->load->model('trait_details_model', 'TDetails');
        
    }


    public function traits_add($id = NULL ,$type=NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Add Trait';
        $this->params['index'] = $id;


        if (isset($_POST['data']) || (!empty($id))) {
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' Trait';
            $validationsRoles = array(
                array(
                    'field' => 'gender',
                    'label' => 'Gender',
                    'rules' => 'required'
                )
            );

            if ($validate = $this->Trait->load_input_value()->validate($validationsRoles)) {
                $date = date("Y_m_d_H_i_s");


                if (empty($id)) {
                    $this->Trait->created_date = $date;
                    $this->Trait->modified_date = $date;
                } else {
                    $this->Trait->modified_date = $date;
                }
//                pr($_POST);

//                $header_value=$this->ahrform->get('header_value');
//
//                $trait_id=$this->ahrform->get('trait_id');
//                $type=$this->ahrform->get('gender');
//                $category=$this->ahrform->get('category');
////                $birthday_value=$this->ahrform->get('birth_return_val');
////                $details=$this->ahrform->get('return_details');
//                $this->db->query("insert into hr_trait_header (type,trait_id,header_value,category)  values ($type,$trait_id,'$header_value',$category)");
//                $insert_id = $this->db->insert_id();


//                $this->db->query("insert into hr_trait_details (`t_header_id`, `birth_return_val`, `return_details`)  values ($insert_id,$birthday_value,'$details')");



//                pr($this->Products);
                if ($this->Trait->save(NULL, false)) {

//                pr($_POST);
                    $id = $this->Trait->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.', 'default', array(), 'success');

//                    $this->params['items'] = $this->ProductsCommon->result(array('conditions' => array(), 'order' => array('product_list' => "ASC")));
                    if($type ==0) {
                        redirect(site_url(CPREFIX . '/traits/show_male/'));
                    }
                    else {
                        redirect(site_url(CPREFIX . '/traits/show_female/'));
                      }
                    
                } else {
                    $this->ahrsession->set_flash(' Information could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($this->Trait->row($id));
            }
            
        }

    }


    public function add_trait_details($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Add Trait Details';
        $this->params['index'] = $id;

        if (isset($_POST['data']) || (!empty($id))) {
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' Trait Details';
            $validationsRoles = array(
                array(
                    'field' => 'return_details',
                    'label' => 'Trait Details',
                    'rules' => 'required'
                )
            );

            if ($validate = $this->TraitDetails->load_input_value()->validate($validationsRoles)) {
                $date = date("Y_m_d_H_i_s");


                if (empty($id)) {
                    $this->TraitDetails->created_date = $date;
                    $this->TraitDetails->modified_date = $date;
                } else {
                    $this->TraitDetails->modified_date = $date;
                }
//                pr($_POST);

//                $header_value=$this->ahrform->get('header_value');
//
//                $trait_id=$this->ahrform->get('trait_id');
//                $type=$this->ahrform->get('gender');
//                $category=$this->ahrform->get('category');
////                $birthday_value=$this->ahrform->get('birth_return_val');
////                $details=$this->ahrform->get('return_details');
//                $this->db->query("insert into hr_trait_header (type,trait_id,header_value,category)  values ($type,$trait_id,'$header_value',$category)");
//                $insert_id = $this->db->insert_id();


//                $this->db->query("insert into hr_trait_details (`t_header_id`, `birth_return_val`, `return_details`)  values ($insert_id,$birthday_value,'$details')");


                if ($this->TraitDetails->save(NULL, false)) {

//                pr($_POST);
                    $id = $this->TraitDetails->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.', 'default', array(), 'success');

                    redirect(site_url(CPREFIX . '/traits/add_trait_details/'));
                    
                } else {
                    $this->ahrsession->set_flash(' Information could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($this->Trait->row($id));
            }

        }
        $this->params['male'] = $this->Trait->get_list(array('conditions' => array('type'=>'0'), 'order' => array('header_value' => "ASC"), 'fields' => array('id', 'header_value')));
        $this->params['female'] = $this->Trait->get_list(array('conditions' => array('type'=>'1'), 'order' => array('header_value' => "ASC"), 'fields' => array('id', 'header_value')));

    }


public function trait_edit($id=NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Update Trait';
        $trait_details =$this->params['return_details']= $this->TDetails->result(array('conditions'=>array('t_header_id'=>$id)));
        $trait_header =$this->params['header']= $this->THeader->row(array('conditions'=>array('id'=>$id)));

    }
    public function trait_update($id,$type) {
        $this->layout = False;
        $this->Template = False;
        $total = count($_POST);

        
        if($total==12) {
            for($i=0;$i<=11;$i++){
                $details = $this->ahrform->get('birth_'.$i);
                $data = array(
                    'return_details' => $details
                );
                $this->db->where('t_header_id', $id);
                $this->db->where('birth_return_val', $i);
                $this->db->update('hr_trait_details', $data);
            }
            if($type ==0) {
                redirect('admin/traits/trait_details_male');
            }
            else {
                redirect('admin/traits/trait_details_female');
            }
        }
        else {
            for($i=1;$i<=11;$i++){
               
               $details = $this->ahrform->get('birth_'.$i);
                $data = array(
                    'return_details' => $details
                );
                $this->db->where('t_header_id', $id);
                $this->db->where('birth_return_val', $i);
                $this->db->update('hr_trait_details', $data);
            }
            

        }
            if($type ==0) {
                redirect('admin/traits/trait_details_male');
            }
            else {
                redirect('admin/traits/trait_details_female');
            }

    }


    public function trait_details_male() {
    
    $this->title_for_layout = $this->params['hTitle'] = 'Male Trait Details';
//        $this->params['Products'] = $this->Products->result(array('order' => array('p_title' => 'ASC')));
//        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>0), 'order' => array('trait_id'=>"ASC")));
//        $this->params['items']=$this->db->query("select id,header_value from hr_trait_header where type=0")->result();
$query1 = $this->db
        ->select('id,header_value,trait_id')
        ->from('hr_trait_header')
        ->where('type',0)
        ->order_by('trait_id','ASC')
        ->get()
        ->result_array();
$this->params['items']=$query1;
//foreach( $query1 as $q ){
// 
//   pr($q['id']);
//}
////pr($query1);
//exit();
$id_array = array();
$array_1 = array();
$array_2 = array();
$array_3 = array();
$array_4 = array();
$array_5 = array();
$array_6 = array();
$array_7 = array();
$array_8 = array();
$array_9 = array();
$array_10 = array();
$array_11 = array();
$array_12 = array();

$array_result = array();

foreach( $query1 as $q ){
 
   array_push($id_array,$q['id']);
}

//for($i=1; $i<12; $i++){

   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',1)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_1,$query2);

   }

   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',2)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_2,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',3)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_3,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',4)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_4,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',5)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_5,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',6)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_6,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',7)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_7,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',8)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_8,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',9)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_9,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',10)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_10,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',11)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_11,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',0)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       if($query2){
        array_push($array_12,$query2);
       }
       else{
        array_push($array_12,0);
       }

   }
//}

//pr(array_chunk($array_result, 25));

   $this->params['val1'] = $array_1;
   $this->params['val2'] = $array_2;
   $this->params['val3'] = $array_3;
   $this->params['val4'] = $array_4;
   $this->params['val5'] = $array_5;
   $this->params['val6'] = $array_6;
   $this->params['val7'] = $array_7;
   $this->params['val8'] = $array_8;
   $this->params['val9'] = $array_9;
   $this->params['val10'] = $array_10;
   $this->params['val11'] = $array_11;
   $this->params['val12'] = $array_12;
    
    }


    public function trait_details_female() {
    
    		$this->title_for_layout = $this->params['hTitle'] = 'Female Trait Details';
//        $this->params['Products'] = $this->Products->result(array('order' => array('p_title' => 'ASC')));
//        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>0), 'order' => array('trait_id'=>"ASC")));
//        $this->params['items']=$this->db->query("select id,header_value from hr_trait_header where type=0")->result();
$query1 = $this->db
        ->select('id,header_value,trait_id')
        ->from('hr_trait_header')
        ->where('type',1)
        ->order_by('trait_id','ASC')
        ->get()
        ->result_array();
$this->params['items']=$query1;
//foreach( $query1 as $q ){
// 
//   pr($q['id']);
//}
////pr($query1);
//exit();
$id_array = array();
$array_1 = array();
$array_2 = array();
$array_3 = array();
$array_4 = array();
$array_5 = array();
$array_6 = array();
$array_7 = array();
$array_8 = array();
$array_9 = array();
$array_10 = array();
$array_11 = array();
$array_12 = array();

$array_result = array();

foreach( $query1 as $q ){
 
   array_push($id_array,$q['id']);
}

//for($i=1; $i<12; $i++){

   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',1)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_1,$query2);

   }

   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',2)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_2,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',3)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_3,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',4)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_4,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',5)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_5,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',6)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_6,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {


       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',7)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_7,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',8)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_8,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',9)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_9,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',10)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_10,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',11)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       array_push($array_11,$query2);

   }
   for($j=0; $j<count($id_array); $j++) {

       $query2 = $this->db
        ->select('return_details')
        ->from('hr_trait_details')
        ->where('birth_return_val',0)
        ->where('t_header_id',$id_array[$j])
        ->get()
        ->result_array();
       //$var = $array.$i;
       if($query2){
        array_push($array_12,$query2);
       }
       else{
        array_push($array_12,0);
       }

   }
//}

//pr(array_chunk($array_result, 25));

   $this->params['val1'] = $array_1;
   $this->params['val2'] = $array_2;
   $this->params['val3'] = $array_3;
   $this->params['val4'] = $array_4;
   $this->params['val5'] = $array_5;
   $this->params['val6'] = $array_6;
   $this->params['val7'] = $array_7;
   $this->params['val8'] = $array_8;
   $this->params['val9'] = $array_9;
   $this->params['val10'] = $array_10;
   $this->params['val11'] = $array_11;
   $this->params['val12'] = $array_12;
    
    }


    public function traits_details_add($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Add Trait Details';
        $this->params['index'] = $id;

        // pr($cate);
        // exit();
        if (isset($_POST['data']) || (!empty($id))) {
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' Trait Details';
            $validationsRoles = array(
                array(
                    'field' => 't_header_id',
                    'label' => 'Header value',
                    'rules' => 'required'
                )
            );

            if ($validate = $this->TraitDetails->load_input_value()->validate($validationsRoles)) {
                $date = date("Y_m_d_H_i_s");


                if (empty($id)) {
                    $this->TraitDetails->created_date = $date;
                    $this->TraitDetails->modified_date = $date;
                } else {
                    $this->TraitDetails->modified_date = $date;
                }
//                pr($_POST);

                if ($this->TraitDetails->save(NULL, false)) {

//                pr($_POST);
                    $id = $this->TraitDetails->id;

                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.', 'default', array(), 'success');

                    redirect(site_url(CPREFIX . '/traits/traits_add/'));
                    
                } else {
                    $this->ahrsession->set_flash(' Information could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($this->TraitDetails->row($id));
            }
            //$this->params['Prents'] = $this->ProductsCommon->get_list(array('conditions' => array('parent_id' => 0), 'fields' => array('id')));
        }

        $this->params['male'] = $this->Trait->get_list(array('conditions' => array('type'=>'0'), 'order' => array('header_value' => "ASC"), 'fields' => array('id', 'header_value')));
        $this->params['female'] = $this->Trait->get_list(array('conditions' => array('type'=>'1'), 'order' => array('header_value' => "ASC"), 'fields' => array('id', 'header_value')));

//        $this->params['male'] = $this->db->query("select id,header_value from hr_trait_header where type=0")->result();
//        $this->params['female'] = $this->db->query("select id,header_value from hr_trait_header where type=1")->result();

    }


    public function show_male() {
        $this->title_for_layout = $this->params['hTitle'] = 'Male Trait List';
//        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>0), 'order' => array('trait_id'=>"ASC")));
        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>0)));

    }

    public function show_female() {
        $this->title_for_layout = $this->params['hTitle'] = 'Female Trait List';
//        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>1), 'order' => array('trait_id'=>"ASC")));
        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>1)));
    }

    public function trait_add_male($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Trait Update';
        $this->params['index'] = $id;

        // pr($cate);
        // exit();
        if (isset($_POST['data']) || (!empty($id))) {
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' Products';
            $validationsRoles = array(
                array(
                    'field' => 'trait_id',
                    'label' => 'Trait id',
                    'rules' => 'required',

                )
            );

            if (($validate = $this->Trait->load_input_value()->validate($validationsRoles))) {
                $date = date("Y_m_d_H_i_s");
                /* ------------------Start Image Upload App Icon---------------- */
//                $config['upload_path'] = 'assets/uploads/product_image/';
//                //print_r($config);
//                $config['allowed_types'] = 'gif|jpg|png|ogg|ico';
//                $config['file_name'] = $this->ahrform->get('image') . '_' . $date;
//                $error = '';
//                $udata = '';
//                $this->load->library('upload');
//                $this->upload->initialize($config);
//
//                if (!$this->upload->do_upload('image')) {
//                    $error = array('error' => $this->upload->display_errors());
//                } else {
//                    $udata = array('upload_data' => $this->upload->data());
//                    $app_icon = $config['upload_path'] . $udata['upload_data']['file_name'];
//                    $this->CommonSettings->set('image', $app_icon);
//                }

                if (empty($id)) {
                    $this->Trait->created_date = $date;
                    $this->Trait->modified_date = $date;
                } else {
                    $this->Trait->modified_date = $date;
                }
                if ($this->Trait->save(NULL, false)) {

//                pr($_POST);
                    $id = $this->Trait->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.', 'default', array(), 'success');

//                    $this->params['items'] = $this->ProductsCommon->result(array('conditions' => array(), 'order' => array('product_list' => "ASC")));

                    redirect(site_url(CPREFIX . '/traits/show_male/' ));
                } else {
                    $this->ahrsession->set_flash('Informations could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($this->Trait->row($id));
            }
            
        }

    }



    public function trait_add_female($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Trait Update';
        $this->params['index'] = $id;

        // pr($cate);
        // exit();
        if (isset($_POST['data']) || (!empty($id))) {
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' Trait';
            $validationsRoles = array(
                array(
                    'field' => 'trait_id',
                    'label' => 'Trait id',
                    'rules' => 'required'
                )
            );

            if ($validate = $this->Trait->load_input_value()->validate($validationsRoles)) {
                $date = date("Y_m_d_H_i_s");


                if (empty($id)) {
                    $this->Trait->created_date = $date;
                    $this->Trait->modified_date = $date;
                } else {
                    $this->Trait->modified_date = $date;
                }
                if ($this->Trait->save(NULL, false)) {

//                pr($_POST);
                    $id = $this->Trait->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.', 'default', array(), 'success');

//                    $this->params['items'] = $this->ProductsCommon->result(array('conditions' => array(), 'order' => array('product_list' => "ASC")));

                    redirect(site_url(CPREFIX . '/traits/show_female/' ));
                } else {
                    $this->ahrsession->set_flash('Informations could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($this->Trait->row($id));
            }
  
        }

    }


    public function delete_male($id = null) {
        $this->layout = FALSE;
        $this->template = FALSE;

        $redirect = site_url('/' . CPREFIX . '/traits/show_male');
        //get id/IDs from ajax request
        $id = $this->ahrform->get('id') ? $this->ahrform->get('id') : $id;

        if (empty($id) && !$this->input->is_ajax_request()) {
            $this->ahrsession->set_flash('Invalid id. Information could not delete. Please try again', 'default', array(), 'warning');
            redirect($redirect);
        }
        $this->Trait->deleteAll(array('conditions' => array('id' => $id)));

        if ($this->input->is_ajax_request()) {
            exit(json_encode(array('status' => true, 'msg' => "Information has been deleted")));
        }
        redirect($redirect);
    }


    public function delete_female($id = null) {
        $this->layout = FALSE;
        $this->template = FALSE;

        $redirect = site_url('/' . CPREFIX . '/traits/show_female');
        //get id/IDs from ajax request
        $id = $this->ahrform->get('id') ? $this->ahrform->get('id') : $id;

        if (empty($id) && !$this->input->is_ajax_request()) {
            $this->ahrsession->set_flash('Invalid id. Information could not delete. Please try again', 'default', array(), 'warning');
            redirect($redirect);
        }
        $this->Trait->deleteAll(array('conditions' => array('id' => $id)));

        if ($this->input->is_ajax_request()) {
            exit(json_encode(array('status' => true, 'msg' => "Information has been deleted")));
        }
        redirect($redirect);
    }


    public function search_birthday($id = NULL) {
        $this->title_for_layout = $this->params['hTitle'] = 'Product Settings';
        $this->params['index'] = $id;

        if (isset($_POST['data']) || (!empty($id))) {
            $this->title_for_layout = $this->params['hTitle'] = (empty($id) ? 'Add' : 'Edit') . ' Products';
            $validationsRoles = array(
                array(
                    'field' => 'title',
                    'label' => 'Product title',
                    'rules' => 'required'
                )
            );

            if ($validate = $this->CommonSettings->load_input_value()->validate($validationsRoles)) {
                $date = date("Y_m_d_H_i_s");
                /* ------------------Start Image Upload App Icon---------------- */
//                $config['upload_path'] = 'assets/uploads/product_image/';
//                //print_r($config);
//                $config['allowed_types'] = 'gif|jpg|png|ogg|ico';
//                $config['file_name'] = $this->ahrform->get('image') . '_' . $date;
//                $error = '';
//                $udata = '';
//                $this->load->library('upload');
//                $this->upload->initialize($config);
//
//                if (!$this->upload->do_upload('image')) {
//                    $error = array('error' => $this->upload->display_errors());
//                } else {
//                    $udata = array('upload_data' => $this->upload->data());
//                    $app_icon = $config['upload_path'] . $udata['upload_data']['file_name'];
//                    $this->CommonSettings->set('image', $app_icon);
//                }

                if (empty($id)) {
                    $this->CommonSettings->created_date = $date;
                    $this->CommonSettings->modified_date = $date;
                } else {
                    $this->CommonSettings->modified_date = $date;
                }
                if ($this->CommonSettings->save(NULL, false)) {

//                pr($_POST);
                    $id = $this->CommonSettings->id;
                    $this->ahrsession->set_flash('Informations has been ' . (empty($_POST['id']) ? 'created' : 'updated') . ' successfully.', 'default', array(), 'success');

//                    $this->params['items'] = $this->ProductsCommon->result(array('conditions' => array(), 'order' => array('product_list' => "ASC")));

                    redirect(site_url(CPREFIX . '/products/product_settings/' ));
                } else {
                    $this->ahrsession->set_flash('Informations could not saved. Please try again.', 'default', array(), 'warning');
                }
            }

            if (!empty($id)) {
                $this->ahrform->set($this->CommonSettings->row($id));
            }
        }

    }


}