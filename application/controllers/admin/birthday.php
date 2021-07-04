<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Birthday   extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->ahruser->Admin('RequireAccess');
        $this->load->model('user_model', 'User');
        $this->load->model('trait_model','Trait');
        $this->load->model('test_model','Test');
    }




    public function search_birthday() {
        $this->title_for_layout = $this->params['hTitle'] = 'Search Birthday';
        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>0), 'order' => array('trait_id'=>"ASC")));
    }
    
    public function upload_birthday() {
        $this->title_for_layout = $this->params['hTitle'] = 'Upload Birthday';
        $this->params['items'] = $this->Trait->result(array('conditions' => array('type'=>0), 'order' => array('trait_id'=>"ASC")));
    }
    public function upload_birthday_csv() {
        $this->layout = False;
        $this->Template = False;
        $this->title_for_layout = $this->params['hTitle'] = 'Upload Birthday';
        $default = ini_get('max_execution_time');
          set_time_limit(10000000000);

//	if(isset($_POST["submit"]))
//	{
		$file = $_FILES['file']['tmp_name'];
		
		$handle = fopen($file, "r");
		$c = 0;
                $test= fgetcsv($handle);
//                print_r ($_FILES);
//                print_r ($file_name);
//                $type= substr($file_name, -3);
                $file_name = $_FILES['file']['name'];
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                
              //  print_r ($type);
//                print_r ($file_name);
//                print_r ($ext);

              if($ext=='CSV' || $ext=='csv'){
                
                //exit();
		while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
		{
			$total_val = count($filesop);
			$birth_year = $filesop[0];
                       
			$birth_value = $this->Test->row(array('conditions'=>array('val_1'=>$birth_year)));
                        if($birth_value) {
                            $get_id = $birth_value->id;
                                          $data = array(
                                               'val_1' => $filesop[0],
                                                'val_2' => $filesop[1],
                                                'val_3' => $filesop[2],
                                                'val_4' => $filesop[3],
                                                'val_5' => $filesop[4],
                                                'val_6' => $filesop[5],
                                                'val_7' => $filesop[6],
                                                'val_8' => $filesop[7],
                                                'val_9' => $filesop[8],
                                                'val_10' => $filesop[9],
                                                'val_11' => $filesop[10],
                                                'val_12' => $filesop[11],
                                                'val_13' => $filesop[12],
                                                'val_14' => $filesop[13],
                                                'val_15' => $filesop[14],
                                                'val_16' => $filesop[15],
                                                'val_17' => $filesop[16],
                                                'val_18' => $filesop[17],
                                                'val_19' => $filesop[18],
                                                'val_20' => $filesop[19],
                                                'val_21' => $filesop[20],
                                                'val_22' => $filesop[21],
                                                'val_23' => $filesop[22],
                                                'val_24' => $filesop[23],
                                                'val_25' => $filesop[24],
                                                'val_26' => $filesop[25],
                                                'val_27' => $filesop[26],
                                                'val_28' => $filesop[27],
                                                'val_29' => $filesop[28],
                                                'val_30' => $filesop[29],
                                                'val_31' => $filesop[30],
                                                'val_32' => $filesop[31],
                                                'val_33' => $filesop[32]
                                            );

                                          $this->db->where('id', $get_id);
                                          $this->db->update('hr_test_upload', $data);
                            
                        }
                        else {
                            
                                $data = array(
                                    'val_1' => $filesop[0],
                                    'val_2' => $filesop[1],
                                    'val_3' => $filesop[2],
                                    'val_4' => $filesop[3],
                                    'val_5' => $filesop[4],
                                    'val_6' => $filesop[5],
                                    'val_7' => $filesop[6],
                                    'val_8' => $filesop[7],
                                    'val_9' => $filesop[8],
                                    'val_10' => $filesop[9],
                                    'val_11' => $filesop[10],
                                    'val_12' => $filesop[11],
                                    'val_13' => $filesop[12],
                                    'val_14' => $filesop[13],
                                    'val_15' => $filesop[14],
                                    'val_16' => $filesop[15],
                                    'val_17' => $filesop[16],
                                    'val_18' => $filesop[17],
                                    'val_19' => $filesop[18],
                                    'val_20' => $filesop[19],
                                    'val_21' => $filesop[20],
                                    'val_22' => $filesop[21],
                                    'val_23' => $filesop[22],
                                    'val_24' => $filesop[23],
                                    'val_25' => $filesop[24],
                                    'val_26' => $filesop[25],
                                    'val_27' => $filesop[26],
                                    'val_28' => $filesop[27],
                                    'val_29' => $filesop[28],
                                    'val_30' => $filesop[29],
                                    'val_31' => $filesop[30],
                                    'val_32' => $filesop[31],
                                    'val_33' => $filesop[32]
                                );
                             
                                
                                $this->db->insert('hr_test_upload', $data);
                        }
//			$sql = mysql_query("INSERT INTO chemical_substance (chem_sub_bnf, chemical_name) VALUES ('$chem_sub_bnf','$chemical_name')");
			$c = $c + 1;
		}
                redirect(site_url(CPREFIX . '/birthday/search_birthday/'));
		
//			if($sql){
//				echo "You database has imported successfully. You have inserted ". $c ." recoreds";
//			}else{
//				echo "Sorry! There is some problem.";
//			}
              } else {
                  echo "Sorry! Please submit only .csv file.";
              }
//	}
        set_time_limit($default);
//        echo "Hello Shakil";
//        exit();
    }



}
