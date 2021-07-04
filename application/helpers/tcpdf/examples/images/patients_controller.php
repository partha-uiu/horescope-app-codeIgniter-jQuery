<?php

class PatientsController extends AppController {

    public $name = 'Patients';
    public $helpers = array('Html', 'Form', 'Javascript', 'Ajax', 'QuickAcl');
    public $components = array('Image');
    public $uses = null;

    public function webcam() {
        $this->layout = 'iframe';
    }

    public function patient_preferences() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        $this->loadModel("UserAccount");
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);

        switch ($task) {
            case 'autocomplete': {
                    $this->loadModel("UserRole");


                    $term = isset($this->data['autocomplete']['keyword']) ? trim($this->data['autocomplete']['keyword']) : '';
                    $limit = isset($this->data['autocomplete']['limit']) ? intval($this->data['autocomplete']['limit']) : 20;
                    $limit = ($limit) ? $limit : 20;

                    $conditions = array(
                        'UserAccount.role_id' => EMR_Roles::PHYSICIAN_ROLE_ID,
                    );

                    if ($term) {
                        $conditions['AND'] = array(
                            'OR' => array(
                                'UserAccount.firstname LIKE ' => $term . '%',
                                'UserAccount.lastname LIKE ' => $term . '%'
                            )
                        );
                    }

                    $this->UserAccount->unbindModelAll();

                    $providers = $this->UserAccount->find('all', array(
                        'conditions' => $conditions,
                        'limit' => $limit,
                        'fields' => array(
                            'UserAccount.user_id',
                            'UserAccount.firstname',
                            'UserAccount.lastname',
                        ),
                            ));


                    $data = array();
                    foreach ($providers as $p) {
                        $data[] = $p['UserAccount']['firstname'] . ' ' . $p['UserAccount']['lastname'] . '|' . $p['UserAccount']['user_id'];
                    }

                    echo implode("\n", $data);
                    exit;
                } break;
            case 'edit': {
                    $this->loadModel("ImmtrackCountry");
                    $this->loadModel("StateCode");
                    $this->loadModel("PatientPreference");
                    $this->loadModel("SmsCarrier");

                    if (!empty($this->data)) {
                        $this->PatientPreference->saveAudit('Update');
                        $this->PatientPreference->save($this->data);
                        $ret = array();
                        echo json_encode($ret);


                        exit;
                    } else {

                        $ifsetPCP = $this->PatientPreference->getPrimaryCarePhysician($patient_id);
                        if (empty($ifsetPCP)) {  // if no PCP is already set
                            // find number of doctors. if only 1, 'Primary Care Physician' box should be pre-filled.
                            $this->loadModel("UserRole");
                            $names = $this->UserRole->getRoleNames(EMR_Roles::PHYSICIAN_ROLE_ID);
                            $data = array();
                            foreach ($names as $value) {
                                $data[] = $value;
                            }

                            if (sizeof($data) == 1) {
                                $this->set("singleProvider", $data[0]);
                            }
                        }
                        $preferences = $this->PatientPreference->getPreferences($patient_id);
                        $this->set("patient_preferences", $this->sanitizeHTML($preferences));

                        /* $referred_by_name = $this->PatientPreference->getPreferences($patient_id);
                          $referred_by = $referred_by_name['referred_by'];
                          $value = $this->UserAccount->getCurrentUser($referred_by);
                          $referred_by_val = $value['firstname'].' '.$value['lastname'];
                          $this->set('referred_by_val', $referred_by_val);

                          $recommended_by_name = $this->PatientPreference->getPreferences($patient_id);
                          $recommended_by = $recommended_by_name['recommended_by'];
                          $value = $this->UserAccount->getCurrentUser($recommended_by);
                          $recommended_by_val = $value['firstname'].' '.$value['lastname'];
                          $this->set('recommended_by_val', $recommended_by_val); */

                        $countries = $this->ImmtrackCountry->getList();
                        $states = $this->StateCode->getList();
                        $smscarrier = $this->SmsCarrier->getList();
                        $this->set("states", $states);
                        $this->set("SmsCarrier", $smscarrier);
                        $this->set("ImmtrackCountries", $countries);

                        $this->PatientPreference->id = $preferences['preference_id'];
                        $this->PatientPreference->saveAudit('View');
                    }
                }
        }
    }

    public function webcam_save() {
        $save_image_path = $this->paths['temp'];

        if (isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
            $snaptime = md5(mktime());
            $jpg = $GLOBALS["HTTP_RAW_POST_DATA"];
            $file_real_name = $snaptime . "_webcam.jpg";
            $filename = $save_image_path . $file_real_name;
            file_put_contents($filename, $jpg);

            $this->Image->resize($filename, $filename, 640, 480, 90);

            $converted_file_real_name = FileHash::getHash($filename) . "_webcam.jpg";
            $converted_filename = $save_image_path . $converted_file_real_name;

            rename($filename, $converted_filename);

            echo $this->url_abs_paths['temp'] . $converted_file_real_name;
        } else {
            echo "Encoded JPEG information not received.";
        }

        exit;
    }

    public function index() {
        $from_encounter = (isset($this->params['named']['from_encounter'])) ? $this->params['named']['from_encounter'] : "";
        $search_data = (isset($this->params['named']['dat'])) ? $this->params['named']['dat'] : "";
        //exit;
        if ($from_encounter == 'yes') {
            $this->layout = "encounter_view";
        }

        $this->loadModel("ScheduleCalendar");
        $this->loadModel("PracticeLocation");
        $this->loadModel("EncounterMaster");
        $this->loadModel("PatientDemographic");

        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $calendar_id = (isset($this->params['named']['calendar_id'])) ? $this->params['named']['calendar_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";

        // make sure patient is found before proceeding further into this function 
        $item = array();
        if ($patient_id) {
            $item = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
            if (!$item) {
                $this->redirect(array('controller' => 'dashboard', 'action' => 'index'));
            }
        }

        $this->loadModel("PatientNote");
        $this->set('patient_notes', $this->sanitizeHTML($this->paginate('PatientNote', array('PatientNote.patient_id' => $patient_id, 'PatientNote.alert_chart' => 'Yes', 'PatientNote.status' => 'New'))));

        if (strlen($calendar_id) > 0) {
            $user = $this->Session->read('UserAccount');
            $user_id = $user['user_id'];

            $patient_id = $this->ScheduleCalendar->getPatientID($calendar_id);
            $reason_for_visit = $this->ScheduleCalendar->getReason($calendar_id);

            $encounter_id = $this->EncounterMaster->getEncounter($calendar_id, $patient_id, $user_id);

            $this->loadModel("EncounterChiefComplaint");
            $this->EncounterChiefComplaint->addItem($reason_for_visit, $encounter_id, $this->user_id);

            //$this->redirect(array('action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id));
        }


        switch ($task) {
            case "save_search_data": {

                    if (!empty($this->data['first_name'])) {
                        $this->Session->write('search_firstname', $this->data['first_name']);
                    }

                    if (!empty($this->data['last_name'])) {
                        $this->Session->write('search_last_name', $this->data['last_name']);
                    }

                    if (!empty($this->data['ssn'])) {
                        $this->Session->write('search_ssn', $this->data['ssn']);
                    }

                    if (!empty($this->data['dob'])) {
                        $this->Session->write('search_dob', $this->data['dob']);
                    }
                    exit;
                }
            case "import_patient": {
                    $ret = array();
                    $ret['success'] = true;
                    $ret['error_field'] = $this->data['error_field'];

                    $this->paths['patient_documents'] =
                            $this->paths['patients'] . intval($this->data['patient_id']) . DS . 'documents' . DS;

                    $ccr_reader = new CCR_Reader($this->paths[$this->data['folder']] . $this->data['filename']);


                    $opts = array(
                        'create_document' => true,
                    );

                    if ($this->data['folder'] == 'patient_documents') {
                        $opts['create_document'] = false;
                    }

                    if ($ccr_reader->isValidDocument()) {
                        $import_result = $ccr_reader->importPatient($this->data['validate_mode'], $this->data['patient_id'], $opts);

                        if ($import_result['success']) {
                            $ret['patient_id'] = $import_result['patient_id'];
                            $ret['document_id'] = isset($import_result['document_id']) ? $import_result['document_id'] : '';
                        } else {
                            $ret['success'] = false;
                            $ret['reason'] = $import_result['reason'];
                        }
                    } else {
                        $ccda_reader = new CCDA_Reader($this->paths[$this->data['folder']] . $this->data['filename']);

                        if ($ccda_reader->isValidDocument()) {
                            $import_result = $ccda_reader->importPatient($this->data['validate_mode'], $this->data['patient_id'], $opts);

                            if ($import_result['success']) {
                                $ret['patient_id'] = $import_result['patient_id'];
                                $ret['document_id'] = isset($import_result['document_id']) ? $import_result['document_id'] : '';
                            } else {
                                $ret['success'] = false;
                                $ret['reason'] = $import_result['reason'];
                            }
                        } else {
                            $ccd_reader = new CCD_Reader($this->paths[$this->data['folder']] . $this->data['filename']);

                            if ($ccd_reader->isValidDocument()) {
                                $import_result = $ccd_reader->importPatient($this->data['validate_mode'], $this->data['patient_id'], $opts);

                                if ($import_result['success']) {
                                    $ret['patient_id'] = $import_result['patient_id'];
                                    $ret['document_id'] = isset($import_result['document_id']) ? $import_result['document_id'] : '';
                                } else {
                                    $ret['success'] = false;
                                    $ret['reason'] = $import_result['reason'];
                                }
                            } else {
                                $ret['success'] = false;
                                $ret['reason'] = "Invalid Document";
                            }
                        }
                    }

                    echo json_encode($ret);
                    exit;
                } break;
            case "import_ccr_ccd": {
                    $this->layout = "empty";


                    $xmlFile = $this->paths[$this->data['folder']] . $this->data['filename'];

                    if (!file_exists($xmlFile)) {
                        $this->paths['patient_documents'] = $this->paths['patients'] . $this->data['patient_id'] . DS . 'documents' . DS;
                        $xmlFile = $this->paths['patient_documents'] . $this->data['filename'];
                    }
                    $ccr_reader = new CCR_Reader($xmlFile);

                    if ($ccr_reader->isValidDocument()) {
                        $file_data = $ccr_reader->ccr_file_contents;

                        header("Content-Type:text/xml");
                        echo '<?xml-stylesheet type="text/xsl" href="' . Router::url(array('task' => 'get_ccr_xsl', 'enable_import' => $this->data['enable_import'], 'folder' => $this->data['folder'], 'validate_mode' => $this->data['validate_mode'], 'patient_id' => $this->data['patient_id'])) . '"?>';
                        echo $file_data;
                    } else {
                        $ccda_reader = new CCDA_Reader($xmlFile);

                        if ($ccda_reader->isValidDocument()) {
                            $file_data = $ccda_reader->ccd_file_contents;

                            header("Content-Type:text/xml");
                            echo '<?xml-stylesheet type="text/xsl" href="' . Router::url(array('task' => 'get_ccd_xsl', 'enable_import' => $this->data['enable_import'], 'folder' => $this->data['folder'], 'validate_mode' => $this->data['validate_mode'], 'patient_id' => $this->data['patient_id'])) . '"?>';
                            echo $file_data;
                        } else {
                            $ccd_reader = new CCD_Reader($xmlFile);

                            if ($ccd_reader->isValidDocument()) {
                                $file_data = $ccd_reader->ccd_file_contents;

                                header("Content-Type:text/xml");
                                echo '<?xml-stylesheet type="text/xsl" href="' . Router::url(array('task' => 'get_ccd_xsl', 'enable_import' => $this->data['enable_import'], 'folder' => $this->data['folder'], 'validate_mode' => $this->data['validate_mode'], 'patient_id' => $this->data['patient_id'])) . '"?>';
                                echo $file_data;
                            } else {
                                echo "Invalid Document";
                            }
                        }
                    }

                    exit;
                } break;
            case "get_ccr_xsl": {
                    $enable_import = (isset($this->params['named']['enable_import'])) ? $this->params['named']['enable_import'] : "";
                    $folder = (isset($this->params['named']['folder'])) ? $this->params['named']['folder'] : "";
                    $validate_mode = (isset($this->params['named']['validate_mode'])) ? $this->params['named']['validate_mode'] : "";
                    $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                    $this->set(compact("enable_import", "folder", "validate_mode", "patient_id"));

                    $this->layout = "empty";
                    header("Content-Type:text/xml");
                    $this->render("ccr_xsl");
                } break;
            case "get_ccd_xsl": {
                    $enable_import = (isset($this->params['named']['enable_import'])) ? $this->params['named']['enable_import'] : "";
                    $folder = (isset($this->params['named']['folder'])) ? $this->params['named']['folder'] : "";
                    $validate_mode = (isset($this->params['named']['validate_mode'])) ? $this->params['named']['validate_mode'] : "";
                    $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                    $this->set(compact("enable_import", "folder", "validate_mode", "patient_id"));

                    $this->set("enable_import", $enable_import);
                    $this->set("folder", $folder);

                    $this->layout = "empty";
                    header("Content-Type:text/xml");
                    $this->render("ccd_xsl");
                } break;
            case "addnew": {
                    $this->loadModel("FavoriteMacros");
                    $favs = $this->FavoriteMacros->find('all', array('conditions' => array('FavoriteMacros.user_id' => $this->user_id)));
                    $this->set('FavoriteMacros', $favs);
                } break;
            case "edit": {

                    //its possible $item query was already completed above, so use it if present			
                    if (!$item) {
                        $item = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
                    }
                    $this->set("demographic_info", $item['PatientDemographic']);

                    $this->loadModel("FavoriteMacros");
                    $favs = $this->FavoriteMacros->find('all', array('conditions' => array('FavoriteMacros.user_id' => $this->user_id)));
                    $this->set('FavoriteMacros', $favs);

                    $PracticeSetting = $this->Session->read("PracticeSetting");
                    if ($view == 'medical_information' && $PracticeSetting['PracticeSetting']['rx_setup'] == 'Electronic_Dosespot') {
                        //If the patient not exists in Dosespot, add the patient to Dosespot
                        if (empty($item['PatientDemographic']['dosespot_patient_id'])) {
                            $this->PatientDemographic->updateDosespotPatient($patient_id);
                        }
                    }

                    if ($view == 'medical_information') {
                        $this->loadModel('PracticePlanSection');
                        $hasCustomPlans = count($this->PracticePlanSection->getCustomSections());
                        $hasTreatmentPlans = $this->PracticePlanSection->hasTreatmentPlans();
                        $this->set(compact('hasCustomPlans', 'hasTreatmentPlans'));
                    }
                } break;
            case "checkPatient": {
                    $this->loadModel("PatientDemographic");
                    $patients = $this->PatientDemographic->find(
                            'all', array(
                        'fields' => array('patient_id', 'first_name', 'last_name'),
                        'conditions' => array('AND' => array('PatientDemographic.first_name' => $this->data['check']['first_name'], 'PatientDemographic.last_name' => $this->data['check']['last_name'], 'PatientDemographic.dob' => __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['check']['dob']))))),
                        'recursive' => -1
                            )
                    );
                    if (count($patients)) {
                        $patient_array = array();
                        foreach ($patients as $patient):
                            array_push($patient_array, '<a href="' . Router::url(array('action' => 'index', 'task' => 'edit', 'patient_id' => $patient['PatientDemographic']['patient_id'])) . '" target="_blank">' . $patient['PatientDemographic']['first_name'] . ' ' . $patient['PatientDemographic']['last_name'] . '</a>');
                        endforeach;
                        echo implode(", ", $patient_array);
                    }
                    exit;
                } break;
            default: {
                    $this->redirect(array('action' => 'search_charts'));
                }
        }

        // for quick visit encounter 
        if ($patient_id && strlen($calendar_id) == 0)
            $this->quick_visit_encounter($patient_id);

        $general_information_access = $this->getAccessType("patients", "general_information");
        $medical_information_access = $this->getAccessType("patients", "medical_information");
        $attachments_access = $this->getAccessType("patients", "attachments");

        $this->set("general_information_access", $general_information_access);
        $this->set("medical_information_access", $medical_information_access);
        $this->set("attachments_access", $attachments_access);

        if ($view == 'medical_information' && $medical_information_access == 'NA') {
            $this->redirect(array('controller' => 'administration', 'action' => 'no_access'));
        } else if ($view == 'attachments' && $attachments_access == 'NA') {
            $this->redirect(array('controller' => 'administration', 'action' => 'no_access'));
        } else {
            if ($general_information_access == 'NA') {
                $this->redirect(array('controller' => 'administration', 'action' => 'no_access'));
            }
        }
    }

    /*
     * params patient id
     * return quick visit encounter create button 
     */

    public function quick_visit_encounter($patient_id = '') {
        $this->loadModel('UserGroup');
        $isProvider = $this->UserGroup->isProvider($this);
        $this->Set('isProvider', $isProvider);
        if ($isProvider === false)// if current user is not a provider no need to continue
            return;

        if (empty($patient_id)) {
            $patient_id = $this->params['named']['patient_id'];
            if (empty($patient_id))
                exit;
            $this->layout = "empty";
            $this->loadModel("PatientDemographic");
            $item = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
            $this->set("demographic_info", $item['PatientDemographic']);
        }

        $this->loadModel('ScheduleCalendar');
        $locations = $this->PracticeLocation->find('list', array('order' => 'PracticeLocation.location_name', 'fields' => 'PracticeLocation.location_name')); // get list of practice locations
        $patient_last_schedule_location = $this->ScheduleCalendar->find('first', array('conditions' => array('patient_id' => $patient_id), 'fields' => array('location'), 'order' => array('date desc', 'starttime desc'), 'recursive' => -1));
        $this->Set(compact('locations', 'patient_last_schedule_location'));
    }

    public function zipcode() {
        $this->loadModel("Zipcode");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $zipcode = (isset($this->data['zipcode'])) ? $this->data['zipcode'] : "";
        $this->Zipcode->execute($this, $task, $zipcode);
    }

    function demographics_counties($state) {
        $this->loadModel('CountyCodes');


        $counties = $this->CountyCodes->getCounties($state);



        exit(json_encode($counties));
    }

    public function add_patient() {
        $this->redirect(array('action' => 'index', 'task' => 'addnew'));
    }

    public function demographics() {
        $this->layout = "blank";
        $this->loadModel("PatientDemographic");
        $this->loadModel("PatientSocialHistory");
        $this->loadModel("StateCode");
        $this->loadModel("MaritalStatus");
        $this->loadModel("Race");
        $this->loadModel("Ethnicity");
        $this->loadModel("PreferredLanguage");
        $this->loadModel("PracticeLocation");
        $this->loadModel("ImmtrackCountry");
        $this->loadModel("County");
        $this->loadModel("ImmtrackVfc");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];

        if ($this->Session->read('search_last_name')) {
            $search_data['last_name'] = $this->Session->read('search_last_name');
            $this->Session->delete('search_last_name');
        }
        if ($this->Session->read('search_firstname')) {
            $search_data['first_name'] = $this->Session->read('search_firstname');
            $this->Session->delete('search_firstname');
        }
        if ($this->Session->read('search_ssn')) {
            $search_data['ssn'] = $this->Session->read('search_ssn');
            $this->Session->delete('search_ssn');
        }
        if ($this->Session->read('search_dob')) {
            $search_data['dob'] = $this->Session->read('search_dob');
            $this->Session->delete('search_dob');
        }
        if (!empty($search_data))
            $this->set("search", $search_data);

        if ($task == "addnew" || $task == "edit") {
            $this->set("StateCode", $this->sanitizeHTML($this->StateCode->find('all')));
            $this->set("MaritalStatus", $this->sanitizeHTML($this->MaritalStatus->find('all')));
            $this->set("Race", $this->sanitizeHTML($this->Race->find('all')));
            $this->set("Ethnicities", $this->sanitizeHTML($this->Ethnicity->find('all')));
            $this->set("PreferredLanguages", $this->sanitizeHTML($this->PreferredLanguage->find('all')));
            $this->set("PracticeLocations", $this->sanitizeHTML($this->PracticeLocation->find('all')));
            $this->set("ImmtrackCountries", $this->sanitizeHTML($this->ImmtrackCountry->find('all')));
            $this->set("Counties", $this->sanitizeHTML($this->County->find('all')));
            $this->set("ImmtrackVfcs", $this->sanitizeHTML($this->ImmtrackVfc->find('all')));

            $practice_location = $this->PracticeLocation->find('list', array(
                'order' => 'PracticeLocation.state',
                'fields' => 'PracticeLocation.state'
                    ));
            $practice_location = array_unique($practice_location);
            if (count($practice_location) == 1) {
                $state_codes = $this->StateCode->find('first', array('conditions' => array('StateCode.fullname' => current($practice_location))));
            } else {
                $state_codes['StateCode']['state'] = "";
            }
            $this->set("StateCodes", $this->sanitizeHTML($state_codes['StateCode']['state']));
        }

        $emdeon_xml_api = new Emdeon_XML_API();
        $isEmdeonOk = $emdeon_xml_api->isOK();
        $this->set('isEmdeonOk', $isEmdeonOk);

        $dosespot_xml_api = new Dosespot_XML_API();
        $isDosespotOk = $dosespot_xml_api->isOK();
        $this->set('isDosespotOk', $isDosespotOk);

        switch ($task) {
            case 'approve':
                $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

                $this->PatientDemographic->id = $patient_id;
                $this->PatientDemographic->saveField('status', 'Active');

                $this->Session->setFlash('Patient Approved and set to Active status');
                $this->redirect(array(
                    'controller' => 'patients',
                    'action' => 'index',
                    'task' => 'edit',
                    'patient_id' => $patient_id,
                ));
                exit();
                break;
            case "patient_user": {
                    if (!isset($this->data['UserAccount']['user_id'])) {

                        if (!empty($this->data['UserAccount']['dob'])) {
                            $date_dob = data::formatDateToStandard($this->__global_date_format, $this->data['UserAccount']['dob']);
                            $date_dob = __date('m/d/Y', strtotime($date_dob));
                        } else {
                            $date_dob = __date('m/d/Y');
                        }

                        $date_ar = explode("/", $date_dob);
                        $lastdigits_year = $date_ar[2];
                        $username_code = $date_ar[0] . $date_ar[1] . $lastdigits_year[2] . $lastdigits_year[3];
                        $user_firstname = $this->data['UserAccount']['firstname'];
                        $firstinitial = $user_firstname[0];
                        $this->data['UserAccount']['username'] = $firstinitial . $this->data['UserAccount']['lastname'] . $username_code;
                        //$this->data['UserAccount']['username'] = $this->data['UserAccount']['firstname'].rand(10000, 99999);
                        $this->data['UserAccount']['password'] = $this->data['UserAccount']['lastname'] . rand(10000, 99999);

                        $this->data['UserAccount']['username'] = preg_replace('/\s+/', '', $this->data['UserAccount']['username']);
                        $this->data['UserAccount']['password'] = preg_replace('/\s+/', '', $this->data['UserAccount']['password']);
                        

                        $count = $this->UserAccount->find('count', array('conditions' => array('UserAccount.username' => $this->data['UserAccount']['username'])));
                        while ($count > 0) {
                            $this->data['UserAccount']['username'] = $this->data['UserAccount']['firstname'] . rand(10000, 99999);
                            $this->data['UserAccount']['username'] = preg_replace('/\s+/', '', $this->data['UserAccount']['username']);
                            $count = $this->UserAccount->find('count', array('conditions' => array('UserAccount.username' => $this->data['UserAccount']['username'])));
                        }
                        if ($this->data['UserAccount']['patient_id']) {
                            $this->UserAccount->create();
                            $this->UserAccount->save($this->data);
                            $array_data[0] = $this->UserAccount->getLastInsertId();
                        } else {
                            $array_data[0] = 0;
                        }
                        $array_data[1] = $this->data['UserAccount']['username'];
                        $array_data[2] = $this->data['UserAccount']['password'];
                        echo json_encode($array_data);
                    } else {
                        $this->UserAccount->save($this->data);
                    }
                    exit();
                } break;


            case 'patient_representative':
                if (!isset($this->data['UserAccount']['user_id'])) {


                    $user_firstname = strtolower(trim($this->data['UserAccount']['firstname']));
                    $user_firstname = str_replace(' ', '', $user_firstname);
                    $firstinitial = $user_firstname[0];
                    $username_code = rand(10000, 99999);
                    $this->data['UserAccount']['username'] = $firstinitial . $this->data['UserAccount']['lastname'] . $username_code;
                    $this->data['UserAccount']['password'] = strtolower($this->data['UserAccount']['lastname']) . rand(10000, 99999);
                    $count = $this->UserAccount->find('count', array('conditions' => array('UserAccount.username' => $this->data['UserAccount']['username'])));

                    while ($count > 0) {
                        $this->data['UserAccount']['username'] = $user_firstname . rand(10000, 99999);
                        $count = $this->UserAccount->find('count', array('conditions' => array('UserAccount.username' => $this->data['UserAccount']['username'])));
                    }

                    $this->data['UserAccount']['role_id'] = EMR_Roles::getPatientRepRoleId();
                    if ($this->data['UserAccount']['patient_id']) {
                        $this->UserAccount->create();
                        $this->UserAccount->save($this->data);
                        $array_data['user_id'] = $this->UserAccount->getLastInsertId();
                    } else {
                        $array_data['user_id'] = '';
                    }
                    $array_data['username'] = $this->data['UserAccount']['username'];
                    $array_data['password'] = $this->data['UserAccount']['password'];
                    echo json_encode($array_data);
                } else {
                    $this->UserAccount->save($this->data);
                }
                exit();
                break;




            case 'delete_photo':

                $patient_id = $this->params['form']['patient_id'];

                $patientData = $this->PatientDemographic->getPatient($patient_id);

                $this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;

                $original = UploadSettings::existing(
                                $this->paths['patients'] . $patientData['patient_photo'], $this->paths['patient_id'] . $patientData['patient_photo']
                );

                @unlink($original);

                $this->PatientDemographic->id = $patient_id;
                $this->PatientDemographic->saveField('patient_photo', '');

                exit();
                break;
            case 'delete_license':
                $patient_id = $this->params['form']['patient_id'];

                $patientData = $this->PatientDemographic->getPatient($patient_id);

                $this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;

                $original = UploadSettings::existing(
                                $this->paths['patients'] . $patientData['driver_license'], $this->paths['patient_id'] . $patientData['driver_license']
                );

                @unlink($original);

                $this->PatientDemographic->id = $patient_id;
                $this->PatientDemographic->saveField('driver_license', '');

                exit();
                break;

            case "save_photo": {
                    $patientData = $this->PatientDemographic->getPatient($this->data['PatientDemographic']['patient_id']);

                    $this->paths['patient_id'] = $this->paths['patients'] . $patientData['patient_id'] . DS;
                    UploadSettings::createIfNotExists($this->paths['patient_id']);

                    if ($this->data['photo_type'] == 'photo') {
                        $source_file = $this->paths['temp'] . $this->data['PatientDemographic']['patient_photo'];
                        $destination_file = $this->paths['patient_id'] . $this->data['PatientDemographic']['patient_photo'];
                        $original = $this->paths['patient_id'] . $patientData['patient_photo'];

                        $original = UploadSettings::existing(
                                        $this->paths['patients'] . $patientData['patient_photo'], $this->paths['patient_id'] . $patientData['patient_photo']
                        );
                    } else {
                        $source_file = $this->paths['temp'] . $this->data['PatientDemographic']['driver_license'];
                        $destination_file = $this->paths['patient_id'] . $this->data['PatientDemographic']['driver_license'];
                        $original = UploadSettings::existing(
                                        $this->paths['patients'] . $patientData['driver_license'], $this->paths['patient_id'] . $patientData['driver_license']
                        );
                    }

                    @unlink($original);

                    @copy($source_file, $destination_file);
                    @unlink($source_file); // remove temp file
                    $this->PatientDemographic->saveAudit('Update');
                    $this->PatientDemographic->save($this->data);
                    echo 'saved';
                    exit;
                } break;
            case "check_mrn": {
                    $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

                    if ($this->PatientDemographic->checkMRN($this->data['PatientDemographic']['mrn'], $patient_id)) {
                        echo "false";
                    } else {
                        echo "true";
                    }

                    exit;
                } break;
            case "addnew": {
                    if (!empty($this->data)) {
                        if ($this->PatientDemographic->checkMRN($this->data['PatientDemographic']['mrn'])) {
                            echo json_encode(array('mrn_error' => 'yes'));
                            exit;
                        }
                        $this->PatientDemographic->create();

                        $this->data['PatientDemographic']['mrn'] = 0; //Dummy value assigned to MRN initially
                        $this->data['PatientDemographic']['dob'] = data::formatDateToStandard($this->__global_date_format, $this->data['PatientDemographic']['dob']);

                        $this->PatientDemographic->encodeRaces($this->data);
                        $this->PatientDemographic->saveAudit('New');
                        $this->PatientDemographic->save($this->data);
                        $patient_id = $this->PatientDemographic->getLastInsertID();

                        if ($patient_id) {
                            $this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
                            UploadSettings::createIfNotExists($this->paths['patient_id']);

                            $this->loadModel('PatientDocument');
                            $this->PatientDocument->saveTempDoc($this, $patient_id);

                            if (strlen($this->data['PatientDemographic']['patient_photo']) > 0) {
                                $source_file = $this->paths['temp'] . $this->data['PatientDemographic']['patient_photo'];
                                $destination_file = $this->paths['patient_id'] . $this->data['PatientDemographic']['patient_photo'];

                                @rename($source_file, $destination_file);
                            }

                            if (strlen($this->data['PatientDemographic']['driver_license']) > 0) {
                                $source_file = $this->paths['temp'] . $this->data['PatientDemographic']['driver_license'];
                                $destination_file = $this->paths['patient_id'] . $this->data['PatientDemographic']['driver_license'];

                                @rename($source_file, $destination_file);
                            }

                            // Add PracticeSetting start mrn #
                            $PracticeSetting = $this->Session->read("PracticeSetting");
                            $this->data['PatientDemographic']['mrn'] = $this->PatientDemographic->getNewMRN();
                            $this->PatientDemographic->save($this->data);
                            if ($this->data['PatientDemographic']['marital_status']) {
                                $maritalHistory = array(
                                    'PatientSocialHistory' => array(
                                        'marital_status' => $this->data['PatientDemographic']['marital_status'],
                                        'patient_id' => $patient_id,
                                        'type' => 'Marital Status',
                                    ),
                                );
                                $this->PatientSocialHistory->save($maritalHistory);
                            }
                        }

                        //Patient User
                        if ($this->data['UserAccount']['patient_user_username'] and $this->data['UserAccount']['patient_user_password']) {
                            $this->data['UserAccount']['username'] = $this->data['UserAccount']['patient_user_username'];
                            $this->data['UserAccount']['password'] = $this->data['UserAccount']['patient_user_password'];
                            $this->data['UserAccount']['role_id'] = EMR_Roles::PATIENT_ROLE_ID;
                            $this->data['UserAccount']['patient_id'] = $patient_id;
                            $this->data['UserAccount']['firstname'] = $this->data['PatientDemographic']['first_name'];
                            $this->data['UserAccount']['lastname'] = $this->data['PatientDemographic']['last_name'];
                            $this->data['UserAccount']['email'] = $this->data['PatientDemographic']['email'];
                            $this->UserAccount->create();
                            $this->UserAccount->save($this->data);
                        }

                        $this->PatientDemographic->updateEmdeonPatient($patient_id);
                        // add patient to dosespot, and get a dosespot ID
                        $this->PatientDemographic->updateDosespotPatient($patient_id);

                        $p = $this->Session->read("PracticeSetting");
                        if (!empty($p['PracticeSetting']['kareo_status'])) {
                            // export patient data into kareo
                            $this->loadModel('kareo');
                            $this->kareo->exportPatientToKareo($patient_id);
                        }
                        $ret = array();
                        $ret['task'] = $task;
                        $ret['patient_id'] = $patient_id;
                        echo json_encode($ret);

                        exit;
                    }

                    if ($this->getAccessType("administration", "users") != 'NA') {
                        $this->set("useraccount_access", 'yes');
                    } else {
                        $this->set("useraccount_access", 'no');
                    }
                } break;
            case "edit": {
                    // Flag if logged in user is a patient
                    $is_patient = EMR_Roles::isPatientRole($user['role_id']);
                    $this->set('is_patient', $is_patient);

                    if (!empty($this->data)) {
                        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                        if ($this->PatientDemographic->checkMRN($this->data['PatientDemographic']['mrn'], $patient_id)) {
                            echo json_encode(array('mrn_error' => 'yes'));
                            exit;
                        }
                        $this->data['PatientDemographic']['dob'] = data::formatDateToStandard($this->__global_date_format, $this->data['PatientDemographic']['dob']);
                        $this->PatientDemographic->encodeRaces($this->data);

                        $this->PatientDemographic->saveAudit('Update');
                        $this->PatientDemographic->save($this->data);

                        // Check Marital Status
                        $this->loadModel('PatientSocialHistory');
                        $maritalHistory = $this->PatientSocialHistory->find('first', array(
                            'conditions' => array(
                                'PatientSocialHistory.patient_id' => $patient_id,
                                'PatientSocialHistory.type' => 'Marital Status',
                            ),
                                ));

                        $this->data['PatientDemographic']['marital_status'] = trim($this->data['PatientDemographic']['marital_status']);
                        if ($this->data['PatientDemographic']['marital_status']) {

                            if ($maritalHistory) {
                                $maritalHistory['PatientSocialHistory']['marital_status'] = $this->data['PatientDemographic']['marital_status'];
                            } else {
                                $maritalHistory = array(
                                    'PatientSocialHistory' => array(
                                        'marital_status' => $this->data['PatientDemographic']['marital_status'],
                                        'patient_id' => $patient_id,
                                        'type' => 'Marital Status',
                                    ),
                                );
                            }

                            $this->PatientSocialHistory->save($maritalHistory);
                        } else {

                            if ($maritalHistory) {
                                $this->PatientSocialHistory->delete($maritalHistory['PatientSocialHistory']['social_history_id']);
                            }
                        }

                        $patient_user_id = 0;

                        // Note if there is an exisiting user account id for this user
                        if ($this->data['UserAccount']['patient_user_user_id']) {
                            $patient_user_id = $this->data['UserAccount']['patient_user_user_id'];
                        }

                        if ($this->data['UserAccount']['patient_user_username'] and $this->data['UserAccount']['patient_user_password']) {
                            // Check if user id is present
                            // which means patient currently has a user account
                            if ($this->data['UserAccount']['patient_user_user_id']) {
                                $this->data['UserAccount']['user_id'] = $this->data['UserAccount']['patient_user_user_id'];
                            }

                            // If user logged in is a patient
                            // do not allow username modification
                            if ($is_patient) {
                                unset($this->data['UserAccount']['username']);
                            } else {
                                $this->data['UserAccount']['username'] = $this->data['UserAccount']['patient_user_username'];
                            }

                            $this->data['UserAccount']['password'] = $this->data['UserAccount']['patient_user_password'];

                            // If this user already has an existing user account note it
                            $existingUserAccount = array();
                            if ($patient_user_id) {

                                $existingUserAccount = $this->UserAccount->getCurrentUser($patient_user_id);

                                // If we are changing the password, set password_last_update to 0
                                // to trigger password change for user
                                if ($this->data['UserAccount']['password'] !== $existingUserAccount['password']) {
                                    $this->data['UserAccount']['password_last_update'] = 0;
                                }
                            }

                            // Set User Patient User Role
                            $this->data['UserAccount']['role_id'] = EMR_Roles::PATIENT_ROLE_ID;
                            // Relate to patient demographic via patient_id
                            $this->data['UserAccount']['patient_id'] = $patient_id;

                            // Minimize private data we will be synching and exposing
                            // from the patient demographics
                            $this->data['UserAccount']['firstname'] = $this->data['PatientDemographic']['first_name'];
                            $this->data['UserAccount']['lastname'] = $this->data['PatientDemographic']['last_name'];
                            $this->data['UserAccount']['email'] = $this->data['PatientDemographic']['email'];

                            $this->UserAccount->save($this->data);

                            // If account user id was not set or missing
                            // this means user account for this patient was just created
                            // Note the newly created user account id
                            if (!isset($this->data['UserAccount']['user_id'])) {
                                $patient_user_id = $this->UserAccount->getLastInsertId();
                            }
                        }


                        if (isset($this->data['PatientRepresentative']['user_id']) && trim($this->data['PatientRepresentative']['user_id'])) {
                            $this->data['PatientRepresentative']['username'] = $this->data['PatientRepresentative']['patient_username'];
                            $this->data['PatientRepresentative']['password'] = $this->data['PatientRepresentative']['patient_password'];
                            $this->UserAccount->save($this->data['PatientRepresentative'], false, array(
                                'username', 'password', 'firstname', 'lastname',
                            ));
                        }

                        $this->PatientDemographic->updateEmdeonPatient($patient_id);

                        $p = $this->Session->read("PracticeSetting");
                        if (!empty($p['PracticeSetting']['kareo_status'])) {
                            // export patient data into kareo
                            $this->loadModel('kareo');
                            $this->kareo->exportPatientToKareo($patient_id);
                        }
                        $ret = array();
                        $ret['task'] = $task;
                        $ret['patient_id'] = $patient_id;

                        // Send patient's user account id along with other info
                        $ret['patient_user_id'] = $patient_user_id;
                        echo json_encode($ret);




                        exit;
                    } else {
                        $this->PatientDemographic->contain(array(
                            'PatientMaritalStatus', 'PatientOccupation',
                        ));
                        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                        $item = $this->PatientDemographic->find(
                                'first', array(
                            'conditions' => array('PatientDemographic.patient_id' => $patient_id)
                                )
                        );

                        $this->PatientDemographic->decodeRaces($this->data);
                        $this->set('EditItem', $item);
                        $this->PatientDemographic->saveAudit('View');
                        $patient_exist_in_useraccount = $this->UserAccount->checkPatient($patient_id);
                        $this->set("patient_exist_in_useraccount", $patient_exist_in_useraccount);

                        $patient_user = $this->UserAccount->find('first', array('fields' => array('UserAccount.user_id', 'UserAccount.username', 'UserAccount.password'), 'conditions' => array('UserAccount.role_id' => EMR_Roles::PATIENT_ROLE_ID, 'UserAccount.patient_id' => $patient_id)));
                        $this->set("patient_user", $patient_user);

                        $representative = $this->UserAccount->find(
                                'first', array(
                            'fields' => array('UserAccount.user_id', 'UserAccount.username', 'UserAccount.password', 'UserAccount.firstname', 'UserAccount.lastname'),
                            'conditions' => array('UserAccount.role_id' => EMR_Roles::getPatientRepRoleId(), 'UserAccount.patient_id' => $patient_id)));
                        $this->set("representative", $representative);

                        if ($this->getAccessType("administration", "users") != 'NA') {
                            $this->set("useraccount_access", 'yes');
                        } else {
                            $this->set("useraccount_access", 'no');
                        }
                    }
                } break;
        }
    }

    public function search_charts() {
        $this->loadModel("PatientDemographic");
        $this->loadModel("PracticeLocation");
        $locations = $this->PracticeLocation->find('list', array(
            'order' => 'PracticeLocation.location_name',
            'fields' => 'PracticeLocation.location_name'
                ));
        $this->Set(compact('locations'));
        $state_codes['StateCode']['state'] = "";
        $this->set("StateCodes", $this->sanitizeHTML($state_codes['StateCode']['state']));


        $task = isset($this->params['named']['task']) ? $this->params['named']['task'] : '';

        if ($task == 'patient_data') {

            $filename = isset($this->params['form']['file']) ? $this->params['form']['file'] : '';

            $filePath = rtrim(WWW_ROOT, DS) . $filename;

            if (!$filePath || !file_exists($filePath)) {
                echo json_encode(array('error' => 'File not found'));
                die();
            }

            $ccr_reader = new CCR_Reader($filePath);
            if ($ccr_reader->isValidDocument()) {
                $patient = $ccr_reader->getPatientInformation();
                echo json_encode($patient);
                die();
            }

            $ccda_reader = new CCDA_Reader($filePath);
            if ($ccda_reader->isValidDocument()) {
                $patient = $ccda_reader->getPatientInformation();
                echo json_encode($patient);
                die();
            }

            $ccd_reader = new CCD_Reader($filePath);
            if ($ccd_reader->isValidDocument()) {
                $patient = $ccd_reader->getPatientInformation();
                echo json_encode($patient);
                die();
            }

            echo json_encode(array('error' => 'Invalid CCDA document'));
            die();
        }

        $this->PatientDemographic->searchCharts($this);
    }

    public function search_charts_view() {
        $this->layout = "empty";
        $this->loadModel('UserGroup');
        $isProvider = $this->UserGroup->isProvider($this);
        $this->Set(compact('isProvider'));
        $this->loadModel("PatientDemographic");
        //echo "<pre>";
        //print_r($this->data);
        //exit("End here :P ");
        $this->PatientDemographic->searchChartsData($this, $this->data);
    }

    public function advance_directives() {
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientAdvanceDirective");

        if ($task == 'addnew' || $task == 'edit') {
            $user = $this->Session->read('UserAccount');
            $user_id = $user['user_id'];
            $this->set('role_id', $role_id = $user['role_id']);
        }

        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            if ($this->data['PatientAdvanceDirective']['attachment_is_uploaded'] == "true") {

                $this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
                UploadSettings::createIfNotExists($this->paths['patient_id']);

                $source_file = $this->paths['temp'] . $this->data['PatientAdvanceDirective']['attachment'];
                $destination_file = $this->paths['patient_id'] . $this->data['PatientAdvanceDirective']['attachment'];

                @rename($source_file, $destination_file);
            }

            $this->data['PatientAdvanceDirective']['terminally_ill'] = (isset($this->data['PatientAdvanceDirective']['terminally_ill']) ? 1 : 0);

            $this->data['PatientAdvanceDirective']['patient_id'] = $patient_id;
            $this->data['PatientAdvanceDirective']['service_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['PatientAdvanceDirective']['service_date'])));
        }

        switch ($task) {

            case "download_file": {

                    $advance_directive_id = (isset($this->params['named']['advance_directive_id'])) ? $this->params['named']['advance_directive_id'] : "";
                    $items = $this->PatientAdvanceDirective->find(
                            'first', array(
                        'conditions' => array('PatientAdvanceDirective.advance_directive_id' => $advance_directive_id)
                            )
                    );

                    $current_item = $items;


                    $file = $current_item['PatientAdvanceDirective']['attachment'];
                    $file_name = explode('_', $file);

                    // Since it is possible for the original file name to contain underscore(s)
                    // we get the name by discarding the left most part that contains the file hash
                    // and combine the remaining items in the split array. 
                    // If it did not have any underscores, we only have 1 element to combine
                    // If it had underscores, all remaining elements are combined
                    $file_attachment = implode('_', array_slice($file_name, 1));
                    $this->paths['patient_id'] = $this->paths['patients'] . $current_item['PatientAdvanceDirective']['patient_id'] . DS;

                    $targetFile = UploadSettings::existing(
                                    str_replace('//', '/', $this->paths['patients']) . $file, str_replace('//', '/', $this->paths['patient_id']) . $file
                    );

                    header('Content-Type: application/octet-stream; name="' . $file . '"');
                    header('Content-Disposition: attachment; filename="' . $file_attachment . '"');
                    header('Accept-Ranges: bytes');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Content-transfer-encoding: binary');
                    header('Content-length: ' . @filesize($targetFile));
                    @readfile($targetFile);

                    exit;
                } break;
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->PatientAdvanceDirective->create();
                        $this->PatientAdvanceDirective->saveAudit('New');
                        $this->PatientAdvanceDirective->save($this->data);

                        $ret = array();
                        echo json_encode($ret);

                        exit;
                    }
                }
                break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->PatientAdvanceDirective->saveAudit('Update');
                        $this->PatientAdvanceDirective->save($this->data);

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $advance_directive_id = (isset($this->params['named']['advance_directive_id'])) ? $this->params['named']['advance_directive_id'] : "";
                        $item = $this->PatientAdvanceDirective->find(
                                'first', array(
                            'conditions' => array('PatientAdvanceDirective.advance_directive_id' => $advance_directive_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($item));
                    }
                }
                break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientAdvanceDirective']['advance_directive_id'];

                        foreach ($ids as $id) {
                            $this->PatientAdvanceDirective->saveAudit('Delete');
                            $this->PatientAdvanceDirective->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }


                    echo json_encode($ret);
                    exit;
                }
                break;
            default: {
                    $this->paginate['PatientAdvanceDirective'] = array(
                        'conditions' => array('PatientAdvanceDirective.patient_id' => $patient_id),
                        'order' => array('PatientAdvanceDirective.modified_timestamp' => 'DESC')
                    );
                    $this->set('advance_directives', $this->sanitizeHTML($this->paginate('PatientAdvanceDirective')));
                    $this->PatientAdvanceDirective->saveAudit('View');
                }
        }
    }

    public function guarantor_details($patient_id) {
        $this->loadModel('PatientDemographic');
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $patient['ipad_dob'] = $patient['dob'];
        $patient['dob'] = __date($this->__global_date_format, strtotime($patient['dob']));
        die(json_encode($patient));
    }

    public function guarantor_information() {
        $emdeon_xml_api = new Emdeon_XML_API();
        $practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup = $practice_settings['PracticeSetting']['labs_setup'];

        $this->loadModel('PatientGuarantor');
        $this->loadModel('PatientDemographic');

        $this->layout = "empty";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $mrn = $patient['mrn'];

        $practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup = $practice_settings['PracticeSetting']['labs_setup'];

        $this->set('patient_data', $this->sanitizeHTML($patient));

        if ($task == 'addnew' || $task == 'edit') {
            $this->loadModel('EmdeonRelationship');
            $this->loadModel('StateCode');
            $this->set('states', $this->sanitizeHTML($this->StateCode->getList()));
            $this->set('relationships', $this->sanitizeHTML($this->EmdeonRelationship->find('all')));
            if (!empty($this->data)) {
                $emdeon_xml_api = new Emdeon_XML_API();
                $this->data['PatientGuarantor']['person'] = 0;

                if ($emdeon_xml_api->checkConnection()) {
                    $person = $emdeon_xml_api->getPersonByMRN($mrn);
                    $this->data['PatientGuarantor']['person'] = $person;
                }
            }
        }

        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        if ($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection()) {
                            $this->PatientGuarantor->saveAudit('New');
                            $this->PatientGuarantor->saveGuarantor($this->data);
                        } else {
                            $this->PatientGuarantor->saveAudit('New');
                            $this->PatientGuarantor->save($this->data);
                        }

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        if ($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection()) {
                            $this->PatientGuarantor->saveAudit('Update');
                            $this->PatientGuarantor->saveGuarantor($this->data);
                        } else {
                            $this->PatientGuarantor->saveAudit('Update');
                            $this->PatientGuarantor->save($this->data);
                        }

                        $ret = array();
                        echo json_encode($ret);

                        exit;
                    } else {
                        $guarantor_id = (isset($this->params['named']['guarantor_id'])) ? $this->params['named']['guarantor_id'] : "";
                        $items = $this->PatientGuarantor->find(
                                'first', array(
                            'conditions' => array('PatientGuarantor.guarantor_id' => $guarantor_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientGuarantor']['guarantor_id'];

                        foreach ($ids as $id) {
                            if ($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection()) {
                                $this->PatientGuarantor->saveAudit('Delete');
                                $this->PatientGuarantor->deleteGuarantor($id);
                            } else {
                                $this->PatientGuarantor->saveAudit('Delete');
                                $this->PatientGuarantor->delete($id);
                            }

                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            case "get_content": {
                    $guarantor_id = (isset($this->data['guarantor_id'])) ? $this->data['guarantor_id'] : "";
                    $guarantor_content = $this->PatientGuarantor->find('first', array('conditions' => array('PatientGuarantor.patient_id' => $this->data['patient_id'], 'PatientGuarantor.relationship' => $this->data['relationship'], 'PatientGuarantor.guarantor_id' => $guarantor_id)));
                    $ret = array();
                    $ret['content'] = $guarantor_content['PatientGuarantor'];
                    echo json_encode($ret);

                    exit;
                }break;
            default: {
                    $page = (isset($this->params['named']['page'])) ? $this->params['named']['page'] : "";

                    if ($page == "" && $labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection()) {
                        $this->loadModel("PatientDemographic");
                        $this->PatientDemographic->updateEmdeonPatient($patient_id, true);
                        $this->PatientGuarantor->sync($patient_id);
                    }


                    $this->paginate['PatientGuarantor'] = array(
                        //'conditions' => array('PatientGuarantor.patient_id' => $patient_id, 'PatientGuarantor.relationship !=' => 18),
                        'conditions' => array('PatientGuarantor.patient_id' => $patient_id),
                        'order' => array('PatientGuarantor.modified_timestamp' => 'DESC')
                    );
                    $this->set('guarantors', $this->sanitizeHTML($this->paginate('PatientGuarantor')));



                    //$this->set('guarantors', $this->sanitizeHTML($this->paginate('PatientGuarantor', array('PatientGuarantor.patient_id' => $patient_id, 'PatientGuarantor.relationship !=' => 18))));

                    $this->PatientGuarantor->saveAudit('View');
                }
        }
    }

    public function insurance_information() {
        $this->loadModel("PatientDemographic");
        $this->loadModel("PatientInsurance");

        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "0";
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $mrn = $patient['mrn'];

        if ($patient['dob'] && !strpos($patient['dob'], '0000')) {
            $patient['dob_js'] = __date($this->__global_date_format, strtotime($patient['dob']));
        }

        $this->set('patient_state', $patient['state']);

        $practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup = $practice_settings['PracticeSetting']['labs_setup'];

        $emdeon_xml_api = new Emdeon_XML_API();

        if ($task == "save_photo") {
            $this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
            UploadSettings::createIfNotExists($this->paths['patient_id']);
            if ($this->data['photo_type'] == 'insurance_card_front') {
                $source_file = $this->paths['temp'] . $this->data['PatientInsurance']['insurance_card_front'];
                $destination_file = $this->paths['patient_id'] . $this->data['PatientInsurance']['insurance_card_front'];
            } else {
                $source_file = $this->paths['temp'] . $this->data['PatientInsurance']['insurance_card_back'];
                $destination_file = $this->paths['patient_id'] . $this->data['PatientInsurance']['insurance_card_back'];
            }

            @copy($source_file, $destination_file);
            @unlink($source_file); // remove temp file
            $this->PatientInsurance->saveAudit('Update');
            $this->PatientInsurance->save($this->data);


            echo 'saved';
            exit;
        }

        if ($task == 'addnew' || $task == 'edit') {
            $this->loadModel('EmdeonRelationship');
            $this->loadModel('StateCode');
            $this->set('states', $this->sanitizeHTML($this->StateCode->getList()));
            $this->set('relationships', $this->sanitizeHTML($this->EmdeonRelationship->find('all')));
            if (!empty($this->data)) {
                $emdeon_xml_api = new Emdeon_XML_API();
                $person = $emdeon_xml_api->getPersonByMRN($mrn);
                $this->data['PatientInsurance']['person'] = $person;
            }

            $this->set("priority_values", $this->PatientInsurance->getPriorityValues($patient_id, $insurance_info_id));
        }

        switch ($task) {
            case "get_patient_data": {
                    $patientData = json_encode($patient);
                    echo $patientData;
                    exit;
                } break;
            case "search_insurance": {

                    if (isset($this->data['name'])) {
                        if ($labs_setup == 'Electronic') {
                            $this->data['name'] = str_replace('*', '', $this->data['name']);
                            $this->data['name'] = '*' . $this->data['name'] . '*';
                        } else {
                            $this->data['name'] = str_replace('%', '', $this->data['name']);
                        }
                    }

                    $search_options = array(
                        'type' => isset($this->data['type']) ? $this->data['type'] : '',
                        'name' => isset($this->data['name']) ? $this->data['name'] : '',
                        'address' => isset($this->data['address']) ? $this->data['address'] : '',
                        'city' => isset($this->data['city']) ? $this->data['city'] : '',
                        'state' => isset($this->data['state']) ? $this->data['state'] : '',
                        'hsi_value' => isset($this->data['hsi_value']) ? $this->data['hsi_value'] : ''
                    );
                    if ($labs_setup == 'Electronic') {
                        $emdeon_xml_api = new Emdeon_XML_API();
                        $search_results = $emdeon_xml_api->searchInsurance($search_options);
                    } else {
                        $this->loadModel('DirectoryInsuranceCompany');
                        $search_results = $this->DirectoryInsuranceCompany->searchInsurance($search_options);
                    }

                    echo json_encode($search_results);
                    exit;
                } break;
            case "addnew": {
                    if (!empty($this->data)) {
                        //$this->PatientInsurance->id = $this->data['PatientInsurance']['insurance_info_id'];
                        //$patient_id = $this->PatientInsurance->field('patient_id');

                        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : $this->data['PatientInsurance']['patient_id'];

                        if ($patient_id) {
                            $this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
                            UploadSettings::createIfNotExists($this->paths['patient_id']);

                            if (strlen($this->data['PatientInsurance']['insurance_card_front']) > 0) {
                                $source_file = $this->paths['temp'] . $this->data['PatientInsurance']['insurance_card_front'];
                                $destination_file = $this->paths['patient_id'] . $this->data['PatientInsurance']['insurance_card_front'];

                                @rename($source_file, $destination_file);
                            }

                            if (strlen($this->data['PatientInsurance']['insurance_card_back']) > 0) {
                                $source_file = $this->paths['temp'] . $this->data['PatientInsurance']['insurance_card_back'];
                                $destination_file = $this->paths['patient_id'] . $this->data['PatientInsurance']['insurance_card_back'];

                                @rename($source_file, $destination_file);
                            }
                        }
                        if ($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection()) {
                            $this->PatientInsurance->saveAudit('New');
                            $this->PatientInsurance->saveInsurance($this->data);
                        } else {
                            $this->PatientInsurance->saveAudit('New');
                            $this->PatientInsurance->save($this->data);
                        }

                        $p = $this->Session->read("PracticeSetting");
                        if (!empty($p['PracticeSetting']['kareo_status'])) {
                            // export patient data into kareo
                            $this->loadModel('kareo');
                            $this->kareo->exportPatientToKareo($patient_id);
                        }

                        $ret = array();
                        echo json_encode($ret);


                        exit;
                    }
                }
                break;
            case "edit": {
                    if (!empty($this->data)) {
                        if ($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection()) {
                            $this->PatientInsurance->saveAudit('Update');
                            $this->PatientInsurance->saveInsurance($this->data);
                        } else {
                            $this->PatientInsurance->saveAudit('Update');
                            $this->PatientInsurance->save($this->data);
                        }

                        $p = $this->Session->read("PracticeSetting");
                        if (!empty($p['PracticeSetting']['kareo_status'])) {
                            // export patient data into kareo
                            $this->loadModel('kareo');
                            $this->kareo->exportPatientToKareo($patient_id);
                        }

                        $ret = array();
                        echo json_encode($ret);


                        exit;
                    } else {
                        $items = $this->PatientInsurance->find('first', array('conditions' => array('PatientInsurance.insurance_info_id' => $insurance_info_id)));
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientInsurance']['insurance_info_id'];

                        foreach ($ids as $id) {
                            if ($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection()) {
                                $this->PatientInsurance->saveAudit('Delete');
                                $this->PatientInsurance->deleteInsurance($id, false);
                            } else {
                                $this->PatientInsurance->saveAudit('Delete');
                                $this->PatientInsurance->delete($id, false);
                            }
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                } break;
            default: {
                    $page = (isset($this->params['named']['page'])) ? $this->params['named']['page'] : "";

                    if ($page == "" and $labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection()) {
                        $this->loadModel("PatientDemographic");
                        $this->PatientDemographic->updateEmdeonPatient($patient_id, true);
                        $this->PatientInsurance->sync($patient_id);
                    }
                    /*
                      $this->PracticeSetting =& ClassRegistry::init('PracticeSetting');
                      $practice_settings = $this->PracticeSetting->getSettings();

                      if($labs_setup == 'Electronic')
                      {
                      $this->paginate['PatientInsurance'] = array(
                      'conditions' => array('PatientInsurance.patient_id' => $patient_id, 'PatientInsurance.ownerid' => $practice_settings->emdeon_facility),
                      'order' => array('PatientInsurance.start_date' => 'DESC')
                      );
                      }
                      else
                      {
                      $this->paginate['PatientInsurance'] = array(
                      'conditions' => array('PatientInsurance.patient_id' => $patient_id, 'PatientInsurance.insurance' => ''),
                      'order' => array('PatientInsurance.start_date' => 'DESC')
                      );
                      }
                     */
                    // show all insurances which are entered...emdeon and self-entered ones
                    $this->paginate['PatientInsurance'] = array(
                        'conditions' => array('PatientInsurance.patient_id' => $patient_id),
                        'order' => array('PatientInsurance.start_date' => 'DESC')
                    );

                    $this->set('insurance_datas', $this->sanitizeHTML($this->paginate('PatientInsurance')));

                    //$this->set('insurance_datas', $this->sanitizeHTML($this->paginate('PatientInsurance', array('PatientInsurance.patient_id' => $patient_id, 'PatientInsurance.ownerid' => $practice_settings->emdeon_facility))));

                    $this->PatientInsurance->saveAudit('View');
                }
        }
    }

    public function check_eligibility() {
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        switch ($task) {
            case "payer_list_autocomplete": {
                    $this->loadModel("EligibilityPayerList");
                    $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

                    $this->EligibilityPayerList->execute($this, "load_autocomplete");
                } break;
            case "service_type_autocomplete": {
                    $this->loadModel("EligibilityServiceType");
                    $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

                    $this->EligibilityServiceType->execute($this, "load_autocomplete");
                } break;
            case "provider_list_autocomplete": {
                    $this->loadModel("UserRole");
                    if (!empty($this->data)) {
                        $this->loadModel("UserGroup");
                        $this->loadModel("UserRole");

                        $providerRoles = $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, true);

                        $search_keyword = $this->data['autocomplete']['keyword'];
                        $search_limit = $this->data['autocomplete']['limit'];

                        $eligibility_provider_list_items = $this->UserRole->find('all', array(
                            'conditions' => array('AND' => array('UserRole.role_desc LIKE ' => '%' . $search_keyword . '%', 'UserRole.role_id' => $providerRoles)),
                            'limit' => $search_limit
                                ));
                        $data_array = array();

                        foreach ($eligibility_provider_list_items as $eligibility_provider_list_item) {
                            $data_array[] = $eligibility_provider_list_item['UserRole']['role_desc'];
                        }

                        echo implode("\n", $data_array);
                    }
                    exit();
                } break;
            case "check_eligibility": {
                    $x12 = ITS::print_elig($this->data);
                    $x12_arr = explode('~', $x12);
                    $new_x12 = "";
                    for ($i = 0; $i < count($x12_arr); $i++) {
                        $new_x12 .= trim($x12_arr[$i]) . '~';
                    }
                    $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                    $insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "0";
                    $this->Session->write('EligibilityRespond_' . $patient_id . '_' . $insurance_info_id, ITS::emdeonITS("X12", array("wsRequest" => $new_x12)));
                    exit;
                } break;
            case "eligibility_respond": {
                    $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                    $insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "0";
                    $this->set('insurance_info_id', $insurance_info_id);
                    $this->set('EligibilityRespond', $this->Session->read('EligibilityRespond_' . $patient_id . '_' . $insurance_info_id));
                } break;
            default: {
                    $this->loadModel("PatientDemographic");
                    $this->loadModel("PatientInsurance");
                    $this->loadModel("PatientPreference");
                    $this->loadModel("EligibilityPayerList");

                    $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                    $insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "0";

                    $this->Session->write('EligibilityRespond_' . $patient_id . '_' . $insurance_info_id, "");

                    $patient = $this->PatientDemographic->getPatient($patient_id);
                    $mrn = $patient['mrn'];

                    if ($patient['dob'] && !strpos($patient['dob'], '0000')) {
                        $patient['dob_js'] = __date($this->__global_date_format, strtotime($patient['dob']));
                    }

                    $this->set('patient_state', $patient['state']);

                    $practice_settings = $this->Session->read("PracticeSetting");
                    $labs_setup = $practice_settings['PracticeSetting']['labs_setup'];

                    $emdeon_xml_api = new Emdeon_XML_API();

                    $this->loadModel('EmdeonRelationship');
                    $this->loadModel('StateCode');
                    $this->set('states', $this->sanitizeHTML($this->StateCode->getList()));
                    $this->set('relationships', $this->sanitizeHTML($this->EmdeonRelationship->find('all')));
                    if (!empty($this->data)) {
                        $emdeon_xml_api = new Emdeon_XML_API();
                        $person = $emdeon_xml_api->getPersonByMRN($mrn);
                        $this->data['PatientInsurance']['person'] = $person;
                    }

                    $this->set("priority_values", $this->PatientInsurance->getPriorityValues($patient_id, $insurance_info_id));

                    $patient_preferences = $this->PatientPreference->find('first', array('conditions' => array('PatientPreference.patient_id' => $patient_id)));
                    $this->set("provider_npi", $patient_preferences['UserAccount']['npi']);

                    $items = $this->PatientInsurance->find('first', array('conditions' => array('PatientInsurance.insurance_info_id' => $insurance_info_id)));
                    $this->set('EditItem', $this->sanitizeHTML($items));
                } break;
        }
    }

    public function amendment_requests() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $amendment_request_id = (isset($this->params['named']['amendment_request_id'])) ? $this->params['named']['amendment_request_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');
        $this->set('role_id', $user['role_id']);
        $this->loadModel('PatientAmendmentRequest');
        $timestamp = __date("Y-m-d H:i:s");

        switch ($task) {
            case "addnew":
                if (!empty($this->data)) {
                    // Save the record
                    $this->PatientAmendmentRequest->create();
                    $this->data['PatientAmendmentRequest']['patient_id'] = $patient_id;
                    $this->data['PatientAmendmentRequest']['request_timestamp'] = $timestamp;
                    $this->data['PatientAmendmentRequest']['request_status'] = "New";
                    $this->data['PatientAmendmentRequest']['status_timestamp'] = $timestamp;
                    $this->data['PatientAmendmentRequest']['info_timestamp'] = null;
                    $this->PatientAmendmentRequest->saveAudit('New');
                    $this->PatientAmendmentRequest->save($this->data);
                    // FIXME: send someone, like the Advice Nurse, a message to review and process the amendment request!
                    exit();
                }
                break;
            case "edit":
                if (!empty($this->data)) {
                    $item = $this->PatientAmendmentRequest->find('first', array(
                        'conditions' => array(
                            'amendment_request_id' => $amendment_request_id
                        )
                            ));

                    // Save the record
                    if ($item['PatientAmendmentRequest']['request_timestamp'] == $item['PatientAmendmentRequest']['status_timestamp']) {
                        $this->data['PatientAmendmentRequest']['status_timestamp'] = $timestamp;
                        $this->data['PatientAmendmentRequest']['approver'] = $_SESSION['UserAccount']['user_id'];
                    }
                    if (!empty($this->data['PatientAmendmentRequest']['amendment_info'])) {
                        $this->data['PatientAmendmentRequest']['info_timestamp'] = $timestamp;
                    } else {
                        $this->data['PatientAmendmentRequest']['info_source'] = null;
                    }
                    $this->PatientAmendmentRequest->saveAudit('Update');
                    $this->PatientAmendmentRequest->save($this->data);
                    exit();
                } else {
                    $items = $this->PatientAmendmentRequest->find('first', array(
                        'conditions' => array(
                            'PatientAmendmentRequest.amendment_request_id' => $amendment_request_id
                        )
                            ));
                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
                break;
            default :
                $this->paginate['PatientAmendmentRequest'] = array(
                    'conditions' => array(
                        'PatientAmendmentRequest.patient_id' => $patient_id
                    ),
                    'order' => array(
                        'PatientAmendmentRequest.request_timestamp' => 'DESC'
                    )
                );
                $this->set('amendment_requests', $this->sanitizeHTML($this->paginate('PatientAmendmentRequest')));
                $this->PatientAmendmentRequest->saveAudit('View');
        }
    }

    public function disclosure_records() {
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $disclosure_id = (isset($this->params['named']['disclosure_id'])) ? $this->params['named']['disclosure_id'] : "";

        $this->loadModel("PatientDisclosure");
        $this->loadModel("PatientDemographic");

        $medical_information_access = $this->getAccessType("patients", "medical_information");
        $this->set("medical_information_access", $medical_information_access);

        $user = $this->Session->read('UserAccount');


        if (EMR_Roles::isPatientRole($user['role_id'])) {

            if ($patient_id != '' && intval($patient_id) !== intval($user['patient_id'])) {
                $this->Session->setFlash(__('Record not found.', true));
                $this->redirect(array('controller' => 'dashboard', 'action' => 'patient_portal'));
                exit();
            }

            if ($disclosure_id) {
                $disclosure = $this->PatientDisclosure->find('first', array(
                    'conditions' => array(
                        'PatientDisclosure.disclosure_id' => $disclosure_id
                    ),
                        ));

                if ($disclosure && intval($disclosure['PatientDisclosure']['patient_id']) !== intval($user['patient_id'])) {
                    $this->Session->setFlash(__('Record not found.', true));
                    $this->redirect(array('controller' => 'dashboard', 'action' => 'patient_portal'));
                    exit();
                }
            }
        }


        $this->set('role_id', $user['role_id']);
        $user_name = $user['firstname'] . ' ' . $user['lastname'];


        if ($patient_id) {
            $this->paths['patient_disclosures'] = $this->paths['patients'] . $patient_id . DS . 'disclosures' . DS;
            UploadSettings::createIfNotExists($this->paths['patient_disclosures']);
            $this->set('paths', $this->paths);
        }


        if (!empty($this->data) && ( $task == "addnew" || $task == "edit" || $task == "get_report_ccda" )) {
            $this->data['PatientDisclosure']['patient_id'] = $patient_id;
            $this->data['PatientDisclosure']['created_by'] = $user_name;

            $this->data['PatientDisclosure']['date_requested'] = __date("Y-m-d", ( empty($this->data['PatientDisclosure']['date_requested']) ? null : strtotime($this->data['PatientDisclosure']['date_requested'])));
            $this->data['PatientDisclosure']['service_date'] = __date("Y-m-d");

            $this->data['PatientDisclosure']['demographics'] = (int) @$this->data['PatientDisclosure']['demographics'];
            $this->data['PatientDisclosure']['allergies'] = (int) @$this->data['PatientDisclosure']['allergies'];
            $this->data['PatientDisclosure']['immunizations'] = (int) @$this->data['PatientDisclosure']['immunizations'];
            $this->data['PatientDisclosure']['problem_list'] = (int) @$this->data['PatientDisclosure']['problem_list'];
            $this->data['PatientDisclosure']['medication_list'] = (int) @$this->data['PatientDisclosure']['medication_list'];
            $this->data['PatientDisclosure']['lab_results'] = (int) @$this->data['PatientDisclosure']['lab_results'];
            $this->data['PatientDisclosure']['radiology_results'] = (int) @$this->data['PatientDisclosure']['radiology_results'];
            $this->data['PatientDisclosure']['health_maintenance'] = (int) @$this->data['PatientDisclosure']['health_maintenance'];
            $this->data['PatientDisclosure']['referrals'] = (int) @$this->data['PatientDisclosure']['referrals'];
            $this->data['PatientDisclosure']['direct_recipient'] = (int) @$this->data['PatientDisclosure']['direct_recipient'];
        }

        $patient = $this->PatientDemographic->getPatient($patient_id);
        switch ($task) {

            case 'direct_email_autocomplete': {
                    App::import('Lib', 'DirectEmail');
                    $addr = DirectEmail::getKnownAddresses();

                    $data = array();

                    foreach ($addr as $key => $val) {
                        $data[] = $key . '|' . $val;
                    }

                    echo implode("\n", $data);
                    die();
                }
                break;

            case 'direct_email_validate': {
                    App::import('Lib', 'DirectEmail');

                    $email = trim($this->data['PatientDisclosure']['recipient_direct_email']);

                    if (DirectEmail::validateAddress($email)) {
                        die('true');
                    }

                    die('"Email address not found."');
                }
                break;

            case "get_report_ccda": {
                    $this->layout = 'empty';


                    if (isset($this->data['PatientDisclosure']['download_zip']) && intval($this->data['PatientDisclosure']['download_zip'])) {
                        $timeCount = intval($this->data['PatientDisclosure']['visit_time_count']);
                        $timeUnit = strtolower(trim($this->data['PatientDisclosure']['visit_time_unit']));
                        $timeUnit = ($timeUnit == 'months') ? $timeUnit : 'years';


                        $conditions = array(
                            'EncounterMaster.encounter_status' => 'Closed',
                            'EncounterMaster.patient_id' => $patient_id,
                        );
                        if ($timeCount) {
                            $conditions['EncounterMaster.encounter_date >='] = __date('Y-m-d 00:00:0', strtotime('-' . $timeCount . ' ' . $timeUnit));
                        }

                        $this->loadModel('EncounterMaster');
                        $encounters = $this->EncounterMaster->find('all', array(
                            'conditions' => $conditions,
                                ));

                        $zipFile = $this->paths['temp'] . uniqid();
                        $zip = new ZipArchive();
                        if (!$zip->open($zipFile, ZipArchive::CREATE)) {
                            echo "\n Failed to create zip archive. \n";
                            die();
                        }


                        $pdffile = $patient['mrn'] . '_Medical_Records.pdf';

                        $options = array(
                            'return' => true,
                            'output_as_file' => false,
                            'selections' => $this->data['PatientDisclosure']
                        );
                        $ccda = new CCDA_Generator();
                        $data = $ccda->generate($patient_id, null, null, $options);

                        $xml = new DOMDocument();
                        $xml->loadXML($data);

                        $xsl = new DOMDocument();
                        $xsl->load(WWW_ROOT . 'ccda' . DS . 'CDA.xsl');

                        $proc = new XSLTProcessor();
                        $proc->importStyleSheet($xsl); // attach the xsl rules

                        $doc = $proc->transformToDoc($xml);
                        $html = $doc->saveHTML();


                        $html = Disclosure_Records::generateSimpleReport($patient_id, $disclosure_id, $this->data['PatientDisclosure']);
                        $filename = $patient['mrn'] . '_Medical_Records.pdf';

                        $file = $this->paths['temp'] . $filename;

                        if ($task == 'send_ccda_fax') {
                            $this->Session->write('fileName', $file);
                            $this->redirect(array(
                                'controller' => 'messaging',
                                'action' => 'new_fax',
                                'fax_doc'
                            ));
                            die();
                        }

                        site::write(pdfReport::generate($html), $file);

                        $zip->addFile($file, $filename);

                        $visitSummaryFiles = array();

                        foreach ($encounters as $e) {

                            $encounter_id = $e['EncounterMaster']['encounter_id'];
                            if (empty($e['UserAccount']['new_pt_note']) || empty($e['UserAccount']['est_pt_note'])) {
                                $defaultFormat = 'soap';
                            } else {
                                $defaultFormat = 'full';
                            }

                            if ($e['PatientDemographic']['status'] == 'New') {
                                $defaultFormat = 'soap';

                                if ($e['UserAccount']['new_pt_note'] == '1') {
                                    $defaultFormat = 'full';
                                }
                            } else {
                                $defaultFormat = 'soap';

                                if ($e['UserAccount']['est_pt_note'] == '1') {
                                    $defaultFormat = 'full';
                                }
                            }

                            $snapShots = Visit_Summary::getSnapShot($encounter_id);

                            $pdf = Visit_Summary::generatePdf($snapShots[$defaultFormat]);
                            $file = 'encounter_' . $encounter_id . '_summary.pdf';

                            $targetFile = $this->paths['temp'] . $file;
                            site::write($pdf, $targetFile);


                            $zip->addFile($targetFile, $file);
                        }

                        $zip->close();

                        $filename = 'patient_disclosure_' . $patient_id . '.zip';
                        header('Content-Type: application/octet-stream; name="' . $filename . '"');
                        header('Content-Disposition: attachment; filename="' . $filename . '"');
                        header('Accept-Ranges: bytes');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Content-transfer-encoding: binary');
                        header('Content-length: ' . @filesize($zipFile));
                        @readfile($zipFile);

                        @unlink($zipFile);
                        exit();
                    }

                    $report = Disclosure_Records::generateReport($patient_id, $disclosure_id, $this->data['PatientDisclosure']);
                    if (!$report)
                        die('could not generate CCDA');
                    if (!empty($this->data)) {
                        // Save the record
                        $this->data['PatientDisclosure']['type'] = 'Medical Records';
                        $this->data['PatientDisclosure']['recipient'] = $user_name;
                        $this->data['PatientDisclosure']['description'] = 'viewed/downloaded medical records';
                        $this->data['PatientDisclosure']['requested_by'] = $user_name;
                        $this->PatientDisclosure->create();
                        $this->PatientDisclosure->saveAudit('New');
                        $this->PatientDisclosure->save($this->data);
                        $disclosure_id = $this->PatientDisclosure->getLastInsertId();
                    }
                    echo $report;
                    exit();
                }
                break;
            case "get_ccda_xml": {
                    $session_data_id = (isset($this->params['named']['session_data_id'])) ? $this->params['named']['session_data_id'] : "";

                    header('Content-type: application/xml');
                    $ccda = new CCDA_Generator();
                    $options = array(
                        'output_as_file' => false,
                        'selections' => $_SESSION[$session_data_id]
                    );
                    echo $ccda->generate($patient_id, 0, 0, $options);
                    exit();
                }
                break;
            case "download_ccda": {
                    $this->PatientDisclosure->saveAudit('Download');
                    $session_data_id = (isset($this->params['named']['session_data_id'])) ? $this->params['named']['session_data_id'] : "";
                    $options = array(
                        'selections' => $_SESSION[$session_data_id]
                    );
                    $ccda = new CCDA_Generator();
                    $ccda->generate($patient_id, 0, 0, $options);
                    exit();
                }
                break;

            case "send_ccda_fax":
            case "download_ccda_pdf": {
                    $this->PatientDisclosure->saveAudit('Print');

                    $session_data_id = (isset($this->params['named']['session_data_id'])) ? $this->params['named']['session_data_id'] : "";
                    $options = array(
                        'return' => true,
                        'output_as_file' => false,
                        'selections' => $_SESSION[$session_data_id]
                    );
                    $ccda = new CCDA_Generator();
                    $data = $ccda->generate($patient_id, null, null, $options);

                    $xml = new DOMDocument();
                    $xml->loadXML($data);

                    $xsl = new DOMDocument();
                    $xsl->load(WWW_ROOT . 'ccda' . DS . 'CDA.xsl');

                    $proc = new XSLTProcessor();
                    $proc->importStyleSheet($xsl); // attach the xsl rules

                    $doc = $proc->transformToDoc($xml);
                    $html = $doc->saveHTML();

                    $filename = $patient['first_name'] . '_' . $patient['last_name'] . '_' . __date("m-d-Y") . '.pdf';

                    $file = $this->paths['temp'] . $filename;

                    site::write(pdfReport::generate($html), $file);

                    if ($task == 'send_ccda_fax') {
                        $this->Session->write('fileName', $file);
                        $this->redirect(array(
                            'controller' => 'messaging',
                            'action' => 'new_fax',
                            'fax_doc'
                        ));
                        die();
                    }

                    header('Content-Type: application/octet-stream; name="' . $filename . '"');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Accept-Ranges: bytes');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Content-transfer-encoding: binary');
                    header('Content-length: ' . @filesize($file));
                    @readfile($file);
                    exit();
                }
                break;

            case 'download_file': {

                    $disclosure = $this->PatientDisclosure->find('first', array(
                        'conditions' => array(
                            'PatientDisclosure.disclosure_id' => $disclosure_id,
                        ),
                            ));

                    if (!$disclosure) {
                        die('Disclosure record not found');
                    }

                    $file = $this->paths['patient_disclosures'] . $disclosure['PatientDisclosure']['filename'];

                    if (!file_exists($file)) {
                        die('File not found');
                    }
                    echo $file;
                    die();
                    $filename = explode('_', $disclosure['PatientDisclosure']['filename']);
                    unset($filename[0]);
                    $filename = implode('_', $filename);

                    header('Content-Type: application/octet-stream; name="' . $filename . '"');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Accept-Ranges: bytes');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Content-transfer-encoding: binary');
                    header('Content-length: ' . @filesize($file));
                    @readfile($file);
                    exit();
                }
                break;

            case 'remove_file': {

                    $disclosure = $this->PatientDisclosure->find('first', array(
                        'conditions' => array(
                            'PatientDisclosure.disclosure_id' => $disclosure_id,
                        ),
                            ));


                    if (!$disclosure) {
                        die('Disclosure record not found');
                    }

                    $file = $this->paths['patient_disclosures'] . $disclosure['PatientDisclosure']['filename'];

                    if (!file_exists($file)) {
                        die('File not found');
                    }

                    @unlink($file);

                    $this->PatientDisclosure->id = $disclosure['PatientDisclosure']['disclosure_id'];
                    $this->PatientDisclosure->saveField('filename', '');
                    echo 'Done';
                    exit();
                }
                break;

            case "addnew": {
                    if (!empty($this->data)) {
                        // Use the direct email recipient for the recipient field, if present
                        if ($this->data['PatientDisclosure']['direct_recipient'])
                            $this->data['PatientDisclosure']['recipient'] = $this->data['PatientDisclosure']['recipient_direct_email'];
                        unset($this->data['PatientDisclosure']['recipient_direct_email']);

                        // Save the record
                        $this->PatientDisclosure->create();
                        $this->PatientDisclosure->saveAudit('New');
                        $this->PatientDisclosure->save($this->data);
                        $disclosure_id = $this->PatientDisclosure->getLastInsertId();

                        if ($this->data['PatientDisclosure']['filename'] && file_exists($this->paths['temp'] . $this->data['PatientDisclosure']['filename'])) {
                            @rename($this->paths['temp'] . $this->data['PatientDisclosure']['filename'], $this->paths['patient_disclosures'] . $this->data['PatientDisclosure']['filename']);
                        }


                        // Perform the requested action
                        if ($this->data['PatientDisclosure']['type'] == 'Medical Records' && $this->data['PatientDisclosure']['direct_recipient']) {

                            $selections = array();

                            foreach ($this->data['PatientDisclosure'] as $key => $val) {
                                if ($val == '1') {
                                    $selections[] = $key;
                                }
                            }

                            $options = array(
                                'return' => true,
                                'output_as_file' => false,
                                'selections' => $selections,
                            );
                            $this->PatientDisclosure->sendDirectMail($disclosure_id, 0, $options);
                            $this->PatientDisclosure->saveAudit('Transmit');
                        }

                        if ($this->data['PatientDisclosure']['type'] == 'Patient File' && $this->data['PatientDisclosure']['direct_recipient']) {
                            $this->PatientDisclosure->sendDirectMail($disclosure_id, 0);
                            $this->PatientDisclosure->saveAudit('Transmit');
                        }
                        exit();
                    }
                }
                break;
            case "edit": {
                    if (!empty($this->data)) {
                        // Use the direct email recipient for the recipient field, if present
                        if ($this->data['PatientDisclosure']['direct_recipient'])
                            $this->data['PatientDisclosure']['recipient'] = $this->data['PatientDisclosure']['recipient_direct_email'];
                        unset($this->data['PatientDisclosure']['recipient_direct_email']);

                        // Save the record
                        $this->PatientDisclosure->saveAudit('Update');
                        $this->PatientDisclosure->save($this->data);
                        $disclosure_id = $this->PatientDisclosure->getLastInsertId();
                        if ($this->data['PatientDisclosure']['filename'] && file_exists($this->paths['temp'] . $this->data['PatientDisclosure']['filename'])) {
                            @rename($this->paths['temp'] . $this->data['PatientDisclosure']['filename'], $this->paths['patient_disclosures'] . $this->data['PatientDisclosure']['filename']);
                        }

                        // FIXME - do we resend if it is a direct message??

                        exit();
                    } else {
                        $items = $this->PatientDisclosure->find('first', array(
                            'conditions' => array(
                                'PatientDisclosure.disclosure_id' => $disclosure_id
                            )
                                ));
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                }
                break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientDisclosure']['disclosure_id'];

                        foreach ($ids as $id) {

                            $this->PatientDisclosure->id = $id;
                            $filename = $this->PatientDisclosure->field('filename');

                            if ($filename && file_exists($this->paths['patient_disclosures'] . $filename)) {
                                @unlink($this->paths['patient_disclosures'] . $filename);
                            }


                            $this->PatientDisclosure->saveAudit('Delete');
                            $this->PatientDisclosure->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit();
                }
            default : {
                    $this->paginate['PatientDisclosure'] = array(
                        'conditions' => array(
                            'PatientDisclosure.patient_id' => $patient_id
                        ),
                        'order' => array(
                            'PatientDisclosure.service_date' => 'DESC'
                        )
                    );
                    $this->set('disclosure_records', $this->sanitizeHTML($this->paginate('PatientDisclosure')));
                    // $this->set('disclosure_records', $this->sanitizeHTML($this->paginate('PatientDisclosure', array('patient_id' => $patient_id))));
                    $this->PatientDisclosure->saveAudit('View');
                }
        }
    }

    public function hx_medical() {
        $this->layout = "blank";
        $this->loadModel("PatientMedicalHistory");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $this->loadModel("PatientProblemList");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        //$this->set("Icd9", $this->sanitizeHTML($this->Icd9->find('all')));
        //$this->set('PatientMedicalHistory', $this->sanitizeHTML($this->PatientMedicalHistory->find('all')));

        $this->loadModel('PatientDemographic');
        $patient_data = $this->PatientDemographic->getPatient($patient_id);
        $this->set(compact('patient_data'));

        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            $this->data['PatientMedicalHistory']['patient_id'] = $patient_id;
            $this->data['PatientMedicalHistory']['encounter_id'] = 0;
            /* $this->data['PatientMedicalHistory']['diagnosis'] = $this->data['PatientMedicalHistory']['diagnosis'];
              $this->data['PatientMedicalHistory']['icd_code'] = $this->data['PatientMedicalHistory']['icd_code']; */
            $this->data['PatientMedicalHistory']['start_month'] = $this->data['PatientMedicalHistory']['start_month'];
            $this->data['PatientMedicalHistory']['start_year'] = $this->data['PatientMedicalHistory']['start_year'];
            $this->data['PatientMedicalHistory']['end_month'] = $this->data['PatientMedicalHistory']['end_month'];
            $this->data['PatientMedicalHistory']['end_year'] = $this->data['PatientMedicalHistory']['end_year'];
            $this->data['PatientMedicalHistory']['occurrence'] = $this->data['PatientMedicalHistory']['occurrence'];
            $this->data['PatientMedicalHistory']['comment'] = $this->data['PatientMedicalHistory']['comment'];
            $this->data['PatientMedicalHistory']['status'] = isset($this->data['PatientMedicalHistory']['status']) ? $this->data['PatientMedicalHistory']['status'] : '';
            $this->data['PatientMedicalHistory']['action'] = isset($this->data['PatientMedicalHistory']['action']) ? 'Moved' : '';
            $this->data['PatientMedicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['PatientMedicalHistory']['modified_user_id'] = $this->user_id;
        }

        switch ($task) {
            case "load_Icd9_autocomplete": {
                    if (!empty($this->data)) {
                        $this->Icd->execute($this, $task);
                    }
                    exit();
                }
                break;
            case "validate_duplicate": {
                    if (!empty($this->data)) {
                        $all_diagnosis = explode(',', $this->data['diagnosis']);
                        $patient_id = $this->data['patient_id'];
                        $return = array('result' => 'true');
                        foreach ($all_diagnosis as $diagnosis) {
                            $diagnosis = trim($diagnosis);
                            if (empty($diagnosis))
                                continue;
                            $count = $this->PatientMedicalHistory->find('count', array(
                                'conditions' => array('diagnosis' => $diagnosis, 'patient_id' => $patient_id)
                                    ));
                            if ($count) {
                                $return = array('result' => 'false');
                                break;
                            }
                        }
                    }
                    echo json_encode($return);
                    exit();
                }
                break;
            case "addnew": {
                    if (!empty($this->data)) {
                        if ($this->data['PatientMedicalHistory']['action'] == 'Moved') {  // Move to Problem List
                            $this->data['PatientProblemList']['patient_id'] = $patient_id;
                            /* $this->data['PatientProblemList']['diagnosis'] = $this->data['PatientMedicalHistory']['diagnosis'];
                              $this->data['PatientProblemList']['icd_code'] = $this->data['PatientMedicalHistory']['icd_code']; */
                            $this->data['PatientProblemList']['start_date'] = $this->data['PatientMedicalHistory']['start_year'] . '-' . $this->data['PatientMedicalHistory']['start_month'] . '-' . '00';
                            $this->data['PatientProblemList']['end_date'] = $this->data['PatientMedicalHistory']['end_year'] . '-' . $this->data['PatientMedicalHistory']['end_month'] . '-' . '00';
                            $this->data['PatientProblemList']['occurrence'] = $this->data['PatientMedicalHistory']['occurrence'];
                            $this->data['PatientProblemList']['comment'] = $this->data['PatientMedicalHistory']['comment'];
                            $this->data['PatientProblemList']['status'] = $this->data['PatientMedicalHistory']['status'];
                            $this->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                            $this->data['PatientProblemList']['modified_user_id'] = $this->user_id;
                        }

                        $all_diagnosis = explode(',', $this->data['PatientMedicalHistory']['diagnosis']);
                        foreach ($all_diagnosis as $diagnosis) {
                            $diagnosis = trim($diagnosis);
                            if (empty($diagnosis))
                                continue;
                            $icd9 = '';
                            // Check if matches with an ICD9 code format in the name...
                            if (preg_match('/\[(?P<icd9>[\w\.]+)]\s*$/i', $diagnosis, $match)) {
                                // Get the matching code
                                $icd9 = $match['icd9'];
                            }
                            $this->data['PatientMedicalHistory']['diagnosis'] = $diagnosis;
                            $this->data['PatientMedicalHistory']['icd_code'] = $icd9;
                            $this->PatientMedicalHistory->create();
                            $this->PatientMedicalHistory->saveAudit('New');
                            $this->PatientMedicalHistory->save($this->data);
                            unset($this->PatientMedicalHistory->id);
                            if ($this->data['PatientMedicalHistory']['action'] == 'Moved') {
                                // Check if not yet in problem list for this encounter
                                $prob = $this->PatientProblemList->find('count', array(
                                    'conditions' => array(
                                        'PatientProblemList.diagnosis' => $diagnosis,
                                        'PatientProblemList.icd_code' => $icd9,
                                        'patient_id' => $patient_id
                                    ),
                                        ));
                                // Not yet in problem list
                                if (!$prob) {
                                    $this->data['PatientProblemList']['diagnosis'] = $diagnosis;
                                    $this->data['PatientProblemList']['icd_code'] = $icd9;
                                    $this->PatientProblemList->create();
                                    $this->PatientProblemList->saveAudit('New');
                                    $this->PatientProblemList->save($this->data);
                                    unset($this->PatientProblemList->id);
                                }
                            }
                        }

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        if ($this->data['PatientMedicalHistory']['action'] == 'Moved') {  // Move to Problem List
                            $this->data['PatientProblemList']['patient_id'] = $patient_id;
                            $this->data['PatientProblemList']['diagnosis'] = $this->data['PatientMedicalHistory']['diagnosis'];
                            $this->data['PatientProblemList']['icd_code'] = $this->data['PatientMedicalHistory']['icd_code'];
                            $this->data['PatientProblemList']['start_date'] = $this->data['PatientMedicalHistory']['start_year'] . '-' . $this->data['PatientMedicalHistory']['start_month'] . '-' . '00';
                            $this->data['PatientProblemList']['end_date'] = $this->data['PatientMedicalHistory']['end_year'] . '-' . $this->data['PatientMedicalHistory']['end_month'] . '-' . '00';
                            $this->data['PatientProblemList']['occurrence'] = $this->data['PatientMedicalHistory']['occurrence'];
                            $this->data['PatientProblemList']['comment'] = $this->data['PatientMedicalHistory']['comment'];
                            $this->data['PatientProblemList']['status'] = $this->data['PatientMedicalHistory']['status'];
                            $this->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                            $this->data['PatientProblemList']['modified_user_id'] = $this->user_id;
                            $prob = $this->PatientProblemList->find('count', array(
                                'conditions' => array(
                                    'PatientProblemList.diagnosis' => $this->data['PatientMedicalHistory']['diagnosis'],
                                    'PatientProblemList.icd_code' => $this->data['PatientMedicalHistory']['icd_code'],
                                    'patient_id' => $patient_id
                                ),
                                    ));
                            // Not yet in problem list
                            if (!$prob) {
                                $this->PatientProblemList->create();
                                $this->PatientProblemList->saveAudit('New');
                                $this->PatientProblemList->save($this->data);
                            }
                            //Delete from Medical History
                            //$medical_history_id = (isset($this->params['named']['medical_history_id'])) ? $this->params['named']['medical_history_id'] : "";
                            //$this->PatientMedicalHistory->delete($medical_history_id, false);
                        }
                        //else
                        //{
                        $this->PatientMedicalHistory->saveAudit('Update');
                        $this->PatientMedicalHistory->save($this->data);

                        //}

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $medical_history_id = (isset($this->params['named']['medical_history_id'])) ? $this->params['named']['medical_history_id'] : "";
                        $items = $this->PatientMedicalHistory->find(
                                'first', array(
                            'conditions' => array('PatientMedicalHistory.medical_history_id' => $medical_history_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientMedicalHistory']['medical_history_id'];

                        foreach ($ids as $id) {
                            $this->PatientMedicalHistory->saveAudit('Delete');
                            $this->PatientMedicalHistory->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }break;
            default: {
                    $this->paginate['PatientMedicalHistory'] = array(
                        'conditions' => array('PatientMedicalHistory.patient_id' => $patient_id),
                        'order' => array('PatientMedicalHistory.modified_timestamp' => 'DESC')
                    );
                    $this->set('PatientMedicalHistory', $this->sanitizeHTML($this->paginate('PatientMedicalHistory')));
                    //$this->set('PatientMedicalHistory', $this->sanitizeHTML($this->paginate('PatientMedicalHistory', array('patient_id' => $patient_id))));

                    $this->PatientMedicalHistory->saveAudit('View');
                }
        }

        $this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
        $this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

        $this->loadModel("PatientDemographic");
        $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $PatientDemographic['gender']);

        $this->loadModel("FavoriteMedical");
        $favitems = $this->FavoriteMedical->find('all', array(
            'conditions' => array('FavoriteMedical.user_id' => $_SESSION['UserAccount']['user_id']),
            'order' => array('FavoriteMedical.diagnosis ASC'),
                ));
        $this->set('favitems', $this->sanitizeHTML($favitems));
    }

    public function hx_surgical() {
        $this->layout = "blank";
        $this->loadModel("PatientSurgicalHistory");
        // $this->loadModel("Icd9");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        // $this->set("Icd9", $this->sanitizeHTML($this->Icd9->find('all')));
        //$this->set('PatientSurgicalHistory', $this->sanitizeHTML($this->PatientSurgicalHistory->find('all')));

        $this->loadModel('PatientDemographic');
        $patient_data = $this->PatientDemographic->getPatient($patient_id);
        $this->set(compact('patient_data'));

        $PatientDemographic = ClassRegistry::init('PatientDemographic');
        $patient = $PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id)));
        $this->set('age', $patient['PatientDemographic']['age']);

        $dateFields = array(
            'date_from', 'date_to'
        );

        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            $this->data['PatientSurgicalHistory']['patient_id'] = $patient_id;
            $this->data['PatientSurgicalHistory']['encounter_id'] = 0;
            $this->data['PatientSurgicalHistory']['hospitalization'] = isset($this->data['PatientSurgicalHistory']['hospitalization']) ? $this->data['PatientSurgicalHistory']['hospitalization'] : '';
            $this->data['PatientSurgicalHistory']['reason'] = $this->data['PatientSurgicalHistory']['reason'];
            $this->data['PatientSurgicalHistory']['outcome'] = $this->data['PatientSurgicalHistory']['outcome'];
            $this->data['PatientSurgicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['PatientSurgicalHistory']['modified_user_id'] = $this->user_id;
            foreach ($dateFields as $f) {
                $this->data['PatientSurgicalHistory'][$f] = $this->data['PatientSurgicalHistory'][$f . '_year'] . '-' . $this->data['PatientSurgicalHistory'][$f . '_month'] . '-' . $this->data['PatientSurgicalHistory'][$f . '_day'];
            }
        }

        switch ($task) {
            case "load_Icd9_autocomplete": {
                    
                } break;
            case "addnew": {
                    if (!empty($this->data)) {
                        $all_surgeries = explode(',', $this->data['PatientSurgicalHistory']['surgery']);
                        foreach ($all_surgeries as $surgeries) {
                            $surgeries = trim($surgeries);
                            if (empty($surgeries))
                                continue;
                            $this->data['PatientSurgicalHistory']['surgery'] = $surgeries;
                            $this->PatientSurgicalHistory->create();
                            $this->PatientSurgicalHistory->saveAudit('New');
                            $this->PatientSurgicalHistory->save($this->data);
                            unset($this->PatientSurgicalHistory->id);
                        }
                        $ret = array();
                        echo json_encode($ret);


                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->PatientSurgicalHistory->saveAudit('Update');
                        $this->PatientSurgicalHistory->save($this->data);

                        $ret = array();
                        echo json_encode($ret);


                        exit;
                    } else {
                        $surgical_history_id = (isset($this->params['named']['surgical_history_id'])) ? $this->params['named']['surgical_history_id'] : "";
                        $items = $this->PatientSurgicalHistory->find(
                                'first', array(
                            'conditions' => array('PatientSurgicalHistory.surgical_history_id' => $surgical_history_id)
                                )
                        );


                        foreach ($dateFields as $f) {
                            $items['PatientSurgicalHistory'][$f] = trim($items['PatientSurgicalHistory'][$f]);

                            if (!$items['PatientSurgicalHistory'][$f]) {
                                $tmp = array('0000', '00', '00');
                            }

                            $tmp = explode('-', $items['PatientSurgicalHistory'][$f]);

                            $items['PatientSurgicalHistory'][$f . '_year'] = $tmp[0];
                            $items['PatientSurgicalHistory'][$f . '_month'] = $tmp[1];
                            $items['PatientSurgicalHistory'][$f . '_day'] = $tmp[2];
                        }
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientSurgicalHistory']['surgical_history_id'];

                        foreach ($ids as $id) {
                            $this->PatientSurgicalHistory->saveAudit('Delete');
                            $this->PatientSurgicalHistory->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $this->paginate['PatientSurgicalHistory'] = array(
                        'conditions' => array('PatientSurgicalHistory.patient_id' => $patient_id),
                        'order' => array('PatientSurgicalHistory.modified_timestamp' => 'DESC')
                    );

                    $this->set('PatientSurgicalHistory', $this->sanitizeHTML($this->paginate('PatientSurgicalHistory')));
                    //$this->set('PatientSurgicalHistory', $this->sanitizeHTML($this->paginate('PatientSurgicalHistory', array('patient_id' => $patient_id))));

                    $this->PatientSurgicalHistory->saveAudit('View');
                }
        }

        $this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
        $this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

        $this->loadModel("PatientDemographic");
        $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $PatientDemographic['gender']);
        if ($task == 'addnew' || $task == 'edit') {
            $this->loadModel("FavoriteSurgeries");
            $favitems = $this->FavoriteSurgeries->find('all', array(
                'fields' => 'distinct(surgeries)',
                'conditions' => array(
                    'FavoriteSurgeries.user_id' => $this->user_id,
                ),
                'order' => array(
                    'FavoriteSurgeries.surgeries ASC'
                ),
                    ));
            $this->set('favitems', $favitems);
        }
    }

    public function hx_social() {
        $this->layout = "blank";
        $this->loadModel("PatientSocialHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        $this->loadModel("MaritalStatus");
        $this->set("MaritalStatus", $this->sanitizeHTML($this->MaritalStatus->find('all')));

        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            $this->data['PatientSocialHistory']['pets'] = ((isset($this->params['form']['pets_option_1'])) ? $this->params['form']['pets_option_1'] : "") . "|" . ((isset($this->params['form']['pets_option_2'])) ? $this->params['form']['pets_option_2'] : "") . "|" . ((isset($this->params['form']['pets_option_3'])) ? $this->params['form']['pets_option_3'] : "") . "|" . ((isset($this->params['form']['pets_option_4'])) ? $this->params['form']['pets_option_4'] : "") . "|" . ((isset($this->params['form']['pets_option_5'])) ? $this->params['form']['pets_option_5'] : "");
            $this->data['PatientSocialHistory']['patient_id'] = $patient_id;
            $this->data['PatientSocialHistory']['encounter_id'] = 0;
            $this->data['PatientSocialHistory']['smoking_recodes'] = (trim($this->data['PatientSocialHistory']['smoking_recodes']) != "") ? $this->data['PatientSocialHistory']['smoking_recodes'] : 0;
            $this->data['PatientSocialHistory']['modified_user_id'] = $this->user_id;
            $this->data['PatientSocialHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
        }

        switch ($task) {
            case "load_Icd9_autocomplete": {
                    
                } break;
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->PatientSocialHistory->create();
                        $this->PatientSocialHistory->saveAudit('New');
                        if (!isset($this->data['PatientSocialHistory']['substance']) || $this->data['PatientSocialHistory']['substance'] != 'Tobacco') {
                            $this->data['PatientSocialHistory']['smoking_status'] = '';
                            $this->data['PatientSocialHistory']['snomed_ct'] = '';
                        }

                        $this->PatientSocialHistory->save($this->data);


                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->PatientSocialHistory->saveAudit('Update');
                        if (!isset($this->data['PatientSocialHistory']['substance']) || $this->data['PatientSocialHistory']['substance'] != 'Tobacco') {
                            $this->data['PatientSocialHistory']['smoking_status'] = '';
                            $this->data['PatientSocialHistory']['snomed_ct'] = '';
                        }

                        $this->PatientSocialHistory->save($this->data);


                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $social_history_id = (isset($this->params['named']['social_history_id'])) ? $this->params['named']['social_history_id'] : "";
                        //echo $social_history_id;
                        $items = $this->PatientSocialHistory->find(
                                'first', array(
                            'conditions' => array('PatientSocialHistory.social_history_id' => $social_history_id)
                                )
                        );
                        $last_user_inf = array(
                            'role_id' => '',
                            'full_name' => '(User no longer in the system)',
                        );
                        // who was last person to edit this record?			
                        $last_user = $this->UserAccount->getUserByID($items['PatientSocialHistory']['modified_user_id']);

                        if ($last_user) {
                            $last_user_inf['role_id'] = $last_user->role_id;
                            $last_user_inf['full_name'] = $last_user->full_name;
                        }


                        $this->set('last_user', $last_user_inf);

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientSocialHistory']['social_history_id'];

                        foreach ($ids as $id) {
                            $this->PatientSocialHistory->saveAudit('Delete');
                            $this->PatientSocialHistory->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $this->paginate['PatientSocialHistory'] = array(
                        'conditions' => array('PatientSocialHistory.patient_id' => $patient_id),
                        'order' => array('PatientSocialHistory.modified_timestamp' => 'DESC')
                    );
                    if (isset($this->params['named']['sort']) && $this->params['named']['sort'] == 'status') {
                        $this->PatientSocialHistory->virtualFields['status'] = "(
										CASE 1
										WHEN TYPE = 'Marital Status' THEN marital_status
										WHEN TYPE = 'Occupation' THEN occupation
										WHEN TYPE = 'Living Arrangement' THEN living_arrangement
										WHEN TYPE = 'Activities' THEN routine_status
										WHEN TYPE = 'Pets' THEN replace(replace(pets,'|',', '), ', ','')
										WHEN consumption_status != '' THEN consumption_status
										ELSE smoking_status
										END
                  )";
                    }
                    $PatientSocialHistory = $this->sanitizeHTML($this->paginate('PatientSocialHistory'));
                    $this->set('PatientSocialHistory', $PatientSocialHistory);
                    $this->PatientSocialHistory->saveAudit('View');
                }
        }

        $this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
        $this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

        $this->loadModel("PatientDemographic");
        $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $PatientDemographic['gender']);
    }

    public function hx_family() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientFamilyHistory");
        $this->loadModel("Icd");
        $this->Icd->setVersion();

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        //$this->set('PatientFamilyHistory', $this->sanitizeHTML($this->PatientFamilyHistory->find('all')));

        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            $this->data['PatientFamilyHistory']['patient_id'] = $patient_id;
            $this->data['PatientFamilyHistory']['encounter_id'] = 0;
            $this->data['PatientFamilyHistory']['diagnosis'] = $this->data['PatientFamilyHistory']['diagnosis'];
            $this->data['PatientFamilyHistory']['modified_user_id'] = $this->user_id;
            $this->data['PatientFamilyHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
        }
        switch ($task) {
            case "load_snomed": {
                    if (!empty($this->data)) {
                        $this->loadModel('SnomedIcd10Map');
                        $ret = array();
                        $ret['list'] = $this->SnomedIcd10Map->getSnomedCT($this->data['icd']);
                        echo json_encode($ret);
                    }
                    exit();
                } break;
            case "load_Icd9_autocomplete": {
                    if (!empty($this->data)) {
                        $this->Icd->execute($this, $task);
                    }
                    exit();
                } break;

            case "load_relationship": {
                    $showall = (isset($this->params['named']['showall'])) ? $this->params['named']['showall'] : "";

                    $search_keyword = $this->data['autocomplete']['keyword'];
                    $data_array = array('Mother', 'Father', 'Maternal Grandmother', 'Maternal Grandfather', 'Paternal Grandmother', 'Paternal Grandfather', 'Sister', 'Brother', 'Aunt', 'Uncle', 'Cousin', 'Child');

                    if (empty($showall)) {
                        $matches = array();
                        foreach ($data_array as $data_array) {
                            if (stripos($data_array, $search_keyword) !== false) {
                                $matches[] = $data_array;
                            }
                        }

                        $matches = array_slice($matches, 0, 5);
                        echo implode("\n", $matches);
                    } else {
                        echo json_encode($data_array);
                    }
                    exit();
                }
                break;

            case "load_problem": {
                    $search_keyword = $this->data['autocomplete']['keyword'];
                    $data_array = array('Asthma', 'Back Problems', 'Cancer', 'Child Birth', 'Diabetes Type II', 'Heart Disease', 'Hypertension', 'Mental Disorders', 'Osteoarthritis', 'Trauma Disorders');


                    $matches = array();
                    foreach ($data_array as $data_array) {
                        if (stripos($data_array, $search_keyword) !== false) {
                            $matches[] = $data_array;
                        }
                    }

                    $matches = array_slice($matches, 0, 5);
                    echo implode("\n", $matches);
                    exit();
                }
                break;

            case "addnew": {
                    if (!empty($this->data)) {
                        $this->PatientFamilyHistory->create();
                        $this->PatientFamilyHistory->saveAudit('New');
                        $this->PatientFamilyHistory->save($this->data);


                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                    $this->loadModel("FavoriteMedical");
                    $favitems = $this->FavoriteMedical->find('all', array(
                        'conditions' => array('FavoriteMedical.user_id' => $_SESSION['UserAccount']['user_id']),
                        'order' => array(
                            'FavoriteMedical.diagnosis ASC',
                        ),
                            ));
                    $this->set('favitems', $this->sanitizeHTML($favitems));
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->PatientFamilyHistory->saveAudit('Update');
                        $this->PatientFamilyHistory->save($this->data);


                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $family_history_id = (isset($this->params['named']['family_history_id'])) ? $this->params['named']['family_history_id'] : "";
                        //echo $family_history_id;
                        $items = $this->PatientFamilyHistory->find(
                                'first', array(
                            'conditions' => array('PatientFamilyHistory.family_history_id' => $family_history_id)
                                )
                        );

                        $last_user_inf = array(
                            'role_id' => '',
                            'full_name' => '(User no longer in the system)',
                        );

// who was last person to edit this record?			
                        $last_user = $this->UserAccount->getUserByID($items['PatientFamilyHistory']['modified_user_id']);

                        if ($last_user) {
                            $last_user_inf['role_id'] = $last_user->role_id;
                            $last_user_inf['full_name'] = $last_user->full_name;
                        }

                        $this->set('last_user', $last_user_inf);

                        $this->set('EditItem', $this->sanitizeHTML($items));

                        $this->loadModel("FavoriteMedical");
                        $favitems = $this->FavoriteMedical->find('all', array(
                            'conditions' => array('FavoriteMedical.user_id' => $_SESSION['UserAccount']['user_id']),
                            'order' => array(
                                'FavoriteMedical.diagnosis ASC',
                            ),
                                ));
                        $this->set('favitems', $this->sanitizeHTML($favitems));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientFamilyHistory']['family_history_id'];

                        foreach ($ids as $id) {
                            $this->PatientFamilyHistory->saveAudit('Delete');
                            $this->PatientFamilyHistory->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $this->paginate['PatientFamilyHistory'] = array(
                        'conditions' => array('PatientFamilyHistory.patient_id' => $patient_id),
                        'order' => array('PatientFamilyHistory.modified_timestamp' => 'DESC')
                    );

                    $this->set('PatientFamilyHistory', $this->sanitizeHTML($this->paginate('PatientFamilyHistory')));
                    //$this->set('PatientFamilyHistory', $this->sanitizeHTML($this->paginate('PatientFamilyHistory', array('patient_id' => $patient_id))));

                    $this->PatientFamilyHistory->saveAudit('View');
                }
        }

        $this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
        $this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

        $this->loadModel("PatientDemographic");
        $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $PatientDemographic['gender']);
    }

    public function hx_obgyn() {
        $this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
        $this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientObGynHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        //$this->set('PatientObGynHistory', $this->sanitizeHTML($this->PatientObGynHistory->find('all')));

        $this->loadModel('PatientDemographic');
        $patient_data = $this->PatientDemographic->getPatient($patient_id);
        $this->set(compact('patient_data'));

        if ($task == "addnew" || $task == "edit") {
            $this->loadModel("PracticeProfile");
            $PracticeProfile = $this->PracticeProfile->find('first');
            $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);

            $this->loadModel("PatientDemographic");
            $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
            $this->set('gender', $PatientDemographic['gender']);
        }
        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            if ($this->data['PatientObGynHistory']['type'] == "Gynecologic History") {
                if (!isset($this->data['PatientObGynHistory']['abnormal_pap_smear']) or @$this->data['PatientObGynHistory']['abnormal_pap_smear'] != 'Yes') {
                    $this->data['PatientObGynHistory']['abnormal_pap_smear_date'] = "";
                }
                if (!isset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding']) or @$this->data['PatientObGynHistory']['abnormal_irregular_bleeding'] != 'Yes') {
                    $this->data['PatientObGynHistory']['abnormal_irregular_bleeding_date'] = "";
                }
                if (!isset($this->data['PatientObGynHistory']['endometriosis']) or @$this->data['PatientObGynHistory']['endometriosis'] != 'Yes') {
                    $this->data['PatientObGynHistory']['endometriosis_date'] = "";
                    $this->data['PatientObGynHistory']['endometriosis_text'] = "";
                }
                if (!isset($this->data['PatientObGynHistory']['sexually_transmitted_disease']) or @$this->data['PatientObGynHistory']['sexually_transmitted_disease'] != 'Yes') {
                    $this->data['PatientObGynHistory']['sexually_transmitted_disease_date'] = "";
                    $this->data['PatientObGynHistory']['sexually_transmitted_disease_text'] = "";
                }
                if (!isset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease']) or @$this->data['PatientObGynHistory']['pelvic_inflammatory_disease'] != 'Yes') {
                    $this->data['PatientObGynHistory']['pelvic_inflammatory_disease_date'] = "";
                    $this->data['PatientObGynHistory']['pelvic_inflammatory_disease_text'] = "";
                }
                unset($this->data['PatientObGynHistory']['age_started_period']);
                unset($this->data['PatientObGynHistory']['last_menstrual_period']);
                unset($this->data['PatientObGynHistory']['how_often']);
                unset($this->data['PatientObGynHistory']['how_long']);
                unset($this->data['PatientObGynHistory']['birth_control_method']);
                unset($this->data['PatientObGynHistory']['menopause']);
                unset($this->data['PatientObGynHistory']['menopause_text']);
                unset($this->data['PatientObGynHistory']['total_of_pregnancies']);
                unset($this->data['PatientObGynHistory']['number_of_full_term']);
                unset($this->data['PatientObGynHistory']['number_of_premature']);
                unset($this->data['PatientObGynHistory']['number_of_miscarriages']);
                unset($this->data['PatientObGynHistory']['number_of_abortions']);
                unset($this->data['PatientObGynHistory']['type_of_delivery']);
                unset($this->data['PatientObGynHistory']['delivery_weight']);
                unset($this->data['PatientObGynHistory']['delivery_date']);
            } else if ($this->data['PatientObGynHistory']['type'] == "Menstrual History") {
                unset($this->data['PatientObGynHistory']['abnormal_pap_smear']);
                unset($this->data['PatientObGynHistory']['abnormal_pap_smear_date']);
                unset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding']);
                unset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding_date']);
                unset($this->data['PatientObGynHistory']['endometriosis']);
                unset($this->data['PatientObGynHistory']['endometriosis_date']);
                unset($this->data['PatientObGynHistory']['endometriosis_text']);
                unset($this->data['PatientObGynHistory']['sexually_transmitted_disease']);
                unset($this->data['PatientObGynHistory']['sexually_transmitted_disease_date']);
                unset($this->data['PatientObGynHistory']['sexually_transmitted_disease_text']);
                unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease']);
                unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease_date']);
                unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease_text']);
                if (!isset($this->data['PatientObGynHistory']['menopause']) or @$this->data['PatientObGynHistory']['menopause'] != 'Yes') {
                    $this->data['PatientObGynHistory']['menopause_text'] = "";
                }
                unset($this->data['PatientObGynHistory']['total_of_pregnancies']);
                unset($this->data['PatientObGynHistory']['number_of_full_term']);
                unset($this->data['PatientObGynHistory']['number_of_premature']);
                unset($this->data['PatientObGynHistory']['number_of_miscarriages']);
                unset($this->data['PatientObGynHistory']['number_of_abortions']);
                unset($this->data['PatientObGynHistory']['type_of_delivery']);
                unset($this->data['PatientObGynHistory']['delivery_weight']);
                unset($this->data['PatientObGynHistory']['delivery_date']);
                $this->data['PatientObGynHistory']['last_menstrual_period'] = $this->data['PatientObGynHistory']['last_menstrual_period'] ? __date("Y-m-d", strtotime($this->data['PatientObGynHistory']['last_menstrual_period'])) : '';
            } else if ($this->data['PatientObGynHistory']['type'] == "Pregnancy History") {
                unset($this->data['PatientObGynHistory']['abnormal_pap_smear']);
                unset($this->data['PatientObGynHistory']['abnormal_pap_smear_date']);
                unset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding']);
                unset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding_date']);
                unset($this->data['PatientObGynHistory']['endometriosis']);
                unset($this->data['PatientObGynHistory']['endometriosis_date']);
                unset($this->data['PatientObGynHistory']['endometriosis_text']);
                unset($this->data['PatientObGynHistory']['sexually_transmitted_disease']);
                unset($this->data['PatientObGynHistory']['sexually_transmitted_disease_date']);
                unset($this->data['PatientObGynHistory']['sexually_transmitted_disease_text']);
                unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease']);
                unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease_date']);
                unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease_text']);
                unset($this->data['PatientObGynHistory']['age_started_period']);
                unset($this->data['PatientObGynHistory']['last_menstrual_period']);
                unset($this->data['PatientObGynHistory']['how_often']);
                unset($this->data['PatientObGynHistory']['how_long']);
                unset($this->data['PatientObGynHistory']['birth_control_method']);
                unset($this->data['PatientObGynHistory']['menopause']);
                unset($this->data['PatientObGynHistory']['menopause_text']);


                if (isset($this->data['PatientObGynHistory']['type_of_delivery'])) {
                    $length = count($this->data['PatientObGynHistory']['type_of_delivery']);

                    $deliveries = array();
                    for ($ct = 0; $ct < $length; $ct++) {

                        $type = trim($this->data['PatientObGynHistory']['type_of_delivery'][$ct]);
                        $weight = trim($this->data['PatientObGynHistory']['delivery_weight'][$ct]);
                        // added ounces
                        $ounces = trim($this->data['PatientObGynHistory']['delivery_weight_ounce'][$ct]);
                        $date = trim($this->data['PatientObGynHistory']['delivery_date'][$ct]);
                        $date = __date('Y-m-d', strtotime($date));

                        if ($type) {
                            $deliveries[] = array(
                                'type' => $type,
                                'weight' => $weight,
                                'ounces' => $ounces,
                                'date' => $date,
                            );
                        }
                    }

                    $this->data['PatientObGynHistory']['deliveries'] = json_encode($deliveries);

                    unset($this->data['PatientObGynHistory']['type_of_delivery']);
                    unset($this->data['PatientObGynHistory']['delivery_weight']);
                    // unset ounces
                    unset($this->data['PatientObGynHistory']['delivery_weight_ounce']);
                    unset($this->data['PatientObGynHistory']['delivery_date']);
                }
            }
            $this->data['PatientObGynHistory']['patient_id'] = $patient_id;
            $this->data['PatientObGynHistory']['encounter_id'] = 0;
            $this->data['PatientObGynHistory']['modified_user_id'] = $this->user_id;
            $this->data['PatientObGynHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
        }
        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->PatientObGynHistory->create();
                        $this->PatientObGynHistory->saveAudit('New');
                        $this->PatientObGynHistory->save($this->data);


                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->PatientObGynHistory->saveAudit('Update');
                        $this->PatientObGynHistory->save($this->data);


                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $ob_gyn_history_id = (isset($this->params['named']['ob_gyn_history_id'])) ? $this->params['named']['ob_gyn_history_id'] : "";
                        //echo $ob_gyn_history_id;
                        $items = $this->PatientObGynHistory->find(
                                'first', array(
                            'conditions' => array('PatientObGynHistory.ob_gyn_history_id' => $ob_gyn_history_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                        $this->set('rawItem', $items);
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientObGynHistory']['ob_gyn_history_id'];

                        foreach ($ids as $id) {
                            $this->PatientObGynHistory->saveAudit('Delete');
                            $this->PatientObGynHistory->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $this->paginate['PatientObGynHistory'] = array(
                        'conditions' => array('PatientObGynHistory.patient_id' => $patient_id),
                        'order' => array('PatientObGynHistory.modified_timestamp' => 'DESC')
                    );

                    $this->set('PatientObGynHistory', $this->sanitizeHTML($this->paginate('PatientObGynHistory')));
                    //$this->set('PatientObGynHistory', $this->sanitizeHTML($this->paginate('PatientObGynHistory', array('patient_id' => $patient_id))));

                    $this->PatientObGynHistory->saveAudit('View');


                    $this->loadModel("PatientDemographic");
                    $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
                    $this->set('gender', $PatientDemographic['gender']);
                }
        }
    }

    public function conservative_therapy() {
        $this->layout = "blank";
        $this->loadModel("PatientConservativeTherapy");
        $this->loadModel("PatientDemographic");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $encounter_id = 0;

        $this->set('encounter_id', $encounter_id);


        $patient = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id)));
        $this->PatientConservativeTherapy->execute($this, $task, $encounter_id, $patient_id);

        $this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);

        $obgyn_feature_include_flag = intval($PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);
        $this->set('obgyn_feature_include_flag', $obgyn_feature_include_flag);


        $this->set('gender', $patient['PatientDemographic']['gender']);
    }

    public function allergies() {
        $this->loadModel("PatientDemographic");
        $this->layout = "blank";
        $practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup = $practice_settings['PracticeSetting']['labs_setup'];
        $rx_setup = $practice_settings['PracticeSetting']['rx_setup'];
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientAllergy");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        //$this->set('PatientAllergy', $this->sanitizeHTML($this->PatientAllergy->find('all')));

        $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
        $dosespot_xml_api = new Dosespot_XML_API();
        $emdeon_xml_api = new Emdeon_XML_API();
        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            $this->data['PatientAllergy']['patient_id'] = $patient_id;
            $this->data['PatientAllergy']['encounter_id'] = 0;
            $this->data['PatientAllergy']['modified_user_id'] = $this->user_id;
            $this->data['PatientAllergy']['modified_timestamp'] = __date("Y-m-d H:i:s");
            for ($i = $this->data['PatientAllergy']['reaction_count'] + 1; $i <= 10; $i++) {
                $this->data['PatientAllergy']['reaction' . $i] = "";
                $this->data['PatientAllergy']['severity' . $i] = "";
            }
        }
        if ($task == "addnew" || $task == "edit") {
            $patient_mrn = $this->PatientDemographic->getPatient($patient_id);
            $mrn = $patient_mrn['mrn'];
            $this->set('mrn', $this->sanitizeHTML($mrn));
        }
        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        if ($practice_settings['PracticeSetting']['rx_setup'] == 'Electronic_Emdeon') { // Emdeon patient allergies 
                            $this->data['PatientAllergy']['rxnorm'] = $this->PatientAllergy->saveAllergy($this->data);
                        }

                        if (!empty($dosespot_patient_id)) {
                            //Add Allergy data to Dosespot 
                            if (!isset($this->data['PatientAllergy']['allergy_code'])) {
                                $this->data['PatientAllergy']['allergy_code'] = '3';
                            }

                            if (!isset($this->data['PatientAllergy']['allergy_code_type'])) {
                                $this->data['PatientAllergy']['allergy_code_type'] = 'AllergyClass';
                            }

                            $added_allergy_item = $dosespot_xml_api->executeAddAllergy($dosespot_patient_id, $this->data['PatientAllergy']);
                        }

                        $this->data['PatientAllergy']['dosespot_allergy_id'] = isset($added_allergy_item['PatientAllergyID']) ? ($added_allergy_item['PatientAllergyID']) : 0;

                        $this->PatientAllergy->saveAudit('New');
                        $this->PatientAllergy->save($this->data);

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        if (($labs_setup == 'Electronic' || $rx_setup == 'Electronic_Emdeon') && $emdeon_xml_api->checkConnection()) {
                            $this->PatientAllergy->saveAudit('Update');
                            $this->PatientAllergy->saveAllergy($this->data);
                        } else {
                            $this->PatientAllergy->saveAudit('Update');
                            $this->PatientAllergy->save($this->data);
                        }

                        $PracticeSetting = $this->Session->read("PracticeSetting");
                        if (!empty($this->data['PatientAllergy']['dosespot_allergy_id']) and $PracticeSetting['PracticeSetting']['rx_setup'] == 'Electronic_Dosespot') {
                            $dosespot_xml_api = new Dosespot_XML_API();
                            $dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $this->data['PatientAllergy']);
                        }

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $allergy_id = (isset($this->params['named']['allergy_id'])) ? $this->params['named']['allergy_id'] : "";
                        //echo $family_history_id;
                        $items = $this->PatientAllergy->find(
                                'first', array(
                            'conditions' => array('PatientAllergy.allergy_id' => $allergy_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;

            case "import_allery_from_surescripts": {
                    $PracticeSetting = $this->Session->read("PracticeSetting");
                    if ($PracticeSetting['PracticeSetting']['rx_setup'] == 'Electronic_Dosespot') {
                        //If the patient not exists in Dosespot, add the patient to Dosespot
                        if ($dosespot_patient_id == 0 or $dosespot_patient_id == '') {
                            $this->PatientDemographic->updateDosespotPatient($patient_id);
                            $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
                        }

                        $allergy_items = $dosespot_xml_api->getAllergyList($dosespot_patient_id);

                        foreach ($allergy_items as $allergy_item) {
                            $dosespot_allergy_id = $allergy_item['PatientAllergyId'];
                            $items = $this->PatientAllergy->find('first', array('conditions' => array('PatientAllergy.dosespot_allergy_id' => $dosespot_allergy_id)));

                            if (empty($items)) {
                                $this->data = array();
                                $this->data['PatientAllergy']['patient_id'] = $patient_id;
                                $this->data['PatientAllergy']['dosespot_allergy_id'] = $dosespot_allergy_id;
                                $this->data['PatientAllergy']['agent'] = $allergy_item['agent'];
                                $this->data['PatientAllergy']['reaction_count'] = 1;
                                $this->data['PatientAllergy']['reaction1'] = $allergy_item['reaction1'] ? $allergy_item['reaction1'] : '';
                                $this->data['PatientAllergy']['status'] = $allergy_item['status'];
                                $this->data['PatientAllergy']['modified_user_id'] = $this->user_id;
                                $this->data['PatientAllergy']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                $this->PatientAllergy->create();
                                $this->PatientAllergy->saveAudit('New');
                                $this->PatientAllergy->save($this->data);
                            } else {
                                $this->data['PatientAllergy']['allergy_id'] = $items['PatientAllergy']['allergy_id'];
                                $this->data['PatientAllergy']['patient_id'] = $patient_id;
                                $this->data['PatientAllergy']['dosespot_allergy_id'] = $dosespot_allergy_id;
                                $this->data['PatientAllergy']['agent'] = $allergy_item['agent'];
                                $this->data['PatientAllergy']['reaction_count'] = 1;
                                $this->data['PatientAllergy']['reaction1'] = $allergy_item['reaction1'] ? $allergy_item['reaction1'] : '';
                                $this->data['PatientAllergy']['status'] = $allergy_item['status'];
                                $this->data['PatientAllergy']['modified_user_id'] = $this->user_id;
                                $this->data['PatientAllergy']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                $this->PatientAllergy->saveAudit('Update');
                                $this->PatientAllergy->save($this->data);
                            }
                        }
                    } //close if dosespot enabled
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientAllergy']['allergy_id'];

                        foreach ($ids as $id) {

                            if (($labs_setup == 'Electronic' || $rx_setup == 'Electronic_Emdeon') && $emdeon_xml_api->checkConnection()) {
                                $this->PatientAllergy->saveAudit('Delete');
                                $this->PatientAllergy->deleteAllergy($id);
                            } else {
                                $id_array = explode('|', $id);
                                $allergy_id = $id_array[0];
                                $dosespot_allergy_id = $id_array[1];
                                $this->PatientAllergy->saveAudit('Delete');
                                $this->PatientAllergy->delete($allergy_id, false);
                            }
                            $PracticeSetting = $this->Session->read("PracticeSetting");
                            //Delete allergy in Dosespot
                            if (!empty($dosespot_allergy_id) and $PracticeSetting['PracticeSetting']['rx_setup'] == 'Electronic_Dosespot') {
                                $this->data['PatientAllergy']['dosespot_allergy_id'] = $dosespot_allergy_id;
                                $this->data['PatientAllergy']['reaction1'] = '';
                                $this->data['PatientAllergy']['status'] = 'Deleted';
                                $dosespot_xml_api = new Dosespot_XML_API();
                                $dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $this->data['PatientAllergy']);
                            }


                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            case "markNone": {
                    if (!empty($this->data)) {
                        $this->data['PatientDemographic']['patient_id'] = $patient_id;
                        $this->data['PatientDemographic']['allergies_none'] = $this->data['submitted']['value'];
                        $this->PatientDemographic->save($this->data);
                    }
                    exit;
                } break;
            case "update_status": {
                    $this->PatientAllergy->setItemValue("status", $this->data['submitted']['value'], $this->data['allergy_id'], $patient_id, $this->user_id);
                    echo $this->data['submitted']['value'];
                    exit;
                }break;
            default: {
                    $demographic_items = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
                    $practice_settings = $this->Session->read("PracticeSetting");
                    $rx_setup = $practice_settings['PracticeSetting']['rx_setup'];
                    if ($rx_setup == 'Electronic_Dosespot') {
                        $dosespot_patient_id = $demographic_items['PatientDemographic']['dosespot_patient_id'];
                        //If the patient not exists in Dosespot, add the patient to Dosespot
                        if (empty($dosespot_patient_id)) {
                            $this->PatientDemographic->updateDosespotPatient($patient_id);
                        }
                    }

                    $allergies_none = $demographic_items['PatientDemographic']['allergies_none'];
                    $this->set('allergies_none', $allergies_none);

                    $show_all_allergies = (isset($this->params['named']['show_all_allergies'])) ? $this->params['named']['show_all_allergies'] : "yes";
                    $show_history = (isset($this->params['named']['show_history'])) ? $this->params['named']['show_history'] : "yes";
                    $show_patient_reported = (isset($this->params['named']['show_patient_reported'])) ? $this->params['named']['show_patient_reported'] : "yes";
                    $show_practice_reported = (isset($this->params['named']['show_practice_reported'])) ? $this->params['named']['show_practice_reported'] : "yes";
                    $show_transition_referral = (isset($this->params['named']['show_transition_referral'])) ? $this->params['named']['show_transition_referral'] : "yes";
                    $this->set(compact('show_all_allergies', 'show_history', 'show_patient_reported', 'show_practice_reported', 'show_transition_referral'));

                    $conditions = array();
                    $conditions['PatientAllergy.patient_id'] = $patient_id;

                    if ($show_all_allergies == 'no') {
                        $conditions['PatientAllergy.status'] = 'Active';
                    }

                    $sources_array = array();

                    if ($show_history == 'no') {
                        $sources_array[] = 'Allergy History';
                    }

                    if ($show_patient_reported == 'no') {
                        $sources_array[] = 'Patient Reported';
                    }

                    if ($show_practice_reported == 'no') {
                        $sources_array[] = 'Practice Reported';
                    }

                    if ($show_transition_referral == 'no') {
                        $sources_array[] = 'Transition of Care/Referral';
                    }

                    if (count($sources_array) > 0) {
                        $sources_array[] = '';

                        $conditions['PatientAllergy.source NOT '] = $sources_array;
                    }

                    $this->paginate['PatientAllergy'] = array(
                        'conditions' => $conditions,
                        'order' => array('PatientAllergy.modified_timestamp' => 'DESC')
                    );

                    $this->set('PatientAllergy', $this->sanitizeHTML($this->paginate('PatientAllergy')));

                    //$this->set('PatientAllergy', $this->sanitizeHTML($this->paginate('PatientAllergy', $conditions)));

                    $this->PatientAllergy->saveAudit('View');
                }
        }
    }

    public function problem_list() {
        $this->layout = "blank";
        $this->loadModel("PatientProblemList");
        $this->loadModel("PatientDemographic");
        $this->loadModel("PatientMedicalHistory");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $show_all_problems = (isset($this->params['named']['show_all_problems'])) ? $this->params['named']['show_all_problems'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        //$this->set('PatientProblemList', $this->sanitizeHTML($this->PatientProblemList->find('all')));
        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            $this->data['PatientProblemList']['patient_id'] = $patient_id;
            $this->data['PatientProblemList']['encounter_id'] = 0;
            $this->data['PatientProblemList']['diagnosis'] = $this->data['PatientProblemList']['diagnosis'];
            $this->data['PatientProblemList']['start_date'] = $this->data['PatientProblemList']['start_date'] ? __date("Y-m-d", strtotime($this->data['PatientProblemList']['start_date'])) : '';
            $this->data['PatientProblemList']['end_date'] = $this->data['PatientProblemList']['end_date'] ? __date("Y-m-d", strtotime($this->data['PatientProblemList']['end_date'])) : '';
            $this->data['PatientProblemList']['occurrence'] = $this->data['PatientProblemList']['occurrence'];
            $this->data['PatientProblemList']['comment'] = $this->data['PatientProblemList']['comment'];
            $this->data['PatientProblemList']['source'] = isset($this->data['PatientProblemList']['source']) ? $this->data['PatientProblemList']['source'] : '';
            $this->data['PatientProblemList']['status'] = isset($this->data['PatientProblemList']['status']) ? $this->data['PatientProblemList']['status'] : '';
            $this->data['PatientProblemList']['action'] = isset($this->data['PatientProblemList']['action']) ? 'Moved' : '';
            $this->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['PatientProblemList']['modified_user_id'] = $this->user_id;
        }

        switch ($task) {
            case "load_snomed_autocomplete": {
                    if (!empty($this->data)) {
                        $this->loadModel('SnomedIcd10Map');
                        $this->SnomedIcd10Map->execute($this, $task);
                    }
                    exit();
                } break;
            case "load_Icd9_autocomplete": {
                    if (!empty($this->data)) {
                        $this->Icd->execute($this, $task);
                    }
                    exit();
                } break;
            case "addnew": {
                    if (!empty($this->data)) {
                        if ($this->data['PatientProblemList']['action'] == 'Moved') {
                            $this->data['PatientMedicalHistory']['patient_id'] = $patient_id;
                            $this->data['PatientMedicalHistory']['diagnosis'] = $this->data['PatientProblemList']['diagnosis'];

                            if ($this->data['PatientProblemList']['start_date'] != '') {
                                $splitted_start_date = explode('-', $this->data['PatientProblemList']['start_date']);
                                if ($this->__global_date_format == 'Y-m-d') {
                                    list($start_day, $start_month, $start_year) = $splitted_start_date;
                                } elseif ($this->__global_date_format == 'd-m-Y') {
                                    list($start_year, $start_month, $start_day) = $splitted_start_date;
                                } else {
                                    list($start_year, $start_day, $start_month) = $splitted_start_date;
                                }
                                $this->data['PatientMedicalHistory']['start_month'] = $start_month ? $start_month : '';
                                $this->data['PatientMedicalHistory']['start_year'] = $start_year ? $start_year : '';
                            }
                            if ($this->data['PatientProblemList']['end_date'] != '') {
                                $splitted_end_date = explode('-', $this->data['PatientProblemList']['end_date']);
                                if ($this->__global_date_format == 'Y-m-d') {
                                    list($end_day, $end_month, $end_year) = $splitted_end_date;
                                } elseif ($this->__global_date_format == 'd-m-Y') {
                                    list($end_year, $end_month, $end_day) = $splitted_end_date;
                                } else {
                                    list($end_year, $end_day, $end_month) = $splitted_end_date;
                                }
                                $this->data['PatientMedicalHistory']['end_month'] = $end_month ? $end_month : '';
                                $this->data['PatientMedicalHistory']['end_year'] = $end_year ? $end_year : '';
                            }
                            $this->data['PatientMedicalHistory']['occurrence'] = $this->data['PatientProblemList']['occurrence'];
                            $this->data['PatientMedicalHistory']['comment'] = $this->data['PatientProblemList']['comment'];
                            $this->data['PatientMedicalHistory']['source'] = $this->data['PatientProblemList']['source'];
                            $this->data['PatientMedicalHistory']['status'] = isset($this->data['PatientProblemList']['status']) ? $this->data['PatientProblemList']['status'] : '';
                            $this->data['PatientMedicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
                            $this->data['PatientMedicalHistory']['modified_user_id'] = $this->user_id;
                            $this->PatientMedicalHistory->create();
                            $this->PatientMedicalHistory->saveAudit('New');
                            $this->PatientMedicalHistory->save($this->data);
                        } else {
                            $this->PatientProblemList->create();
                            $this->PatientProblemList->saveAudit('New');
                            $this->PatientProblemList->save($this->data);
                        }
                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        if ($this->data['PatientProblemList']['action'] == 'Moved') {
                            $this->data['PatientMedicalHistory']['patient_id'] = $patient_id;
                            $this->data['PatientMedicalHistory']['diagnosis'] = $this->data['PatientProblemList']['diagnosis'];
                            $this->data['PatientMedicalHistory']['icd_code'] = $this->data['PatientProblemList']['icd_code'];
                            /* $this->data['PatientMedicalHistory']['start_date'] = $this->data['PatientProblemList']['start_date']?__date("Y-m-d", strtotime($this->data['PatientProblemList']['start_date'])):'';
                              $this->data['PatientMedicalHistory']['end_date'] = $this->data['PatientProblemList']['end_date']?__date("Y-m-d", strtotime($this->data['PatientProblemList']['end_date'])):''; */
                            if ($this->data['PatientProblemList']['start_date'] != '') {
                                $splitted_start_date = explode('-', $this->data['PatientProblemList']['start_date']);
                                if ($this->__global_date_format == 'Y-m-d') {
                                    list($start_day, $start_month, $start_year) = $splitted_start_date;
                                } elseif ($this->__global_date_format == 'd-m-Y') {
                                    list($start_year, $start_month, $start_day) = $splitted_start_date;
                                } else {
                                    list($start_year, $start_day, $start_month) = $splitted_start_date;
                                }
                                $this->data['PatientMedicalHistory']['start_month'] = $start_month ? $start_month : '';
                                $this->data['PatientMedicalHistory']['start_year'] = $start_year ? $start_year : '';
                            }
                            if ($this->data['PatientProblemList']['end_date'] != '') {
                                $splitted_end_date = explode('-', $this->data['PatientProblemList']['end_date']);
                                if ($this->__global_date_format == 'Y-m-d') {
                                    list($end_day, $end_month, $end_year) = $splitted_end_date;
                                } elseif ($this->__global_date_format == 'd-m-Y') {
                                    list($end_year, $end_month, $end_day) = $splitted_end_date;
                                } else {
                                    list($end_year, $end_day, $end_month) = $splitted_end_date;
                                }
                                $this->data['PatientMedicalHistory']['end_month'] = $end_month ? $end_month : '';
                                $this->data['PatientMedicalHistory']['end_year'] = $end_year ? $end_year : '';
                            }
                            $this->data['PatientMedicalHistory']['occurrence'] = $this->data['PatientProblemList']['occurrence'];
                            $this->data['PatientMedicalHistory']['comment'] = $this->data['PatientProblemList']['comment'];
                            $this->data['PatientMedicalHistory']['source'] = $this->data['PatientProblemList']['source'];
                            $this->data['PatientMedicalHistory']['status'] = isset($this->data['PatientProblemList']['status']) ? $this->data['PatientProblemList']['status'] : '';
                            $this->data['PatientMedicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
                            $this->data['PatientMedicalHistory']['modified_user_id'] = $this->user_id;
                            $this->PatientMedicalHistory->create();
                            $this->PatientMedicalHistory->saveAudit('New');
                            $this->PatientMedicalHistory->save($this->data);


                            //Delete from Problem List
                            $problem_list_id = (isset($this->params['named']['problem_list_id'])) ? $this->params['named']['problem_list_id'] : "";
                            $this->PatientProblemList->saveAudit('Delete');
                            $this->PatientProblemList->delete($problem_list_id, false);
                        } else {
                            $this->PatientProblemList->saveAudit('Update');
                            $this->PatientProblemList->save($this->data);
                        }
                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $problem_list_id = (isset($this->params['named']['problem_list_id'])) ? $this->params['named']['problem_list_id'] : "";
                        $items = $this->PatientProblemList->find(
                                'first', array(
                            'conditions' => array('PatientProblemList.problem_list_id' => $problem_list_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;

            case "markNone": {
                    if (!empty($this->data)) {
                        $this->data['PatientDemographic']['patient_id'] = $patient_id;
                        $this->data['PatientDemographic']['problem_list_none'] = $this->data['submitted']['value'];
                        $this->PatientDemographic->save($this->data);
                    }
                    exit;
                } break;

            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientProblemList']['problem_list_id'];

                        foreach ($ids as $id) {
                            $this->PatientProblemList->saveAudit('Delete');
                            $this->PatientProblemList->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }break;
            default: {
                    $demographic_items = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));

                    $problem_list_none = $demographic_items['PatientDemographic']['problem_list_none'];
                    $this->set('problem_list_none', $problem_list_none);

                    if ($show_all_problems == 'yes') {
                        $this->set('show_all_problems', 'yes');

                        $this->paginate['PatientProblemList'] = array(
                            'conditions' => array('PatientProblemList.patient_id' => $patient_id),
                            'order' => array('PatientProblemList.modified_timestamp' => 'DESC')
                        );

                        $this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList')));
                        //$this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList', array('patient_id' => $patient_id))));
                    } elseif ($show_all_problems == 'no') {
                        $this->set('show_all_problems', 'no');

                        $this->paginate['PatientProblemList'] = array(
                            'conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.status' => 'Active'),
                            'order' => array('PatientProblemList.modified_timestamp' => 'DESC')
                        );

                        $this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList')));

                        //$this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList', array('patient_id' => $patient_id, 'status'=>'Active'))));
                    } else {
                        $this->set('show_all_problems', 'yes');

                        $this->paginate['PatientProblemList'] = array(
                            'conditions' => array('PatientProblemList.patient_id' => $patient_id),
                            'order' => array('PatientProblemList.modified_timestamp' => 'DESC')
                        );

                        $this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList')));

                        //$this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList', array('patient_id' => $patient_id))));
                    }

                    $this->PatientProblemList->saveAudit('View');
                }
        }
    }

    public function medication_list_refill() {
        $this->layout = "blank";
        $this->loadModel("PatientMedicationRefill");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        $role_id = $_SESSION['UserAccount']['role_id'];
        $this->set("role_id", $role_id);

        $this->loadModel("UserAccount");
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);

        switch ($task) {
            case "edit": {
                    if (!empty($this->data)) {
                        $this->PatientMedicationRefill->saveAudit('Update');
                        $this->PatientMedicationRefill->save($this->data);
                        $this->PatientMedicationRefill->PatientMedicationList->approveRefill($this->data['PatientMedicationRefill']['refill_id']);

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $refill_id = (isset($this->params['named']['refill_id'])) ? $this->params['named']['refill_id'] : "";
                        $item = $this->PatientMedicationRefill->find('first', array('conditions' => array('PatientMedicationRefill.refill_id' => $refill_id)));

                        $this->set('EditItem', $this->sanitizeHTML($item));
                        $this->PatientMedicationRefill->saveAudit('View');
                    }
                }
                break;
            case "show_dosespot_refill": {
                    $dosespot_xml_api = new Dosespot_XML_API();
                    $this->set("dosespot_info", $dosespot_xml_api->getInfo());
                }break;
            default: {
                    $this->paginate['PatientMedicationRefill'] = array(
                        'order' => array('PatientMedicationRefill.refill_request_date' => 'desc', 'PatientMedicationRefill.refill_id' => 'desc')
                    );

                    $this->set('refills', $this->sanitizeHTML($this->paginate('PatientMedicationRefill', array('PatientMedicationRefill.patient_id' => $patient_id))));
                    $this->PatientMedicationRefill->saveAudit('View');
                }
        }
    }

    public function refill_summary() {
        $practice_settings = $this->Session->read("PracticeSetting");
        $rx_setup = $practice_settings['PracticeSetting']['rx_setup'];

        if ($rx_setup == 'Electronic_Emdeon') {
            $this->redirect(array('action' => 'emdeon_refill_summary'));
        }

        if ($rx_setup == 'Electronic_Dosespot') {
            $this->redirect(array('action' => 'dosespot_refill_summary'));
        }
    }

    public function refill_summary_grid() {
        $this->layout = "empty";
        $this->loadModel("PatientMedicationRefill");
        $this->loadModel("PatientDemographic");

        $this->PatientMedicationRefill->find('all');

        $this->PatientMedicationRefill->inheritVirtualFields('PatientDemographic', 'patientName');

        $patient_ids = array();
        $conditions = array();

        if (isset($this->data['patient_name'])) {
            if (strlen($this->data['patient_name']) > 0) {
                $this->PatientDemographic->recursive = -1;
                $patients = $this->PatientDemographic->find('all', array(
                    'conditions' => array('OR' => array('PatientDemographic.patient_search_name LIKE ' => '' . $this->data['patient_name'] . '%', 'PatientDemographic.first_name LIKE ' => '' . $this->data['patient_name'] . '%', 'PatientDemographic.last_name LIKE ' => '' . $this->data['patient_name'] . '%'))
                        ));

                if (count($patients) > 0) {
                    foreach ($patients as $patient) {
                        $patient_ids[] = $patient['PatientDemographic']['patient_id'];
                    }
                } else {
                    $patient_ids[] = '0';
                }

                if (count($patient_ids) > 0) {
                    $conditions['PatientMedicationRefill.patient_id'] = $patient_ids;
                }
            }
        }
        // $this->PatientMedicationRefill->find('all');
        //$this->PatientMedicationRefill->inheritVirtualFields('PatientDemographic', 'patientName');

        $this->paginate['PatientMedicationRefill'] = array(
            'conditions' => $conditions,
            'limit' => 20,
            'page' => 1,
            'order' => array('PatientMedicationRefill.refill_request_date' => 'desc', 'PatientMedicationRefill.refill_id' => 'desc')
        );

        $this->set('refills', $this->sanitizeHTML($this->paginate('PatientMedicationRefill')));
        //var_dump($this->paginate('PatientMedicationRefill'));
    }

    public function emdeon_refill_summary_print() {
        $this->layout = "blank";

        $practice_settings = $_SESSION['PracticeSetting'];

        $icd_version = intval($practice_settings['PracticeSetting']['icd_version']);
        $icd_var = 'icd_9_cm_code';

        if ($icd_version == 10) {
            $icd_var = 'icd_10_cm_code';
            $icd_version = 10;
        }

        $this->set('icd_var', $icd_var);
        $this->set('icd_version', $icd_version);

        $this->layout = "blank";
        $this->loadModel('EmdeonRefillRequest');

        $refill_request_id = (isset($this->params['named']['refill_request_id'])) ? $this->params['named']['refill_request_id'] : "";

        $rx = $this->EmdeonRefillRequest->find('first', array('conditions' => array('EmdeonRefillRequest.request_id' => $refill_request_id)));

        $rx_unique_id = $rx['EmdeonRefillRequest']['rx'];

        $emdeon_xml_api = new Emdeon_XML_API();

        //get prescriber details
        $prescriber = $emdeon_xml_api->getPrescriberDetails($rx['EmdeonRefillRequest']['prescriber']);

        //get supervising prescriber details
        if ($rx['EmdeonRefillRequest']['prescriber'] == $rx['EmdeonRefillRequest']['supervising_prescriber']) {
            $supervising_prescriber = $prescriber;
        } else {
            if (strlen($rx['EmdeonRefillRequest']['supervising_prescriber']) > 0) {
                $supervising_prescriber = $emdeon_xml_api->getCaregiverDetails($rx['EmdeonRefillRequest']['supervising_prescriber']);
            } else {
                $supervising_prescriber = false;
            }
        }

        //get patient details
        $this->loadModel('PatientDemographic');
        $patient = $this->PatientDemographic->getPatient($rx['EmdeonRefillRequest']['patient_id']);
        $patient['home_phone'] = $emdeon_xml_api->formatPhone($patient['home_phone']);

        //get emdeon configuration
        $api_configs = $emdeon_xml_api->getInfo();

        //get facility details
        $organization_details = $emdeon_xml_api->getOrganizationDetails();
        $organization_details['contact_phone'] = $emdeon_xml_api->formatPhone($organization_details['contact_phone']);

        $this->set(compact('rx_unique_id', 'rx', 'prescriber', 'supervising_prescriber', 'patient', 'api_configs', 'organization_details'));
    }

    public function emdeon_refill_summary() {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        $this->loadModel("EmdeonRefillRequest");

        switch ($task) {
            case "edit": {
                    if ($this->data['prescriber'] != '') {
                        $prescriber_info = explode('|', $this->data['prescriber']);
                        $this->data['prescriber'] = $prescriber_info[0];
                        $this->data['prescriber_name'] = $prescriber_info[1];
                    }

                    $emdeon_xml_api = new Emdeon_XML_API();
                    $emdeon_xml_api->refillRx($this->data['rx'], $this->data);

                    $this->loadModel('PatientMedicationList');

                    if (strtolower($this->data['action']) != 'deny') {
                        $this->PatientMedicationList->addEmdeonRefill($this->data['refill_request_id'], $this->data['patient_id']);
                    }

                    switch ($this->data['action_type']) {
                        case "print": {
                                $this->Session->setFlash('Rx has been authorized.');
                                $this->redirect(array('action' => 'emdeon_refill_summary', 'task' => 'print_refill_request', 'refill_request_id' => $this->data['refill_request_id'], 'auto_print' => 1));
                            } break;
                        default: {
                                $rx_result = ($this->data['action_type'] == 'deny') ? 'Denied' : 'Authorized';
                                $this->Session->setFlash('Rx has been ' . $rx_result);
                                $this->redirect(array('action' => 'emdeon_refill_summary'));
                            }
                    }
                } break;
            case "view_refill_request": {
                    $refill_request_id = (isset($this->params['named']['refill_request_id'])) ? $this->params['named']['refill_request_id'] : "";
                    $items = $this->EmdeonRefillRequest->find('first', array('conditions' => array('EmdeonRefillRequest.request_id' => $refill_request_id)));
                    $this->set('ViewItem', $this->sanitizeHTML($items));

                    $emdeon_xml_api = new Emdeon_XML_API;
                    $caregivers = $emdeon_xml_api->getCaregivers();
                    $this->set("caregivers", $caregivers);

                    $this->loadModel('AdministrationPrescriptionAuth');
                    $allowed = $this->AdministrationPrescriptionAuth->getAuthorizingUsers($_SESSION['UserAccount']['user_id']);
                    $this->set('prescriptionAuth', $allowed);

                    $user = $this->Session->read('UserAccount');

                    $rx_providers = array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID);
                    $rx_provider = (in_array($user['role_id'], $rx_providers)) ? 1 : 0;

                    $this->set('rx_provider', $rx_provider);
                } break;
            default: {
                    // moved to cronjob
                    //	$this->EmdeonRefillRequest->getEmdeonRefillList();
                }
        }
    }

    public function emdeon_refill_summary_grid() {
        $this->layout = "empty";

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        $this->loadModel("EmdeonRefillRequest");

        $this->EmdeonRefillRequest->execute($this, $task, true);
    }

    public function dosespot_refill_summary() {
        
    }

    public function dosespot_refill_summary_grid() {
        $this->layout = "empty";

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        $this->loadModel("DosespotRefillRequest");

        $this->DosespotRefillRequest->execute($this, $task);
    }

    public function lab_result_summary() {

        $user = $this->Session->read('UserAccount');
        //find out how many providers are in system
        $conditions = array('UserAccount.role_id  ' => array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID));
        $providers = $this->UserAccount->find('all', array('conditions' => $conditions));
        $this->set(compact('providers'));
    }

    public function lab_result_summary_grid() {
        $this->layout = "empty";
        $user = $this->Session->read('UserAccount');
        $this->loadModel('EmdeonLabResult');

        $usr = (isset($this->params['named']['usr'])) ? $this->params['named']['usr'] : "";
        $search = (isset($this->params['named']['search'])) ? $this->params['named']['search'] : "";
        //find out how many providers are in system
        $conditions = array('UserAccount.role_id  ' => array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID));
        $providers = $this->UserAccount->find('all', array('conditions' => $conditions));

        if ($usr == 'all') {
            $user2 = "";
        }
        //is a provider logged in?
        else if ($user['role_id'] == EMR_Roles::PHYSICIAN_ROLE_ID
                || $user['role_id'] == EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID
                || $user['role_id'] == EMR_Roles::NURSE_PRACTITIONER_ROLE_ID
        ) {
            $user2 = $user;
        } else {
            $user2 = "";
        }


        if ($search) {
            $electronic_lab_results = $this->EmdeonLabResult->getAlertPaginate($this, $user2, '', $search);
        } else {
            $electronic_lab_results = $this->EmdeonLabResult->getAlertPaginate($this, $user2, '');
        }


        $this->set(compact('electronic_lab_results', 'providers'));
    }

    public function medication_list() {
        $this->layout = "blank";
        $this->loadModel("PatientMedicationList");
        $this->loadModel("PatientDemographic");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $this->loadModel("UserGroup");
        $this->loadModel('EmdeonPrescription');

        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $prescriber = (isset($this->params['named']['prescriber'])) ? $this->params['named']['prescriber'] : "";

        $view_medications = (isset($this->params['named']['view_medications'])) ? $this->params['named']['view_medications'] : "";

        $dosespot = (isset($this->params['named']['dosespot'])) ? $this->params['named']['dosespot'] : "";
        if ($view_medications == 1 && $dosespot == 1) {
            $this->redirect(array('action' => 'medication_list', 'task' => 'dosespot', 'patient_id' => $patient_id, 'prescriber' => $prescriber));
        }
        if ($view_medications == 1 && $dosespot == 'show_dosespot_refill') {
            $this->redirect(array('action' => 'medication_list_refill', 'task' => 'show_dosespot_refill', 'patient_id' => $patient_id));
        }

        $refill_id = (isset($this->params['named']['refill_id'])) ? $this->params['named']['refill_id'] : "";
        if ($view_medications == 1 && strlen($refill_id) > 0) {
            $this->redirect(array('action' => 'medication_list_refill', 'task' => 'edit', 'patient_id' => $patient_id, 'refill_id' => $refill_id));
        }

        $medication_list_id = (isset($this->params['named']['medication_list_id'])) ? $this->params['named']['medication_list_id'] : "";
        if ($view_medications == 1 && strlen($medication_list_id) > 0) {
            $this->redirect(array('action' => 'medication_list', 'task' => 'edit', 'refill' => 1, 'patient_id' => $patient_id, 'medication_list_id' => $medication_list_id));
        }

        $this->loadModel("UserAccount");
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);

        $show_all_medications = (isset($this->params['named']['show_all_medications'])) ? $this->params['named']['show_all_medications'] : "";
        $show_surescripts = (isset($this->params['named']['show_surescripts'])) ? $this->params['named']['show_surescripts'] : "yes";
        $show_reported = (isset($this->params['named']['show_reported'])) ? $this->params['named']['show_reported'] : "yes";
        $show_prescribed = (isset($this->params['named']['show_prescribed'])) ? $this->params['named']['show_prescribed'] : "yes";
        $show_transition_referral = (isset($this->params['named']['show_transition_referral'])) ? $this->params['named']['show_transition_referral'] : "yes";
        $show_surescripts_history = (isset($this->params['named']['show_surescripts_history'])) ? $this->params['named']['show_surescripts_history'] : "no";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $mrn = $patient['mrn'];
        //$this->set('PatientMedicationList', $this->sanitizeHTML($this->PatientMedicationList->find('all')));
        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            $this->data['PatientMedicationList']['patient_id'] = $patient_id;
            $this->data['PatientMedicationList']['encounter_id'] = 0;
            $this->data['PatientMedicationList']['medication_type'] = "Standard";
            $this->data['PatientMedicationList']['provider_id'] = $this->data['PatientMedicationList']['provider_id'];
            $this->data['PatientMedicationList']['medication'] = $this->data['PatientMedicationList']['medication'];
            $this->data['PatientMedicationList']['diagnosis'] = $this->data['PatientMedicationList']['diagnosis'];
            $this->data['PatientMedicationList']['icd_code'] = $this->data['PatientMedicationList']['icd_code'];
            $this->data['PatientMedicationList']['taking'] = $this->data['PatientMedicationList']['taking'];
            $this->data['PatientMedicationList']['start_date'] = $this->data['PatientMedicationList']['start_date'] ? __date("Y-m-d", strtotime($this->data['PatientMedicationList']['start_date'])) : '';
            $this->data['PatientMedicationList']['end_date'] = $this->data['PatientMedicationList']['end_date'] ? __date("Y-m-d", strtotime($this->data['PatientMedicationList']['end_date'])) : '';
            $this->data['PatientMedicationList']['long_term'] = $this->data['PatientMedicationList']['long_term'];
            $this->data['PatientMedicationList']['source'] = $this->data['PatientMedicationList']['source'];
            $this->data['PatientMedicationList']['provider'] = $this->data['PatientMedicationList']['provider'];
            $this->data['PatientMedicationList']['status'] = $this->data['PatientMedicationList']['status'];
            $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
        }

        $is_midlevel = (EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID || EMR_Roles::NURSE_PRACTITIONER_ROLE_ID) ? true : false;
        $this->set("is_midlevel", $is_midlevel);
        $this->set("is_physician", (bool) ($this->Session->read("UserAccount.role_id") == EMR_Roles::PHYSICIAN_ROLE_ID));
        $this->set("dosepot_singlesignon_userid", $this->Session->read("UserAccount.dosepot_singlesignon_userid"));
        switch ($task) {
            case "emdeon_rx": {
                    $emdeon_medication_id = (isset($this->params['named']['emdeon_medication_id'])) ? $this->params['named']['emdeon_medication_id'] : 0;

                    $this->set('drug_id', 0);

                    if ($emdeon_medication_id > 0) {
                        // Get drug id
                        $this->loadModel('EmdeonPrescription');
                        $emdeon_rx = $this->EmdeonPrescription->find('first', array('conditions' => array('EmdeonPrescription.prescription_id' => $emdeon_medication_id)));

                        $this->set('drug_id', $emdeon_rx['EmdeonPrescription']['drug_id']);
                        $this->set('sig', $emdeon_rx['EmdeonPrescription']['sig']);
                        $this->set('quantity', $emdeon_rx['EmdeonPrescription']['quantity']);
                        $this->set('days_supply', $emdeon_rx['EmdeonPrescription']['days_supply']);
                        $this->set('refill_allowed', $emdeon_rx['EmdeonPrescription']['refill_allowed']);
                    }

                    $this->set("mrn", $patient['mrn']);
                } break;
            case "load_Icd9_autocomplete": {
                    if (!empty($this->data)) {
                        $this->Icd->execute($this, $task);
                    }
                    exit();
                } break;

            case "load_provider_autocomplete": {
                    if (!empty($this->data)) {
                        $search_keyword = '' . $this->data['autocomplete']['keyword'];
                        $search_limit = $this->data['autocomplete']['limit'];
                        $referred_by_items = $this->UserAccount->find('all', array('conditions' => array('OR' => array('UserAccount.firstname LIKE ' => $search_keyword . '%', 'UserAccount.lastname LIKE ' => $search_keyword . '%'), array('AND' => array('UserAccount.role_id' => 3))),
                            'limit' => $search_limit
                                ));

                        $data_array = array();

                        foreach ($referred_by_items as $referred_by_item) {
                            $data_array[] = $referred_by_item['UserAccount']['firstname'] . ' ' . $referred_by_item['UserAccount']['lastname'] . '|' . $referred_by_item['UserAccount']['user_id'];
                        }

                        echo implode("\n", $data_array);
                    }
                    exit();
                } break;

            case "addnew": {
                    if (!empty($this->data)) {
                        $this->PatientMedicationList->create();
                        $this->PatientMedicationList->save($this->data);
                        $this->PatientMedicationList->saveAudit('New');

                        $ret = array();

                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    $medication_list_id = (isset($this->params['named']['medication_list_id'])) ? $this->params['named']['medication_list_id'] : "";
                    $practice_settings = $this->Session->read("PracticeSetting");
                    $rx_setup = $practice_settings['PracticeSetting']['rx_setup'];


                    if (!empty($this->data)) {
                        // Get previous status
                        $previous_med = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.medication_list_id' => $medication_list_id)));
                        $previous_status = $previous_med['PatientMedicationList']['status'];

                        $medication_status = $this->data['PatientMedicationList']['status'];

                        //we have to also update the emdeon e-Rx table if status was changed
                        if ($rx_setup == 'Electronic_Emdeon' && !empty($this->data['PatientMedicationList']['emdeon_medication_id'])) {
                            $this->loadModel('EmdeonPrescription');
                            $data2['rx_status'] = $medication_status;
                            $data2['prescription_id'] = $this->data['PatientMedicationList']['emdeon_medication_id'];
                            $this->EmdeonPrescription->save($data2);

                            if ($previous_status != $medication_status && $medication_status == 'Discontinued') {
                                $discontinued_med = $this->EmdeonPrescription->find('first', array('conditions' => array('EmdeonPrescription.prescription_id' => $this->data['PatientMedicationList']['emdeon_medication_id'])));

                                $emdeon_xml_api = new Emdeon_XML_API;
                                $object_param = array();
                                $object_param['rx'] = $discontinued_med['EmdeonPrescription']['rx_unique_id'];
                                $object_param['rx_status'] = "Discontinued";
                                $result = $emdeon_xml_api->execute("rx", "update", $object_param);
                            }
                        }

                        $this->data['PatientMedicationList']['medication_list_id'] = $medication_list_id;
                        if (( $this->data['PatientMedicationList']['end_date'] == '' ||
                                $this->data['PatientMedicationList']['end_date'] == '0000-00-00' ) &&
                                ( $medication_status == 'Inactive' ||
                                $medication_status == 'Cancelled' ||
                                $medication_status == 'Discontinued' ||
                                $medication_status == 'Completed' )) {
                            $this->data['PatientMedicationList']['end_date'] = __date("Y-m-d");
                        }
                        $this->PatientMedicationList->saveAudit('Update');
                        $this->PatientMedicationList->save($this->data);

                        if ($this->data['refill'] == 1) {
                            $this->PatientMedicationList->refill($medication_list_id);
                        }



                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $items = $this->PatientMedicationList->find(
                                'first', array(
                            'conditions' => array('PatientMedicationList.medication_list_id' => $medication_list_id)
                                )
                        );
                        //echo $items['PatientMedicationList']['emdeon_drug_id']; die;
                        if (!empty($items['PatientMedicationList']['emdeon_drug_id'])) {
                            if ($rx_setup == 'Electronic_Emdeon') {
                                $this->loadModel("EmdeonPrescription");
                                $pharmacy = $this->EmdeonPrescription->find(
                                        'first', array(
                                    'conditions' => array(
                                        'EmdeonPrescription.patient_id' => $patient_id, 'EmdeonPrescription.drug_id' => $items['PatientMedicationList']['emdeon_drug_id']),
                                    'recursive' => -1,
                                    'fields' => array('EmdeonPrescription.pharmacy_name', 'EmdeonPrescription.authorized_date', 'EmdeonPrescription.prescriber_name')));
                                $this->set("pharmacy_details", $pharmacy['EmdeonPrescription']);
                            }
                        }
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;

            case "markNone": {
                    if (!empty($this->data)) {
                        $this->data['PatientDemographic']['patient_id'] = $patient_id;
                        $this->data['PatientDemographic']['medication_list_none'] = $this->data['submitted']['value'];
                        $this->PatientDemographic->save($this->data);
                    }
                    exit;
                } break;

            case "import_medications_from_surescripts": {
                    $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);

                    // you can't proceed any further if no dosespot patient ID is defined, so abort. 
                    if ($dosespot_patient_id && is_numeric($dosespot_patient_id)) {
                        $dosespot_xml_api = new Dosespot_XML_API();
                        $medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id, true);

                        foreach ($medication_items as $medication_item) {
                            $dosespot_medication_id = $medication_item['MedicationId'];
                            $items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.dosespot_medication_id' => $dosespot_medication_id)));

                            $this->data['PatientMedicationList']['medication_type'] = "surescripts_history"; //if this name is ever modified, update PatientMedicationList->removeDosespotDeletedData() function to match
                            $this->data['PatientMedicationList']['source'] = "Surescripts History";
                            if (empty($items)) {
                                $this->PatientMedicationList->create();
                                $this->data['PatientMedicationList']['status'] = $medication_item['status'];
                                $this->PatientMedicationList->saveAudit('New');
                            } else {
                                $this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
                                $this->data['PatientMedicationList']['status'] = $items['PatientMedicationList']['status'];
                                $this->PatientMedicationList->saveAudit('Update');
                            }
                            $start_date = __date('Y-m-d', strtotime($medication_item['date_written'] . '+' . $medication_item['days_supply'] . 'days'));
                            $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                            $this->data['PatientMedicationList']['patient_id'] = $patient_id;
                            $this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
                            $this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
                            $this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
                            $this->data['PatientMedicationList']['status'] = $medication_item['status'];
                            $this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
                            $this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
                            $this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
                            $this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
                            //only set inactive_date IF days_supply was provided
                            if (!empty($medication_item['days_supply'])) {
                                $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                            }
                            $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
                            $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");

                            $this->PatientMedicationList->save($this->data);
                        }

                        //Remove the dosespot data from the database.If removed in dosespot.	
                        $this->PatientMedicationList->removeDosespotDeletedData($patient_id, true, $medication_items, true);
                    } //if dosespot patient ID loop close		

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                } break;

            case "import_medications_from_surescripts_emdeon": {
                    $this->loadModel('EmdeonPrescription');
                    $emdeon_xml_api = new Emdeon_XML_API();
                    $person = $emdeon_xml_api->getPersonByMRN($mrn);

                    $medication_items = $this->EmdeonPrescription->find('all', array(
                        'conditions' => array('EmdeonPrescription.patient_id' => $patient_id, 'EmdeonPrescription.rx_status !=' => 'Pending'),
                        'group' => array('EmdeonPrescription.rx_unique_id')
                            ));
                    foreach ($medication_items as $medication_item) {
                        $emdeon_medication_id = $medication_item['EmdeonPrescription']['prescription_id'];
                        $items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.emdeon_medication_id' => $emdeon_medication_id)));

                        if (empty($items)) {
                            $start_date_split = explode(" ", $medication_item['EmdeonPrescription']['created_date']);
                            $start_date = __date('Y-m-d', strtotime($start_date_split[0] . '+' . $medication_item['EmdeonPrescription']['days_supply'] . 'days'));
                            $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                            $this->data = array();
                            $this->data['PatientMedicationList']['patient_id'] = $patient_id;
                            $this->data['PatientMedicationList']['encounter_id'] = $medication_item['EmdeonPrescription']['encounter_id'];
                            $this->data['PatientMedicationList']['medication_type'] = "Electronic_Emdeon";
                            $this->data['PatientMedicationList']['emdeon_medication_id'] = $emdeon_medication_id;
                            $this->data['PatientMedicationList']['emdeon_drug_id'] = (int) $medication_item['EmdeonPrescription']['drug_id'];
                            $this->data['PatientMedicationList']['medication'] = $medication_item['EmdeonPrescription']['drug_name'];
                            $this->data['PatientMedicationList']['rxnorm'] = $medication_item['EmdeonPrescription']['rxnorm'];
                            $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                            $this->data['PatientMedicationList']['status'] = "Active";
                            $this->data['PatientMedicationList']['direction'] = $medication_item['EmdeonPrescription']['sig'];
                            $this->data['PatientMedicationList']['quantity_value'] = $medication_item['EmdeonPrescription']['quantity'];
                            $this->data['PatientMedicationList']['refill_allowed'] = intval($medication_item['EmdeonPrescription']['refills']);
                            $this->data['PatientMedicationList']['start_date'] = __date("Y-m-d", strtotime($start_date_split[0]));
                            $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                            $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
                            $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                            $this->PatientMedicationList->create();
                            $this->PatientMedicationList->saveAudit('New');
                            $this->PatientMedicationList->save($this->data);
                        } else {
                            /* I DON'T THINK THIS IS NEEDED - Robert @ 6/16/2014
                              this should mean the med was already in the table, and it's possible the user had modified it. if this runs,
                              it will overwrite the changes the user made in our table

                              $start_date_split = explode(" ", $medication_item['EmdeonPrescription']['created_date']);
                              $start_date = __date('Y-m-d', strtotime($start_date_split[0].'+'.$medication_item['EmdeonPrescription']['days_supply'].'days'));
                              $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                              $this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
                              $this->data['PatientMedicationList']['patient_id'] = $patient_id;
                              $this->data['PatientMedicationList']['medication_type'] = "Electronic_Emdeon";
                              $this->data['PatientMedicationList']['emdeon_medication_id'] = $emdeon_medication_id;
                              $this->data['PatientMedicationList']['emdeon_drug_id'] = (int)$medication_item['EmdeonPrescription']['drug_id'];
                              $this->data['PatientMedicationList']['medication'] = $medication_item['EmdeonPrescription']['drug_name'];
                              $this->data['PatientMedicationList']['rxnorm'] = @$medication_item['EmdeonPrescription']['rxnorm'];
                              $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                              $this->data['PatientMedicationList']['status'] = "Active";
                              $this->data['PatientMedicationList']['direction'] = "";
                              $this->data['PatientMedicationList']['quantity_value'] = $medication_item['EmdeonPrescription']['quantity'];
                              $this->data['PatientMedicationList']['refill_allowed'] = $medication_item['EmdeonPrescription']['refill_allowed'];
                              $this->data['PatientMedicationList']['start_date'] = __date("Y-m-d", strtotime($start_date_split[0]));
                              $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                              $this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
                              $this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
                              $this->PatientMedicationList->saveAudit('Update');
                             */
                        }
                    }

                    //Remove the dosespot data from the database.If removed in dosespot.	
                    //$this->PatientMedicationList->removeDosespotDeletedData($patient_id, true, $medication_items);	

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                } break;

            case "update_status": {
                    $medication_status = $this->data['submitted']['value'];
                    if ($medication_status == 'Inactive' ||
                            $medication_status == 'Cancelled' ||
                            $medication_status == 'Discontinued' ||
                            $medication_status == 'Completed') {
                        // Check to autofill the end date
                        $existing = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.medication_list_id' => $this->data['medication_list_id'])));
                        if (isset($existing) &&
                                (!isset($existing['PatientMedicationList']['end_date']) ||
                                $existing['PatientMedicationList']['end_date'] == '0000-00-00' )) {
                            $this->data['PatientMedicationList']['medication_list_id'] = $this->data['medication_list_id'];
                            $this->data['PatientMedicationList']['end_date'] = __date("Y-m-d");
                            $this->PatientMedicationList->saveAudit('UpdateStatusAutofill');
                            $this->PatientMedicationList->save($this->data);
                        }
                    }
                    $this->PatientMedicationList->setItemValue("status", $this->data['submitted']['value'], $this->data['medication_list_id'], $patient_id, $this->user_id);
                    echo $this->data['submitted']['value'];
                    exit;
                }
                break;

            case 'track_changes': {
                    $encounter_id = $this->params['form']['encounter_id'];
                    $medication_list_id = $this->params['form']['medication_list_id'];
                    $status = $this->params['form']['status'];

                    $existing = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.medication_list_id' => $medication_list_id)));


                    if (!$existing) {
                        die();
                    }


                    $frequency = '';
                    $unit = '';
                    $route = '';
                    $quantity = '';
                    $direction = '';

                    $frequency_value = $existing['PatientMedicationList']['frequency'];
                    $unit_value = $existing['PatientMedicationList']['unit'];
                    $route_value = $existing['PatientMedicationList']['route'];
                    $quantity_value = $existing['PatientMedicationList']['quantity'];
                    $direction_value = $existing['PatientMedicationList']['direction'];
                    if ($frequency_value != "") {
                        $frequency = ', ' . $frequency_value;
                    }
                    if ($unit_value != "") {
                        $unit = ', ' . $unit_value;
                    }
                    if ($route_value != "") {
                        $route = ', ' . $route_value;
                    }
                    if ($quantity_value != "0") {
                        $quantity = ', ' . $quantity_value;
                    }
                    if ($direction_value != "") {
                        $direction = ', ' . $direction_value;
                    }




                    $data = array(
                        'encounter_id' => $encounter_id,
                        'medication_list_id' => $medication_list_id,
                        'medication_details' => $existing['PatientMedicationList']['medication'] . $quantity . $unit . $route . $frequency . $direction,
                        'medication_status' => $status,
                        'modified_user_id' => $_SESSION['UserAccount']['user_id']
                    );

                    $this->loadModel('EncounterPlanRxChanges');
                    $this->EncounterPlanRxChanges->save($data);



                    exit;
                } break;

            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientMedicationList']['medication_list_id'];

                        foreach ($ids as $id) {
                            $this->PatientMedicationList->saveAudit('Delete');
                            $this->PatientMedicationList->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }break;
            case "delete_medication": {
                    $medication_list_id = (isset($this->data['medication_list_id'])) ? $this->data['medication_list_id'] : "";
                    $this->PatientMedicationList->delete($medication_list_id, false);
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }break;
            case "get_report_html": {
                    $controller->layout = 'empty';

                    if ($report = Medication_List::generateReport($patient_id, $show_all_medications, true)) {
                        App::import('Helper', 'Html');
                        $html = new HtmlHelper();

                        echo $report;

                        ob_flush();
                        flush();

                        exit();
                    }
                    exit('could not generate report');
                }
            case "get_report_pdf" : {
                    $view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : '';
                    $this->layout = 'empty';
                    if ($report = Medication_List::generateReport($patient_id, $show_all_medications, true)) {
                        if ($task == "get_report_pdf") {
                            //$this->loadModel("Pdf");

                            $url = $this->paths['temp'];
                            $url = str_replace('//', '/', $url);

                            $pdffile = $mrn . '_MedicationList.pdf';

                            //PDF file creation
                            //site::write(pdfReport::generate($report, $url.$pdffile), $url.$pdffile);
                            site::write(pdfReport::generate($report), $url . $pdffile);
                            $file = $mrn . '_MedicationList.pdf';
                            $targetPath = $this->paths['temp'];
                            $targetFile = str_replace('//', '/', $targetPath) . $file;
                            if ($view == 'fax') {
                                $this->loadModel('practiceSetting');
                                $settings = $this->practiceSetting->getSettings();
                                if (!$settings->faxage_username || !$settings->faxage_password || !$settings->faxage_company) {
                                    $this->Session->setFlash(__('Fax is not enabled. Contact Sales for assistance.', true));
                                    $this->redirect(array('controller' => 'encounters', 'action' => 'index'));
                                    exit();
                                }
                                if ($view == 'fax') {
                                    $this->Session->write('fileName', $targetFile);
                                    $this->redirect(array('controller' => 'messaging', 'action' => 'new_fax', 'patient_id' => $patient_id, 'fax_doc'));
                                    exit;
                                }
                            }



                            header('Content-Type: application/octet-stream; name="' . $file . '"');
                            header('Content-Disposition: attachment; filename="' . $file . '"');
                            header('Accept-Ranges: bytes');
                            header('Pragma: no-cache');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Content-transfer-encoding: binary');
                            header('Content-length: ' . @filesize($targetFile));
                            @readfile($targetFile);
                        } else {
                            echo $report;
                        }
                        ob_flush();
                        flush();
                        exit();
                    }
                    exit('could not generate report');
                } break;
            case "get_report_ccr" : {
                    MedicationCCR::generateCCR($this, $show_all_medications);
                } break;
            case "dosespot": {
                    $this->loadModel('AdministrationPrescriptionAuth');

                    $allowed = $this->AdministrationPrescriptionAuth->getAuthorizingUsers($_SESSION['UserAccount']['user_id']);
                    $allowedIds = Set::extract('/UserAccount/user_id', $allowed);


                    $prescriber = (isset($this->params['named']['prescriber'])) ? intval($this->params['named']['prescriber']) : 0;

                    if ($allowedIds && in_array($prescriber, $allowedIds)) {
                        foreach ($allowed as $userAccount) {

                            if (intval($userAccount['UserAccount']['user_id']) == $prescriber) {
                                $dosespot_xml_api = new Dosespot_XML_API($userAccount, 'write');
                                break;
                            }
                        }
                    } else {
                        $dosespot_xml_api = new Dosespot_XML_API(false, 'write');
                    }

                    $this->set("dosespot_info", $dosespot_xml_api->getInfo());
                    $demographic_item = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
                    $this->set('demographic_item', $demographic_item['PatientDemographic']);

                    $this->loadModel('PracticeSetting');
                    $settings = $this->Session->read("PracticeSetting");
                    $db_config = $this->PracticeSetting->getDataSource()->config;
                    $cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';

                    $dosespot_accessed = Cache::read($cache_file_prefix . 'dosespot_accessed');

                    if (!$dosespot_accessed) {
                        $dosespot_accessed = array();
                    }

                    $dosespot_accessed[] = $patient_id;
                    $dosespot_accessed = array_unique($dosespot_accessed);

                    Cache::write($cache_file_prefix . 'dosespot_accessed', $dosespot_accessed);
                } break;

            case 'encounter_dosespot':
                // Fetch newly added dosespot medication for patient
                // and associate with given encounter

                $encounterId = (isset($this->params['named']['encounter_id'])) ? intval($this->params['named']['encounter_id']) : 0;

                $practice_settings = $this->Session->read("PracticeSetting");
                $rx_setup = $practice_settings['PracticeSetting']['rx_setup'];
                if ($rx_setup == 'Electronic_Dosespot') {
                    $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
                    //if dosespot patient ID is defined. you can't proceed!
                    if ($dosespot_patient_id && is_numeric($dosespot_patient_id)) {
                        $begin_date = __date('Y-m-d', strtotime('-1 day'));
                        $dosespot_xml_api = new Dosespot_XML_API();
                        $medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id, false, $begin_date);
                        foreach ($medication_items as $medication_item) {
                            $dosespot_medication_id = $medication_item['MedicationId'];
                            $items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.dosespot_medication_id' => $dosespot_medication_id)));

                            if (empty($items)) {
                                $start_date = __date('Y-m-d', strtotime($medication_item['date_written'] . '+' . $medication_item['days_supply'] . 'days'));
                                $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                                $this->data = array();
                                $this->data['PatientMedicationList']['patient_id'] = $patient_id;
                                $this->data['PatientMedicationList']['encounter_id'] = $encounterId;
                                $this->data['PatientMedicationList']['medication_type'] = "Electronic";
                                $this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
                                $this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
                                $this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
                                $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                                $this->data['PatientMedicationList']['status'] = $medication_item['status'];
                                $this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
                                $this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
                                $this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
                                $this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
                                //only set inactive_date IF days_supply was provided 
                                if (!empty($medication_item['days_supply'])) {
                                    $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                                }
                                $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
                                $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                $this->PatientMedicationList->create();
                                $this->PatientMedicationList->saveAudit('New');
                                $this->PatientMedicationList->save($this->data);
                                echo 'Added ' . $medication_item['medication'] . " <br />\n";
                            }
                        }
                    } //close loop if $dosespot_patient_id	
                }




                exit();
                break;
            default: {
                    //Import medications from Dosespot when loading the display table in medication list page.
                    $db_config = $this->PatientDemographic->getDataSource()->config;
                    $cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';

                    $practice_settings = $this->Session->read("PracticeSetting");
                    $rx_setup = $practice_settings['PracticeSetting']['rx_setup'];

                    if ($rx_setup == 'Electronic_Emdeon') {
                        $this->loadModel('AdministrationPrescriptionAuth');

                        $allowed = $this->AdministrationPrescriptionAuth->getAuthorizingUsers($_SESSION['UserAccount']['user_id']);
                        $this->set('prescriptionAuth', $allowed);
                    }


                    $this->loadModel('AdministrationPrescriptionAuth');
                    $autoupdateMeds = intval($practice_settings['PracticeSetting']['autoupdate_meds']);

                    $allowed = $this->AdministrationPrescriptionAuth->getAuthorizingUsers($_SESSION['UserAccount']['user_id']);
                    $this->set('prescriptionAuth', $allowed);
                    if ($rx_setup == 'Electronic_Dosespot') {

                        $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);

                        //If the patient not exists in Dosespot, add the patient to Dosespot
                        if ($dosespot_patient_id == 0 or $dosespot_patient_id == '') {
                            $this->PatientDemographic->updateDosespotPatient($patient_id);
                            $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
                        }
                        // must have $dosespot_patient_id to proceed, otherwise skip				
                        if ($dosespot_patient_id && is_numeric($dosespot_patient_id)) {
                            $dosespot_xml_api = new Dosespot_XML_API();
                            $dosespot_cache_key = $cache_file_prefix . $dosespot_patient_id . '_dosespot_import_time_stamp';
                            Cache::set(array('duration' => '+2 months'));
                            $import_time_stamp = Cache::read($dosespot_cache_key);
                            if (!empty($import_time_stamp)) {
                                $medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id, false, $import_time_stamp);
                            } else {
                                $medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id);
                            }
                            foreach ($medication_items as $medication_item) {
                                $dosespot_medication_id = $medication_item['MedicationId'];
                                $items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.dosespot_medication_id' => $dosespot_medication_id)));

                                if (empty($items)) {
                                    $start_date = __date('Y-m-d', strtotime($medication_item['date_written'] . '+' . $medication_item['days_supply'] . 'days'));
                                    $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                                    $this->data = array();
                                    $this->data['PatientMedicationList']['patient_id'] = $patient_id;
                                    $this->data['PatientMedicationList']['medication_type'] = "Electronic";
                                    $this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
                                    $this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
                                    $this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
                                    $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                                    $this->data['PatientMedicationList']['status'] = $medication_item['status'];
                                    $this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
                                    $this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
                                    $this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
                                    $this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
                                    //only set inactive_date IF days_supply was provided 
                                    if (!empty($medication_item['days_supply'])) {
                                        $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                                    }
                                    $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
                                    $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                    $this->PatientMedicationList->create();
                                    $this->PatientMedicationList->saveAudit('New');
                                    $this->PatientMedicationList->save($this->data);
                                } else {
                                    $start_date = __date('Y-m-d', strtotime($medication_item['date_written'] . '+' . $medication_item['days_supply'] . 'days'));
                                    $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                                    $this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
                                    $this->data['PatientMedicationList']['patient_id'] = $patient_id;
                                    $this->data['PatientMedicationList']['medication_type'] = "Electronic";
                                    $this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
                                    $this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
                                    $this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
                                    $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                                    $this->data['PatientMedicationList']['status'] = $items['PatientMedicationList']['status'];
                                    $this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
                                    $this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
                                    $this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
                                    $this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
                                    //only set inactive_date IF days_supply was provided 
                                    if (!empty($medication_item['days_supply'])) {
                                        $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                                    }
                                    $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
                                    $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                    //only set inactive_date IF days_supply was provided 
                                    if (strtotime(date('Y-m-d')) >= strtotime($inactive_date) && !empty($medication_item['days_supply']) && $autoupdateMeds) {
                                        $this->data['PatientMedicationList']['status'] = 'Completed';
                                    }

                                    $this->PatientMedicationList->saveAudit('Update');
                                    $this->PatientMedicationList->save($this->data);
                                }
                            }

                            //Remove the dosespot data from the database.If removed in dosespot.	
                            $this->PatientMedicationList->removeDosespotDeletedData($patient_id, true, $medication_items);
                            Cache::set(array('duration' => '+2 months'));
                            Cache::write($dosespot_cache_key, __date('Y-m-d', strtotime('-1 week')));
                        } //close loop if $dosespot_patient_id
                    }

                    $isRefillEnable = $this->UserGroup->isRxRefillEnable();
                    $this->set("isRefillEnable", $isRefillEnable);

                    $demographic_items = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
                    $medication_list_none = $demographic_items['PatientDemographic']['medication_list_none'];
                    $this->set('medication_list_none', $medication_list_none);

                    if ($rx_setup == 'Electronic_Emdeon') {
                        $emdeon_xml_api = new Emdeon_XML_API();
                        $person = $emdeon_xml_api->getPersonByMRN($mrn);

                        $medication_items = $this->EmdeonPrescription->find('all', array(
                            'conditions' => array('EmdeonPrescription.patient_id' => $patient_id, 'EmdeonPrescription.rx_status !=' => 'Pending'),
                            'group' => array('EmdeonPrescription.rx_unique_id')
                                ));
                        foreach ($medication_items as $medication_item) {
                            $emdeon_medication_id = $medication_item['EmdeonPrescription']['prescription_id'];
                            $items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.emdeon_medication_id' => $emdeon_medication_id)));

                            if (empty($items)) {
                                $this->PatientMedicationList->create();
                                $this->PatientMedicationList->saveAudit('New');
                                $start_date_split = explode(" ", $medication_item['EmdeonPrescription']['created_date']);
                                $start_date = __date('Y-m-d', strtotime($start_date_split[0] . '+' . $medication_item['EmdeonPrescription']['days_supply'] . 'days'));
                                $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                                $this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
                                $this->data['PatientMedicationList']['patient_id'] = $patient_id;
                                $this->data['PatientMedicationList']['medication_type'] = "Electronic_Emdeon";
                                $this->data['PatientMedicationList']['emdeon_medication_id'] = $emdeon_medication_id;
                                $this->data['PatientMedicationList']['emdeon_drug_id'] = (int) $medication_item['EmdeonPrescription']['drug_id'];
                                $this->data['PatientMedicationList']['medication'] = $medication_item['EmdeonPrescription']['drug_name'];
                                $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                                $this->data['PatientMedicationList']['encounter_id'] = $medication_item['EmdeonPrescription']['encounter_id'];
                                $this->data['PatientMedicationList']['status'] = ($medication_item['EmdeonPrescription']['rx_status'] == 'Authorized') ? 'Active' : $medication_item['EmdeonPrescription']['rx_status'];
                                $this->data['PatientMedicationList']['direction'] = $medication_item['EmdeonPrescription']['sig'];
                                $this->data['PatientMedicationList']['quantity_value'] = $medication_item['EmdeonPrescription']['quantity'];
                                $this->data['PatientMedicationList']['refill_allowed'] = intval($medication_item['EmdeonPrescription']['refills']);
                                $this->data['PatientMedicationList']['start_date'] = __date("Y-m-d", strtotime($start_date_split[0]));
                                $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                                $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
                                $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                $this->PatientMedicationList->save($this->data);
                            }
                        }
                    }

                    $source_array = array();
                    $source_array[] = '';
                    if ($show_surescripts == 'yes') {
                        $source_array[] = 'e-Prescribing History';
                    }
                    if ($show_reported == 'yes') {
                        $source_array[] = 'Patient Reported';
                    }
                    if ($show_prescribed == 'yes') {
                        $source_array[] = 'Practice Prescribed';
                    }
                    if ($show_transition_referral == 'yes') {
                        $source_array[] = 'Transition of Care/Referral';
                    }

                    if ($show_all_medications == 'yes') {
                        if ($show_surescripts_history == 'yes') {
                            $source_array[] = "Surescripts History";
                        }

                        $this->set('show_all_medications', 'yes');

                        $this->paginate['PatientMedicationList'] = array(
                            'conditions' => array('PatientMedicationList.patient_id' => $patient_id,
                                'PatientMedicationList.source' => $source_array),
                            'order' => array('PatientMedicationList.modified_timestamp' => 'DESC')
                        );
                    } else {
                        $this->set('show_all_medications', 'no');

                        $conditions['PatientMedicationList.patient_id'] = $patient_id;
                        //this is outside/old surescripts hx from other providers
                        if ($show_surescripts_history == 'yes') {
                            $conditions['OR'] = array(
                                array("PatientMedicationList.source" => $source_array, "PatientMedicationList.status" => "Active"),
                                array("PatientMedicationList.source" => "Surescripts History"));
                        } else {
                            $conditions['PatientMedicationList.source'] = $source_array;
                            $conditions['PatientMedicationList.status'] = 'Active';
                        }

                        $this->paginate['PatientMedicationList'] = array(
                            'conditions' => $conditions,
                            'order' => array('PatientMedicationList.modified_timestamp' => 'DESC'),
                            'recursive' => -1
                        );
                    }
                    $PatientMedicationList = $this->sanitizeHTML($this->paginate('PatientMedicationList'));
                    $this->set('PatientMedicationList', $PatientMedicationList);

                    if ($rx_setup == 'Electronic_Dosespot') {
                        $dosespot_xml_api = new Dosespot_XML_API();
                        $this->set('verifydosespotinfo', $dosespot_xml_api->verifyPatientDemographics($demographic_items['PatientDemographic']));
                    }

                    $this->set('show_surescripts', $show_surescripts);
                    $this->set('show_reported', $show_reported);
                    $this->set('show_prescribed', $show_prescribed);
                    $this->set('show_transition_referral', $show_transition_referral);
                    $this->set('show_surescripts_history', $show_surescripts_history);
                    $this->PatientMedicationList->saveAudit('View');
                }
        }
    }

    public function imm_injections() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
    }

    public function in_house_work_labs() {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user = $this->Session->read('UserAccount');
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        $view_labs = (isset($this->params['named']['view_labs'])) ? $this->params['named']['view_labs'] : "";
        $order_id = (isset($this->params['named']['order_id'])) ? $this->params['named']['order_id'] : "";

        if ($view_labs == 1 && strlen($order_id) > 0) {
            $this->redirect(array('action' => 'lab_results_electronic', 'task' => 'edit_order', 'patient_id' => $patient_id, 'order_id' => $order_id));
        }

        $this->loadModel('Unit');
        $this->set("units", $this->Unit->find('all'));

        $this->loadModel('SpecimenSource');
        $this->set("specimen_sources", $this->SpecimenSource->find('all'));

        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                        $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                        $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                        $this->data['EncounterPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['EncounterPointOfCare']['modified_user_id'] = $this->user_id;
                        $this->EncounterPointOfCare->create();
                        $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['lab_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['lab_date_performed']));

                        if (isset($this->params['form']['lab_panels'])) {
                            $posted_panels = $this->params['form']['lab_panels'];
                            $panels = array();

                            foreach ($posted_panels as $field => $value) {
                                $panels[$field] = $value;
                            }

                            $this->data['EncounterPointOfCare']['lab_panels'] = json_encode($panels);
                        }


                        $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->data['EncounterPointOfCare']['point_of_care_id'];

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->EncounterPointOfCare->find(
                                'first', array(
                            'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                        $this->set('rawData', $items);
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                            $this->EncounterPointOfCare->delete($id, false);

                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }break;
            default: {
                    $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
                    //debug($encounter_items);

                    if ($encounter_items) {
                        $this->paginate['EncounterPointOfCare'] = array(
                            'conditions' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Labs'),
                            'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                        );
                    } else {
                        $this->paginate['EncounterPointOfCare'] = array(
                            'conditions' => array('EncounterPointOfCare.encounter_id' => null),
                        );
                    }
                    $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));

                    //$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Labs'))));

                    $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                } break;
        }
    }

    public function lab_results_electronic() {
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);


        $this->loadModel("EmdeonOrder");
        $this->EmdeonOrder->execute($this);
    }

    public function lab_results_electronic_view() {
        if (isset($this->Toolbar)) {
            $this->Toolbar->enabled = false;
        }

        $this->layout = "empty";
        $this->loadModel("EmdeonLabResult");

        $lab_result_id = (isset($this->params['named']['lab_result_id'])) ? $this->params['named']['lab_result_id'] : "";
        $page = (isset($this->params['named']['page'])) ? $this->params['named']['page'] : 1;

        if (isset($this->params['named']['auto_print'])) {
            $page = 'print';
        }

        $this->set('report_html', $this->EmdeonLabResult->getHTML($lab_result_id, $page));
    }

    public function lab_result_graph() {
        $this->layout = "blank";
        $this->loadModel('EmdeonLabResult');
        $this->EmdeonLabResult->executeGraph($this);
    }

    public function lab_results() {
        $this->layout = "blank";
        $this->loadModel("PatientLabResult");
        $this->loadModel("EncounterPlanLab");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $this->loadModel("DirectoryLabFacility");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        $this->loadModel('Unit');
        $this->set("units", $this->Unit->find('all'));
        $this->loadModel('SpecimenSource');
        $this->set("specimen_sources", $this->SpecimenSource->find('all'));

        $this->loadModel("StateCode");
        $this->set("StateCode", $this->sanitizeHTML($this->StateCode->find('all')));

        $labs_setup = $this->Session->read("PracticeSetting.PracticeSetting.labs_setup");
        $this->set("labs_setup", $labs_setup);

        $standard_order_list = $this->EncounterPlanLab->getOrderList($patient_id);
        $this->set("standard_order_list", $standard_order_list);

        switch ($task) {
            /* 3718
              case "process_hl7":
              {
              $file_location = $this->paths['temp'] . $this->data['filename'];
              $hl7_contents = file($file_location);

              $this->PatientLabResult->extractHL7Details($hl7_contents, $patient_id);
              echo json_encode(array());
              exit;
              } break;
             */
            case "load_Icd9_autocomplete": {
                    if (!empty($this->data)) {
                        $this->Icd->execute($this, $task);
                    }
                    exit();
                } break;

            case "labname_load": {
                    if (!empty($this->data)) {
                        $search_keyword = $this->data['autocomplete']['keyword'];
                        $search_limit = $this->data['autocomplete']['limit'];
                        $lab_items = $this->DirectoryLabFacility->find('all', array(
                            'conditions' => array('DirectoryLabFacility.lab_facility_name LIKE ' => '%' . $search_keyword . '%'), 'limit' => $search_limit)
                        );
                        $data_array = array();

                        foreach ($lab_items as $lab_item) {
                            $data_array[] = $lab_item['DirectoryLabFacility']['lab_facility_name'] . '|' . $lab_item['DirectoryLabFacility']['address_1'] . '|' . $lab_item['DirectoryLabFacility']['address_2'] . '|' . $lab_item['DirectoryLabFacility']['city'] . '|' . $lab_item['DirectoryLabFacility']['state'] . '|' . $lab_item['DirectoryLabFacility']['zip_code'] . '|' . $lab_item['DirectoryLabFacility']['country'];
                        }

                        echo implode("\n", $data_array);
                    }
                    exit();
                } break;

            case "addnew": {
                    if (!empty($this->data)) {
                        $this->data['PatientLabResult']['patient_id'] = $patient_id;
                        $this->data['PatientLabResult']['date_ordered'] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['date_ordered']));
                        $this->data['PatientLabResult']['report_date'] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['report_date']));
                        $this->data['PatientLabResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['PatientLabResult']['modified_user_id'] = $this->user_id;

                        for ($i = 1; $i <= 5; $i++) {
                            $this->data['PatientLabResult']['test_report_date' . $i] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['test_report_date' . $i]));
                        }

                        $this->PatientLabResult->create();
                        $this->PatientLabResult->save($this->data);
                        $lab_result_id = $this->PatientLabResult->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['PatientLabResult']['patient_id'] = $patient_id;
                        $this->data['PatientLabResult']['date_ordered'] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['date_ordered']));
                        $this->data['PatientLabResult']['report_date'] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['report_date']));
                        $this->data['PatientLabResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['PatientLabResult']['modified_user_id'] = $this->user_id;

                        for ($i = 1; $i <= 5; $i++) {
                            $this->data['PatientLabResult']['test_report_date' . $i] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['test_report_date' . $i]));
                        }

                        $this->PatientLabResult->saveAudit('Update');
                        $this->PatientLabResult->save($this->data);

                        //App::import('Model','PatientActivities');
                        //App::import('Helper', 'Html');$html = new HtmlHelper();
                        $lab_result_id = $this->data['PatientLabResult']['lab_result_id'];

                        //$PatientActivities= new PatientActivities();
                        //$PatientActivities->addActivitiesItem($this->data['PatientLabResult']['ordered_by_id'], $this->data['PatientLabResult']['test_name'], "Labs", "Outside Labs", $this->data['PatientLabResult']['status'], $patient_id, $lab_result_id , $editlink);                    

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $lab_result_id = (isset($this->params['named']['lab_result_id'])) ? $this->params['named']['lab_result_id'] : "";
                        $items = $this->PatientLabResult->find(
                                'first', array(
                            'conditions' => array('PatientLabResult.lab_result_id' => $lab_result_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientLabResult']['lab_result_id'];

                        foreach ($ids as $id) {
                            $this->PatientLabResult->saveAudit('Delete');
                            $this->PatientLabResult->delete($id, false);
                            //App::import('Model','PatientActivities');                        
                            //$PatientActivities= new PatientActivities();
                            //$PatientActivities->deleteActivitiesItem( $id , "Labs", "Outside Labs");

                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $this->set('PatientLabResult', $this->sanitizeHTML($this->paginate('PatientLabResult', array('PatientLabResult.patient_id' => $patient_id, 'PatientLabResult.order_type' => $labs_setup))));
                    $this->PatientLabResult->saveAudit('View');
                }
        }
    }

    public function in_house_administered_by() {
        if (!empty($this->data)) {
            $search_keyword = '' . $this->data['autocomplete']['keyword'];
            $search_limit = $this->data['autocomplete']['limit'];
            $referred_by_items = $this->UserAccount->find('all', array('conditions' => array('OR' => array('UserAccount.firstname LIKE ' => $search_keyword . '%', 'UserAccount.lastname LIKE ' => $search_keyword . '%'), array('AND' => array('UserAccount.role_id' => array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID, EMR_Roles::MEDICAL_ASSISTANT_ROLE_ID)))),
                'order' => array('UserAccount.firstname' => 'asc', 'UserAccount.lastname' => 'asc'),
                'limit' => $search_limit
                    ));

            $data_array = array();

            foreach ($referred_by_items as $referred_by_item) {
                $data_array[] = $referred_by_item['UserAccount']['firstname'] . ' ' . $referred_by_item['UserAccount']['lastname'] . '|' . $referred_by_item['UserAccount']['user_id'];
            }

            echo implode("\n", $data_array);
        }
        exit();
    }

    public function in_house_work_radiology() {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("PatientRadiologyResult");
        $this->loadModel("EncounterMaster");

        $user = $this->Session->read('UserAccount');
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task) {

            case 'save_file':
                $point_of_care_id = $this->params['named']['point_of_care_id'];

                if (isset($this->params['form']['name'])) {
                    $this->EncounterPointOfCare->id = $point_of_care_id;
                    $this->EncounterPointOfCare->saveField('file_upload', $this->params['form']['name']);
                }

                exit();
                break;

            case 'remove_file':
                $point_of_care_id = $this->params['named']['point_of_care_id'];

                if (isset($this->params['form']['delete'])) {
                    $this->EncounterPointOfCare->id = $point_of_care_id;
                    $file = $this->EncounterPointOfCare->field('file_upload');
                    if ($file) {
                        @unlink(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
                    }

                    $this->EncounterPointOfCare->saveField('file_upload', null);
                }

                exit();
                break;

            case 'download_file':
                $point_of_care_id = $this->params['named']['point_of_care_id'];

                $this->EncounterPointOfCare->id = $point_of_care_id;
                $file = $this->EncounterPointOfCare->field('file_upload');
                if ($file) {

                    $filename = explode(DIRECTORY_SEPARATOR, $file);
                    $filename = array_pop($filename);
                    $tmp = explode('_', $filename);
                    unset($tmp[0]);
                    $filename = implode('_', $tmp);

                    header("Content-Type: application/force-download");
                    header("Content-Type: application/octet-stream");
                    header("Content-Type: application/download");
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header("Cache-Control: no-cache, must-revalidate");
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                    readfile(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
                }



                exit();
                break;

            case "upload_file": {
                    if (!empty($_FILES)) {
                        $tempFile = $_FILES['file_upload']['tmp_name'];
                        $targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
                        $targetFile = str_replace('//', '/', $targetPath) . $_FILES['file_upload']['name'];

                        move_uploaded_file($tempFile, $targetFile);
                        echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $targetFile);
                    }

                    exit;
                } break;
            case "addnew": {
                    if (!empty($this->data)) {

                        $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                        $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                        $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                        $this->EncounterPointOfCare->create();
                        $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['radiology_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['radiology_date_performed']));
                        $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->EncounterPointOfCare->find(
                                'first', array(
                            'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                            $this->EncounterPointOfCare->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
                    /* $result = array();
                      if($encounter_items)
                      {
                      foreach($encounter_items as $encounter_item)
                      {
                      $result[] = $encounter_item['encounter_id'];
                      }
                      } */

                    if ($encounter_items) {
                        $this->paginate['EncounterPointOfCare'] = array(
                            'conditions' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Radiology'),
                            'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                        );
                    } else {
                        $this->paginate['EncounterPointOfCare'] = array(
                            'conditions' => array('EncounterPointOfCare.encounter_id' => null),
                        );
                    }
                    $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));


                    //$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Radiology'))));

                    $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                } break;
        }
    }

    public function radiology_results() {
        $this->layout = "blank";
        $this->loadModel("PatientRadiologyResult");
        $this->loadModel("DirectoryLabFacility");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        switch ($task) {
            case "load_Icd9_autocomplete": {
                    if (!empty($this->data)) {
                        $this->Icd->execute($this, $task);
                    }
                    exit();
                } break;

            case "labname_load": {
                    if (!empty($this->data)) {
                        $search_keyword = $this->data['autocomplete']['keyword'];
                        $lab_items = $this->DirectoryLabFacility->find('all', array(
                            'conditions' => array('DirectoryLabFacility.lab_facility_name LIKE ' => '%' . $search_keyword . '%'))
                        );
                        $data_array = array();

                        foreach ($lab_items as $lab_item) {
                            $data_array[] = $lab_item['DirectoryLabFacility']['lab_facility_name'] . '|' . $lab_item['DirectoryLabFacility']['address_1'] . '|' . $lab_item['DirectoryLabFacility']['address_2'] . '|' . $lab_item['DirectoryLabFacility']['city'] . '|' . $lab_item['DirectoryLabFacility']['state'] . '|' . $lab_item['DirectoryLabFacility']['zip_code'] . '|' . $lab_item['DirectoryLabFacility']['country'];
                        }

                        echo implode("\n", $data_array);
                    }
                    exit();
                } break;

            case "addnew": {
                    if (!empty($this->data)) {
                        $this->data['PatientRadiologyResult']['patient_id'] = $patient_id;
                        //$this->data['PatientRadiologyResult']['plan_radiology_id'] = 0;

                        if (isset($this->data['PatientRadiologyResult']['plan_radiology_id'])) {
                            unset($this->data['PatientRadiologyResult']['plan_radiology_id']);
                        }

                        $this->data['PatientRadiologyResult']['diagnosis'] = $this->data['PatientRadiologyResult']['diagnosis'];
                        $this->data['PatientRadiologyResult']['icd_code'] = $this->data['PatientRadiologyResult']['icd_code'];

                        $date_ordered = explode('/', trim($this->data['PatientRadiologyResult']['date_ordered']));
                        if (count($date_ordered) == 3) {
                            $date_ordered = __date('Y-m-d', strtotime($date_ordered[2] . '-' . $date_ordered[0] . '-' . $date_ordered[1]));
                        }

                        $report_date = explode('/', trim($this->data['PatientRadiologyResult']['report_date']));
                        if (count($report_date) == 3) {
                            $report_date = __date('Y-m-d', strtotime($report_date[2] . '-' . $report_date[0] . '-' . $report_date[1]));
                        }


                        $this->data['PatientRadiologyResult']['date_ordered'] = $date_ordered;
                        $this->data['PatientRadiologyResult']['report_date'] = $report_date;
                        $this->data['PatientRadiologyResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['PatientRadiologyResult']['modified_user_id'] = $this->user_id;
                        $this->PatientRadiologyResult->create();
                        $this->PatientRadiologyResult->saveAudit('New');
                        $this->PatientRadiologyResult->save($this->data);
                        //$radiology_result_id = $this->PatientRadiologyResult->getLastInsertId();
                        /* App::import('Model','PatientActivities');
                          $PatientActivities= new PatientActivities();
                          $editlink = Router::url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_radiology' => 2, 'task' => 'edit', 'patient_id' => $patient_id, 'radiology_result_id' => $radiology_result_id), array('escape' => false));
                          $PatientActivities->addActivitiesItem($this->data['PatientRadiologyResult']['ordered_by_id'], $this->data['PatientRadiologyResult']['test_name'], "Radiology", "Outside Radiology", $this->data['PatientRadiologyResult']['status'], $patient_id, $radiology_result_id , $editlink); */


                        $attachment = trim($this->data['PatientRadiologyResult']['attachment']);
                        if ($attachment) {

                            if (file_exists($this->paths['temp'] . $attachment)) {

                                $this->paths['patient_encounter_radiology'] =
                                        $this->paths['patients'] . $patient_id . DS . 'radiology' . DS . '0' . DS;

                                UploadSettings::createIfNotExists($this->paths['patient_encounter_radiology']);

                                rename($this->paths['temp'] . $attachment, $this->paths['patient_encounter_radiology'] . $attachment);
                            }
                        }

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {

                        $date_ordered = explode('/', trim($this->data['PatientRadiologyResult']['date_ordered']));
                        if (count($date_ordered) == 3) {
                            $date_ordered = __date('Y-m-d', strtotime($date_ordered[2] . '-' . $date_ordered[0] . '-' . $date_ordered[1]));
                        }

                        $report_date = explode('/', trim($this->data['PatientRadiologyResult']['report_date']));
                        if (count($report_date) == 3) {
                            $report_date = __date('Y-m-d', strtotime($report_date[2] . '-' . $report_date[0] . '-' . $report_date[1]));
                        }


                        $this->data['PatientRadiologyResult']['date_ordered'] = $date_ordered;
                        $this->data['PatientRadiologyResult']['report_date'] = $report_date;


                        $this->PatientRadiologyResult->saveAudit('Update');
                        $this->PatientRadiologyResult->save($this->data);
                        /* App::import('Model','PatientActivities');
                          $PatientActivities= new PatientActivities();
                          $radiology_result_id = $this->data['PatientRadiologyResult']['radiology_result_id'] = $radiology_result_id;
                          $editlink = Router::url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_radiology' => 2, 'task' => 'edit', 'patient_id' => $patient_id, 'radiology_result_id' => $radiology_result_id), array('escape' => false));
                          $PatientActivities->addActivitiesItem($this->data['PatientRadiologyResult']['ordered_by_id'], $this->data['PatientRadiologyResult']['test_name'], "Radiology", "Outside Radiology", $this->data['PatientRadiologyResult']['status'], $patient_id, $radiology_result_id , $editlink); */

                        $attachment = trim($this->data['PatientRadiologyResult']['attachment']);
                        if ($attachment) {

                            if (file_exists($this->paths['temp'] . $attachment)) {
                                $this->paths['patient_id'] = $this->paths['patients'] . intval($patient_id) . DS;
                                UploadSettings::createIfNotExists($this->paths['patient_id']);

                                copy($this->paths['temp'] . $attachment, $this->paths['patient_id'] . $attachment);
                            }
                        }
                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $radiology_result_id = (isset($this->params['named']['radiology_result_id'])) ? $this->params['named']['radiology_result_id'] : "";
                        //echo $family_history_id;
                        $items = $this->PatientRadiologyResult->find(
                                'first', array(
                            'conditions' => array('PatientRadiologyResult.radiology_result_id' => $radiology_result_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientRadiologyResult']['radiology_result_id'];

                        foreach ($ids as $id) {
                            $this->PatientRadiologyResult->saveAudit('Delete');
                            $this->PatientRadiologyResult->delete($id, false);
                            /* App::import('Model','PatientActivities');
                              $PatientActivities= new PatientActivities();
                              $PatientActivities->deleteActivitiesItem($id, "Radiology", "Outside Radiology");
                             */
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $this->paginate['PatientRadiologyResult'] = array(
                        'conditions' => array('PatientRadiologyResult.patient_id' => $patient_id),
                        'order' => array('PatientRadiologyResult.modified_timestamp' => 'DESC')
                    );

                    $this->set('PatientRadiologyResult', $this->sanitizeHTML($this->paginate('PatientRadiologyResult')));

                    //$this->set('PatientRadiologyResult', $this->sanitizeHTML($this->paginate('PatientRadiologyResult', array('PatientRadiologyResult.patient_id' => $patient_id))));
                    $this->PatientRadiologyResult->saveAudit('View');
                }
        }
    }

    public function in_house_work_procedures() {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                        $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                        $this->EncounterPointOfCare->create();
                        $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();


                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['procedure_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['procedure_date_performed']));
                        $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->EncounterPointOfCare->find(
                                'first', array(
                            'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                            $this->EncounterPointOfCare->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);

                    if ($encounter_items) {
                        $this->paginate['EncounterPointOfCare'] = array(
                            'conditions' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Procedure'),
                            'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                        );
                    } else {
                        $this->paginate['EncounterPointOfCare'] = array(
                            'conditions' => array('EncounterPointOfCare.encounter_id' => null),
                        );
                    }
                    $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));

                    //$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Procedure'))));

                    $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                } break;
        }
    }

    public function procedures() {
        $this->loadModel("EncounterPlanProcedure");
        $this->loadModel("EncounterMaster");
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPlanProcedure']['date_ordered'] = __date("Y-m-d");
                        $this->data['EncounterPlanProcedure']['patient_id'] = $patient_id;
                        $this->data['EncounterPlanProcedure']['ordered_by_id'] = trim($this->data['EncounterPlanProcedure']['ordered_by_id']) ? $this->data['EncounterPlanProcedure']['ordered_by_id'] : 0;

                        $this->EncounterPlanProcedure->create();
                        $this->EncounterPlanProcedure->saveAudit('New');
                        $this->EncounterPlanProcedure->save($this->data);
                        $plan_procedures_id = $this->EncounterPlanProcedure->getLastInsertId();

                        if (isset($this->data['EncounterPlanProcedure']['print_save_add']) && $this->data['EncounterPlanProcedure']['print_save_add'] == 1) {
                            $this->Session->write('last_saved_id', $plan_procedures_id);
                        }
                        if (isset($this->data['EncounterPlanProcedure']['fax_save_add']) && $this->data['EncounterPlanProcedure']['fax_save_add'] == 1) {
                            $this->Session->write('last_saved_fax_id', $plan_procedures_id);
                        }
                        $editlink = Router::url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information', 'view_procedure' => 2, 'task' => 'edit', 'patient_id' => $patient_id, 'plan_procedures_id' => $plan_procedures_id), array('escape' => false));


                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $providers = $this->UserAccount->find('all', array(
                            'conditions' => array(
                                'UserAccount.role_id' => array(
                                    EMR_Roles::PHYSICIAN_ROLE_ID,
                                    EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID,
                                    EMR_Roles::NURSE_PRACTITIONER_ROLE_ID,
                                    EMR_Roles::REGISTERED_NURSE_ROLE_ID,
                                ),
                            ),
                            'fields' => array('CONCAT(UserAccount.firstname," ", UserAccount.lastname) as provider_name , user_id')
                                ));
                        $this->set('providers', $providers);
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPlanProcedure']['date_ordered'] = __date("Y-m-d");
                        $this->EncounterPlanProcedure->saveAudit('Update');
                        $this->EncounterPlanProcedure->save($this->data);
                        //App::import('Model','PatientActivities');
                        //$PatientActivities= new PatientActivities();
                        $plan_procedures_id = $this->data['EncounterPlanProcedure']['plan_procedures_id'];

                        $editlink = Router::url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information', 'view_procedure' => 2, 'task' => 'edit', 'patient_id' => $patient_id, 'plan_procedures_id' => $plan_procedures_id), array('escape' => false));
                        //$PatientActivities->addActivitiesItem($this->data['EncounterPlanProcedure']['ordered_by_id'], $this->data['EncounterPlanProcedure']['test_name'], "Procedure", "Outside Procedure", $this->data['EncounterPlanProcedure']['status'], $patient_id, $plan_procedures_id , $editlink);


                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $providers = $this->UserAccount->find('all', array(
                            'conditions' => array(
                                'UserAccount.role_id' => array(
                                    EMR_Roles::PHYSICIAN_ROLE_ID,
                                    EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID,
                                    EMR_Roles::NURSE_PRACTITIONER_ROLE_ID,
                                    EMR_Roles::REGISTERED_NURSE_ROLE_ID,
                                ),
                            ),
                            'fields' => array('CONCAT(UserAccount.firstname," ", UserAccount.lastname) as provider_name , user_id')
                                ));
                        $this->set('providers', $providers);
                        $plan_procedures_id = (isset($this->params['named']['plan_procedures_id'])) ? $this->params['named']['plan_procedures_id'] : "";
                        //echo $family_history_id;
                        $items = $this->EncounterPlanProcedure->find(
                                'first', array(
                            'conditions' => array('EncounterPlanProcedure.plan_procedures_id' => $plan_procedures_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPlanProcedure']['plan_procedures_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPlanProcedure->saveAudit('Delete');
                            $this->EncounterPlanProcedure->delete($id, false);
                            //App::import('Model','PatientActivities');
                            //$PatientActivities= new PatientActivities();
                            //$PatientActivities->deleteActivitiesItem($id, "Procedure", "Outside Procedure");

                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $this->paginate['EncounterPlanProcedure'] = array(
                        'conditions' => array('EncounterPlanProcedure.patient_id' => $patient_id),
                        'order' => array('EncounterPlanProcedure.modified_timestamp' => 'DESC')
                    );

                    $combine = $this->Session->read('UserAccount.assessment_plan') ? true : false;

                    if ($combine) {
                        $this->paginate['EncounterPlanProcedure']['group'] = array(
                            'EncounterPlanProcedure.encounter_id', 'EncounterPlanProcedure.test_name'
                        );

                        $this->paginate['EncounterPlanProcedure']['contain'] = array(
                            'EncounterMaster' => array(
                                'fields' => array('encounter_id', 'patient_id'),
                                'EncounterAssessment' => array(
                                    'fields' => array('diagnosis'),
                            )),
                        );
                    } else {
                        $this->paginate['EncounterPlanProcedure']['contain'] = array(
                            'EncounterMaster' => array(
                                'fields' => array('encounter_id', 'patient_id'),
                            ),
                        );
                    }
                    $this->set('combine', $combine);


                    $this->set('EncounterPlanProcedure', $this->sanitizeHTML($this->paginate('EncounterPlanProcedure')));

                    //$this->set('EncounterPlanProcedure', $this->sanitizeHTML($this->paginate('EncounterPlanProcedure', array('EncounterMaster.encounter_id' => $result))));
                    $this->EncounterPlanProcedure->saveAudit('View');
                } break;
        }
    }

    public function plan_labs() {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanLab");
        $this->loadModel("DirectoryLabFacility");
        $practice_settings = $this->Session->read("PracticeSetting");

        $labs_setup = $practice_settings['PracticeSetting']['labs_setup'];
        $this->set('labs_setup', $labs_setup);
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $plan_labs_id = (isset($this->params['named']['plan_labs_id'])) ? $this->params['named']['plan_labs_id'] : "";

        $lab_facility_items = $this->DirectoryLabFacility->find('all');
        $this->set('LabFacilityCount', count($lab_facility_items));

        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPlanLab']['patient_id'] = $patient_id;
                        $this->data['EncounterPlanLab']['encounter_id'] = 0;
                        $this->data['EncounterPlanLab']['diagnosis'] = $this->data['EncounterPlanLab']['reason'];
                        $this->data['EncounterPlanLab']['date_ordered'] = __date("Y-m-d");
                        $this->data['EncounterPlanLab']['modified_user_id'] = $this->user_id;
                        //$this->data['EncounterPlanLab']['ordered_by_id'] = $this->user_id;
                        $this->data['EncounterPlanLab']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->EncounterPlanLab->create();
                        $this->EncounterPlanLab->save($this->data);
                        $plan_labs_id = $this->EncounterPlanLab->getLastInsertId();


                        if (isset($this->data['EncounterPlanLab']['print_save_add']) && $this->data['EncounterPlanLab']['print_save_add'] == 1) {
                            $this->Session->write('last_saved__lab_id', $plan_labs_id);
                        }
                        if (isset($this->data['EncounterPlanLab']['fax_save_add']) && $this->data['EncounterPlanLab']['fax_save_add'] == 1) {
                            $this->Session->write('last_saved_fax_lab_id', $plan_labs_id);
                        }

                        $ret = array();

                        echo json_encode($ret);
                        exit;
                    } else {
                        $this->loadModel("UserGroup");
                        $providers = $this->UserAccount->find('all', array(
                            'conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, false)),
                            'fields' => array('CONCAT(UserAccount.firstname," ", UserAccount.lastname) as provider_name , user_id'),
                            'recursive' => -1,
                                ));
                        $this->set('providers', $providers);
                    }
                }break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPlanLab']['diagnosis'] = $this->data['EncounterPlanLab']['reason'];
                        $this->EncounterPlanLab->save($this->data);

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $this->loadModel("UserGroup");
                        $providers = $this->UserAccount->find('all', array(
                            'conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, false)),
                            'fields' => array('CONCAT(UserAccount.firstname," ", UserAccount.lastname) as provider_name , user_id'),
                            'recursive' => -1,
                                ));
                        $this->set('providers', $providers);
                        $this->set('providers', $providers);
                        $items = $this->EncounterPlanLab->find('first', array('conditions' => array('EncounterPlanLab.plan_labs_id' => $plan_labs_id)));
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPlanLab']['plan_labs_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPlanLab->delete($id, false);

                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {

                    $this->paginate['EncounterPlanLab'] = array(
                        'conditions' => array('OR' => array('EncounterPlanLab.patient_id' => $patient_id, 'EncounterMaster.patient_id' => $patient_id)),
                        'order' => array('EncounterPlanLab.modified_timestamp' => 'DESC')
                    );
                    $combine = $this->Session->read('UserAccount.assessment_plan') ? true : false;

                    if ($combine) {
                        $this->paginate['EncounterPlanLab']['group'] = array(
                            'EncounterPlanLab.encounter_id', 'EncounterPlanLab.test_name'
                        );

                        $this->paginate['EncounterPlanLab']['contain'] = array(
                            'EncounterMaster' => array(
                                'fields' => array('encounter_id', 'patient_id'),
                                'EncounterAssessment' => array(
                                    'fields' => array('diagnosis'),
                            )),
                        );
                    } else {

                        $this->paginate['EncounterPlanLab']['contain'] = array(
                            'EncounterMaster' => array(
                                'fields' => array('encounter_id', 'patient_id'),
                            ),
                        );
                    }
                    $this->set('combine', $combine);


                    $encounter_plan_labs = $this->paginate('EncounterPlanLab');
                    $this->set("encounter_plan_labs", $this->sanitizeHTML($encounter_plan_labs));
                }
        }
    }

    public function plan_labs_electronic() {
        $this->layout = "blank";
        $this->loadModel('PatientDemographic');

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        $mrn = $this->PatientDemographic->getPatientMRN($patient_id);
        $this->set("mrn", $mrn);
    }

    public function plan_radiology() {
        $this->layout = "blank";
        $this->loadModel("EncounterPlanRadiology");
        $this->loadModel("DirectoryLabFacility");

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $plan_radiology_id = (isset($this->params['named']['plan_radiology_id'])) ? $this->params['named']['plan_radiology_id'] : "";

        $lab_facility_items = $this->DirectoryLabFacility->find('all');
        $this->set('LabFacilityCount', count($lab_facility_items));


        $this->paths['patient_encounter_radiology'] =
                $this->paths['patients'] . $patient_id . DS . 'radiology' . DS . '0' . DS;

        UploadSettings::createIfNotExists($this->paths['patient_encounter_radiology']);


        switch ($task) {

            case 'download_file': {
                    $this->EncounterPlanRadiology->id = $plan_radiology_id;
                    $radiology = $this->EncounterPlanRadiology->read();


                    if ($radiology && $radiology['EncounterPlanRadiology']['file_upload']) {
                        $file = trim($radiology['EncounterPlanRadiology']['file_upload']);
                        $filename = explode(DIRECTORY_SEPARATOR, $file);
                        $filename = array_pop($filename);
                        $tmp = explode('_', $filename);
                        unset($tmp[0]);
                        $filename = implode('_', $tmp);

                        header("Content-Type: application/force-download");
                        header("Content-Type: application/octet-stream");
                        header("Content-Type: application/download");
                        header('Content-Disposition: attachment; filename="' . $filename . '"');
                        header("Cache-Control: no-cache, must-revalidate");
                        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                        readfile(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
                    }


                    exit();
                } break;

            case 'remove_file': {
                    if (isset($this->params['form']['delete']) && $plan_radiology_id) {
                        $this->EncounterPlanRadiology->id = $plan_radiology_id;
                        $radiology = $this->EncounterPlanRadiology->read();

                        if ($radiology) {
                            $source = WWW_ROOT . ltrim($radiology['EncounterPlanRadiology']['file_upload'], DS);
                            @unlink($source);

                            $radiology['EncounterPlanRadiology']['file_upload'] = '';
                            $this->EncounterPlanRadiology->save($radiology);
                        }
                    }


                    die('Ok');
                } break;

            case "addnew": {
                    if (!empty($this->data)) {

                        $this->data['EncounterPlanRadiology']['file_upload'] = trim($this->data['EncounterPlanRadiology']['file_upload']);

                        // There were changes in uploaded file
                        if ($this->data['EncounterPlanRadiology']['file_upload'] && $this->data['EncounterPlanRadiology']['file_upload'] !== $this->data['EncounterPlanRadiology']['old_file_upload']) {

                            $source = WWW_ROOT . ltrim($this->data['EncounterPlanRadiology']['file_upload'], DS);
                            $fname = basename($source);
                            $target = $this->paths['patient_encounter_radiology'] . $fname;
                            rename($source, $target);
                            $this->data['EncounterPlanRadiology']['file_upload'] = UploadSettings::toURL($target);
                        }

                        $this->data['EncounterPlanRadiology']['patient_id'] = $patient_id;
                        $this->data['EncounterPlanRadiology']['encounter_id'] = 0;
                        $this->data['EncounterPlanRadiology']['diagnosis'] = $this->data['EncounterPlanRadiology']['reason'];
                        $this->data['EncounterPlanRadiology']['date_ordered'] = __date("Y-m-d");
                        $this->data['EncounterPlanRadiology']['modified_user_id'] = $this->user_id;
                        $this->data['EncounterPlanRadiology']['ordered_by_id'] = trim($this->data['EncounterPlanRadiology']['ordered_by_id']) ? $this->data['EncounterPlanRadiology']['ordered_by_id'] : 0;
                        $this->data['EncounterPlanRadiology']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->EncounterPlanRadiology->create();
                        $this->EncounterPlanRadiology->save($this->data);
                        $plan_radiology_id = $this->EncounterPlanRadiology->getLastInsertId();

                        if (isset($this->data['EncounterPlanRadiology']['print_save_add']) && $this->data['EncounterPlanRadiology']['print_save_add'] == 1) {
                            $this->Session->write('last_saved_id_radiology', $plan_radiology_id);
                        }
                        if (isset($this->data['EncounterPlanRadiology']['fax_save_add']) && $this->data['EncounterPlanRadiology']['fax_save_add'] == 1) {
                            $this->Session->write('last_saved_id_radiology_for_fax', $plan_radiology_id);
                        }
                        $ret = array();

                        echo json_encode($ret);
                        exit;
                    } else {
                        $providers = $this->UserAccount->find('all', array(
                            'conditions' => array(
                                'UserAccount.role_id' => array(
                                    EMR_Roles::PHYSICIAN_ROLE_ID,
                                    EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID,
                                    EMR_Roles::NURSE_PRACTITIONER_ROLE_ID,
                                    EMR_Roles::REGISTERED_NURSE_ROLE_ID,
                                ),
                            ),
                            'fields' => array('CONCAT(UserAccount.firstname," ", UserAccount.lastname) as provider_name , user_id')
                                ));
                        $this->set('providers', $providers);
                    }
                }break;
            case "edit": {
                    if (!empty($this->data)) {
                        $plan_radiology_id = (isset($this->params['named']['plan_radiology_id'])) ? $this->params['named']['plan_radiology_id'] : "";
                        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

                        $this->data['EncounterPlanRadiology']['file_upload'] = trim($this->data['EncounterPlanRadiology']['file_upload']);

                        // There were changes in uploaded file
                        if ($this->data['EncounterPlanRadiology']['file_upload'] && $this->data['EncounterPlanRadiology']['file_upload'] !== $this->data['EncounterPlanRadiology']['old_file_upload']) {

                            $source = WWW_ROOT . ltrim($this->data['EncounterPlanRadiology']['file_upload'], DS);
                            $fname = basename($source);
                            $target = $this->paths['patient_encounter_radiology'] . $fname;
                            rename($source, $target);
                            $this->data['EncounterPlanRadiology']['file_upload'] = UploadSettings::toURL($target);
                        }

                        $this->data['EncounterPlanRadiology']['patient_id'] = $patient_id;
                        $this->data['EncounterPlanRadiology']['plan_radiology_id'] = $plan_radiology_id;
                        $this->data['EncounterPlanRadiology']['diagnosis'] = $this->data['EncounterPlanRadiology']['reason'];
                        $this->EncounterPlanRadiology->save($this->data);

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $providers = $this->UserAccount->find('all', array(
                            'conditions' => array(
                                'UserAccount.role_id' => array(
                                    EMR_Roles::PHYSICIAN_ROLE_ID,
                                    EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID,
                                    EMR_Roles::NURSE_PRACTITIONER_ROLE_ID,
                                    EMR_Roles::REGISTERED_NURSE_ROLE_ID,
                                ),
                            ),
                            'fields' => array('CONCAT(UserAccount.firstname," ", UserAccount.lastname) as provider_name , user_id')
                                ));
                        $this->set('providers', $providers);

                        $items = $this->EncounterPlanRadiology->find('first', array('conditions' => array('EncounterPlanRadiology.plan_radiology_id' => $plan_radiology_id)));
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPlanRadiology']['plan_radiology_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPlanRadiology->delete($id, false);

                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {

                    $this->paginate['EncounterPlanRadiology'] = array(
                        'conditions' => array('OR' => array('EncounterPlanRadiology.patient_id' => $patient_id, 'EncounterMaster.patient_id' => $patient_id)),
                        'order' => array('EncounterPlanRadiology.modified_timestamp' => 'DESC')
                    );

                    $combine = $this->Session->read('UserAccount.assessment_plan') ? true : false;

                    if ($combine) {
                        $this->paginate['EncounterPlanRadiology']['group'] = array(
                            'EncounterPlanRadiology.encounter_id', 'EncounterPlanRadiology.procedure_name'
                        );

                        $this->paginate['EncounterPlanRadiology']['contain'] = array(
                            'EncounterMaster' => array(
                                'fields' => array('encounter_id', 'patient_id'),
                                'EncounterAssessment' => array(
                                    'fields' => array('diagnosis'),
                            )),
                        );
                    } else {
                        $this->paginate['EncounterPlanRadiology']['contain'] = array(
                            'EncounterMaster' => array(
                                'fields' => array('encounter_id', 'patient_id'),
                            ),
                        );
                    }
                    $this->set('combine', $combine);





                    $this->set('encounter_plan_radiology', $this->sanitizeHTML($this->paginate('EncounterPlanRadiology')));

                    /* $this->set('encounter_plan_radiology', $this->sanitizeHTML($this->paginate('EncounterPlanRadiology', 
                      array('OR'=>array(

                      'EncounterPlanRadiology.patient_id' => $patient_id, 'EncounterMaster.patient_id' => $patient_id)
                      )))); */
                }
        }
    }

    public function in_house_work_immunizations() {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                        $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                        $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                        $this->data['EncounterPointOfCare']['vaccine_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['vaccine_date_performed']));
                        $this->data['EncounterPointOfCare']['vaccine_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['vaccine_expiration_date']));

                        $this->data['EncounterPointOfCare']['administered_units'] = ($this->data['EncounterPointOfCare']['administered_units'] != "") ? $this->data['EncounterPointOfCare']['administered_units'] : 1;
                        $this->EncounterPointOfCare->create();
                        $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Immunization');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['vaccine_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['vaccine_date_performed']));
                        $this->data['EncounterPointOfCare']['vaccine_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['vaccine_expiration_date']));
                        $this->data['EncounterPointOfCare']['administered_units'] = ($this->data['EncounterPointOfCare']['administered_units'] != "") ? $this->data['EncounterPointOfCare']['administered_units'] : 1;
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Immunization');
                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->EncounterPointOfCare->find(
                                'first', array(
                            'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Immunization');
                            $this->EncounterPointOfCare->delete($id, false);

                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            case "mark_none": {
                    if ($this->data['EncounterPointOfCare']['mark_none'] == "true") {
                        $this->data['EncounterPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
                        $this->data['EncounterPointOfCare']['order_type'] = "Marked as None";
                        $this->data['EncounterPointOfCare']['immunization_none'] = $user['user_id'];
                        $this->EncounterPointOfCare->create();
                        $this->EncounterPointOfCare->save($this->data);
                    } else {
                        $this->EncounterPointOfCare->deleteAll(array("AND" => array("EncounterPointOfCare.encounter_id" => $this->params['named']['encounter_id'], "order_type" => "Marked as None", "immunization_none" => $user['user_id'])), false);
                    }
                } break;
            case "review_by": {
                    if ($this->data['EncounterPointOfCare']['review_by'] == "true") {
                        $this->data['EncounterPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
                        $this->data['EncounterPointOfCare']['order_type'] = "Reviewed by";
                        $this->data['EncounterPointOfCare']['immunization_reviewed'] = $user['user_id'];
                        $this->data['EncounterPointOfCare']['immunization_reviewed_by'] = $user['firstname'] . ' ' . $user['lastname'];
                        $this->data['EncounterPointOfCare']['immunization_reviewed_time'] = __date("Y-m-d, H:i:s");
                        $this->EncounterPointOfCare->create();
                        $this->EncounterPointOfCare->save($this->data);
                    } else {
                        $this->EncounterPointOfCare->deleteAll(array("AND" => array("EncounterPointOfCare.encounter_id" => $this->params['named']['encounter_id'], "order_type" => "Reviewed by", "immunization_reviewed" => $user['user_id'])), false);
                    }
                } break;
            default: {

                    $this->paginate['EncounterPointOfCare'] = array(
                        'conditions' => array('EncounterPointOfCare.order_type' => 'Immunization', 'EncounterPointOfCare.patient_id' => $patient_id),
                        'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                    );

                    $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));

                    /* $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', 
                      array(
                      'EncounterPointOfCare.order_type' => 'Immunization',
                      'EncounterPointOfCare.patient_id' => $patient_id
                      )))); */
                    /* $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
                      var_dump($encounter_items);
                      $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('OR' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization'),'AND' => array('EncounterPointOfCare.encounter_id' =>$encounter_id
                      )))));
                      var_dump(EncounterPointOfCare);
                      // var_dump($this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization'))));
                      $mark_none = $this->EncounterPointOfCare->find(
                      'first',
                      array(
                      'conditions' => array('AND' => array('EncounterMaster.encounter_id' =>$encounter_items, 'EncounterPointOfCare.order_type' => 'Marked as None', 'immunization_none' => $user['user_id']))
                      )
                      );
                      if (!empty($mark_none))
                      {
                      $this->set('MarkedNone', $this->sanitizeHTML($mark_none));
                      }

                      $review_by = $this->EncounterPointOfCare->find(
                      'first',
                      array(
                      'conditions' => array('AND' => array('EncounterMaster.encounter_id' => $encounter_items, 'order_type' => 'Reviewed by', 'immunization_reviewed' => $user['user_id']))
                      )
                      );
                      if (!empty($review_by))
                      {
                      $this->set('ReviewedBy', $this->sanitizeHTML($review_by));
                      } */

                    $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Immunization');
                } break;
        }
    }

    /**
     * To show the immunizations chart
     * for a patient
     * 
     */
    public function immunizations_chart() {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel('PatientDemographic');
        $patient_info = $this->PatientDemographic->find('first', array(
            'fields' => 'dob', 'conditions' => array('patient_id' => $patient_id, 'dob !=' => ''), 'recursive' => -1
                ));
        if (empty($patient_info))
            exit('Patient\'s DOB should not be Empty');
        $dob_timestamp = __date("Y-m-d", strtotime($patient_info['PatientDemographic']['dob']));
        $month_intervals = array(
            1 => array('month' => 'birth', 'label' => 'Birth'),
            2 => array('month' => array(1, 2), 'label' => '1 month'),
            3 => array('month' => array(2, 3), 'label' => '2 months'),
            4 => array('month' => array(4, 5), 'label' => '4 months'),
            5 => array('month' => array(6, 7), 'label' => '6 months'),
            6 => array('month' => array(12, 13), 'label' => '12 months'),
            7 => array('month' => array(15, 16), 'label' => '15 months'),
            8 => array('month' => array(18, 19), 'label' => '18 months'),
            9 => array('month' => array(19, 24), 'label' => '19-23 months'),
            10 => array('month' => array(24, 48), 'label' => '2-3 years'),
            11 => array('month' => array(48, 84), 'label' => '4-6 years'),
            12 => array('month' => array(84, 132), 'label' => '7-10 years'),
            13 => array('month' => array(132, 156), 'label' => '11-12 years'),
            14 => array('month' => array(156, 228), 'label' => '13-18 years'),
        );
        foreach ($month_intervals as $key => $month_interval) {
            if ($month_interval['month'] == 'birth') {
                $month_intervals[$key]['schedule_time'] = __date("Y-m-d", strtotime($dob_timestamp . "+1 week"));
            } else if (is_array($month_interval['month'])) {
                $month_intervals[$key]['schedule_time'][0] = __date("Y-m-d", strtotime($dob_timestamp . "+{$month_interval['month'][0]} month"));
                $month_intervals[$key]['schedule_time'][1] = __date("Y-m-d", strtotime($dob_timestamp . "+{$month_interval['month'][1]} month"));
            }
        }
        //pr($month_intervals);
        $immunizations = array(
            array('label' => 'Hepatitis B', 'cvx_code' => array('08', 42, 43, 44, 45, 51, 104, 110), 'columns' => array(1, 2, 3, 5, 6, 7, 8, 12, 13, 14), 'highlight1' => array(2, 3, 5, 6, 7, 8, 13), 'highlight2' => array(), 'highlight3' => array(14)),
            array('label' => 'Rotavirus', 'cvx_code' => array(116, 119), 'columns' => array(3, 4, 5), 'highlight1' => array(), 'highlight2' => array(), 'highlight3' => array()),
            array('label' => 'Diphtheria, Tetanus, Pertussis', 'cvx_code' => array(20, 50, 106, 110, 120, 130, 115), 'columns' => array(3, 4, 5, 7, 8, 11, 13, 14), 'highlight1' => array(7, 8, 11, 13), 'highlight2' => array(), 'highlight3' => array(14)),
            array('label' => 'Haemophilus influenzae type b', 'cvx_code' => array(17, 46, 47, 48, 49), 'columns' => array(3, 4, 5, 6, 7), 'highlight1' => array(6, 7), 'highlight2' => array(), 'highlight3' => array()),
            array('label' => 'Pneumococcal', 'cvx_code' => array(100, 133), 'columns' => array(3, 4, 5, 6, 7, 10, 11, 12, 13, 14), 'highlight1' => array(6, 7), 'highlight2' => array(10, 11), 'highlight3' => array()),
            array('label' => 'Inactivated Poliovirus', 'cvx_code' => array(10, 110, 120, 130), 'columns' => array(3, 4, 5, 6, 7, 8, 11, 12, 13, 14), 'highlight1' => array(5, 6, 7, 8, 11), 'highlight2' => array(), 'highlight3' => array()),
            array('label' => 'Influenza', 'cvx_code' => array(144, 140, 141, 16, 111, 135, 88), 'columns' => array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14), 'highlight1' => array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14), 'highlight2' => array(), 'highlight3' => array()),
            array('label' => 'Measles, Mumps, Rubella', 'cvx_code' => array('03', '05', '06', '07'), 'columns' => array(6, 7, 11, 12, 13, 14), 'highlight1' => array(6, 7, 11), 'highlight2' => array(), 'highlight3' => array(12, 13, 14)),
            array('label' => 'Varicella', 'cvx_code' => array(21), 'columns' => array(6, 7, 11, 12, 13, 14), 'highlight1' => array(6, 7, 11), 'highlight2' => array(), 'highlight3' => array(12, 13, 14)),
            array('label' => 'Hepatitis A', 'cvx_code' => array(52, 83, 84, 85, 104), 'columns' => array(6, 7, 8, 9, 10, 11, 12, 13, 14), 'highlight1' => array(6, 7, 8, 9), 'highlight2' => array(10, 11, 12, 13, 14), 'highlight3' => array()),
            array('label' => 'Meningococcal', 'cvx_code' => array(32, 114, 136), 'columns' => array(10, 11, 12, 13, 14), 'highlight1' => array(13), 'highlight2' => array(10, 11, 12), 'highlight3' => array(14)),
            array('label' => 'Human Papillomavirus', 'cvx_code' => array(62, 118), 'columns' => array(13, 14), 'highlight1' => array(13), 'highlight2' => array(), 'highlight3' => array(14)),
        );
        $chart = array();
        foreach ($immunizations as $immunization) {
            $data = $this->EncounterPointOfCare->getPatientImmu($patient_id, $immunization['cvx_code']);
            $dates = Set::extract('n/EncounterPointOfCare/vaccine_date_performed', $data); //pr($dates);
            $tmpChart = array();
            foreach ($month_intervals as $key => $month_interval) {
                if (in_array($key, $immunization['columns'])) {
                    foreach ($dates as $date) {
                        $date1 = new DateTime($date);
                        if ($month_interval['month'] == 'birth') {
                            $date2 = new DateTime($month_interval['schedule_time']);
                            $interval = $date1->diff($date2);
                            if ($interval->y == 0 && $interval->m == 0 && $interval->d <= 7) {
                                $tmpChart[$key] = 'Valid'; //pr($interval);	
                                break;
                            }
                        } else if (is_array($month_interval['month'])) {
                            $firstDate1 = strtotime($month_interval['schedule_time'][0]);
                            $firstDate2 = strtotime($month_interval['schedule_time'][1]);
                            $checkDate = strtotime($date);
                            if ($checkDate >= $firstDate1 && $checkDate < $firstDate2) {
                                $tmpChart[$key] = 'Valid';
                                break;
                            }
                        }
                    }
                    if (!isset($tmpChart[$key])) {
                        $tmpChart[$key] = 'Missing';
                    }
                } else {
                    $tmpChart[$key] = 'blank';
                }
            }
            $chart[] = array('label' => $immunization['label'], 'data' => $tmpChart, 'highlight1' => $immunization['highlight1'], 'highlight2' => $immunization['highlight2'], 'highlight3' => $immunization['highlight3']);
        }
        //pr($chart);
        $this->Set(compact('month_intervals', 'chart'));
    }

    public function immunizations_record() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        $this->loadModel("PatientDemographic");
        $demographics = $this->PatientDemographic->getPatient($patient_id);
        $this->set("demographics", (object) $demographics);

        $this->set('admin_path', $this->url_abs_paths['administration']);

        $this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $provider = $PracticeProfile['PracticeProfile'];
        $this->set("provider", (object) $provider);

        $this->loadModel('ScheduleCalendar');
        $schedule = $this->ScheduleCalendar->find('first', array(
            'fields' => array('PracticeLocation.*'),
            'conditions' => array('ScheduleCalendar.patient_id' => $patient_id),
            'order' => array('ScheduleCalendar.date' => 'DESC', 'ScheduleCalendar.starttime' => 'DESC')
                ));

        $this->set('location', $schedule['PracticeLocation']);

        $this->loadModel("EncounterPointOfCare");
        $patient_immunizations_items = $this->EncounterPointOfCare->find('all', array('conditions' => array('order_type' => 'Immunization', 'EncounterPointOfCare.patient_id' => $patient_id), 'order' => array('vaccine_name' => 'ASC')));
        $this->set("patient_immunizations_items", $patient_immunizations_items);

        $report = $this->render(null, null, 'immunizations_record');

        App::import('Helper', 'Html');
        $html = new HtmlHelper();

        $url = $this->paths['temp'];
        $url = str_replace('//', '/', $url);

        $pdffile = 'patient_' . $patient_id . '_immunizations_record.pdf';

        //format report, by removing hide text
        $reportmod = preg_replace('/(<span class="hide_for_print">.+?)+(<\/span>)/i', '', $report);

        //PDF file creation
        //site::write(pdfReport::generate($reportmod, $url . $pdffile), $url . $pdffile);
        // Instead of writing a pdf file, just right the html output for later retrieval;
        $tmp_file = 'patient_' . $patient_id . '_immunizations_record.tmp';
        site::write($reportmod, $url . $tmp_file);

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        if ($task == "get_report_pdf") {
            // Get path were files are being saved/read
            $targetPath = str_replace('//', '/', $this->paths['temp']);

            // Html version file
            $tmp_file = 'patient_' . $patient_id . '_immunizations_record.tmp';

            // PDF file
            $file = 'patient_' . $patient_id . '_immunizations_record.pdf';

            $targetFile = $targetPath . $file;

            // Read contents of report
            $report = file_get_contents($targetPath . $tmp_file);

            // Write pdf
            site::write(pdfReport::generate($report, "landscape"), $targetFile);

            if (!is_file($targetFile)) {
                die("Invalid File: does not exist");
            }

            header('Content-Type: application/octet-stream; name="' . $file . '"');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Accept-Ranges: bytes');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-transfer-encoding: binary');
            header('Content-length: ' . @filesize($targetFile));
            @readfile($targetFile);
            exit;
        }
    }

    public function in_house_work_injections() {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                        $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                        $this->data['EncounterPointOfCare']['injection_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_date_performed']));
                        $this->data['EncounterPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_expiration_date']));

                        $this->data['EncounterPointOfCare']['injection_unit'] = ($this->data['EncounterPointOfCare']['injection_unit'] != "") ? $this->data['EncounterPointOfCare']['injection_unit'] : 1;
                        $this->EncounterPointOfCare->create();
                        $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Injection');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['injection_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_date_performed']));
                        $this->data['EncounterPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_expiration_date']));
                        $this->data['EncounterPointOfCare']['injection_unit'] = ($this->data['EncounterPointOfCare']['injection_unit'] != "") ? $this->data['EncounterPointOfCare']['injection_unit'] : 1;
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Injection');
                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->EncounterPointOfCare->find(
                                'first', array(
                            'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Injection');
                            $this->EncounterPointOfCare->delete($id, false);

                            $ret['delete_count']++;
                        }

                        if ($ret['delete_count'] > 0) {
                            $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Injection');
                        }
                    }

                    echo json_encode($ret);
                    exit;
                } break;
            /* case "mark_none":
              {
              if ($this->data['EncounterPointOfCare']['mark_none'] == "true")
              {
              $this->data['EncounterPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
              $this->data['EncounterPointOfCare']['order_type'] = "Marked as None";
              $this->data['EncounterPointOfCare']['injection_none'] = $user['user_id'];
              $this->EncounterPointOfCare->create();
              $this->EncounterPointOfCare->save($this->data);
              }
              else
              {
              $this->EncounterPointOfCare->deleteAll(array("AND" => array("EncounterPointOfCare.encounter_id" => $this->params['named']['encounter_id'], "order_type" => "Marked as None", "injection_none" => $user['user_id'])), false);
              }
              }
              break; */

            /* case "review_by":
              {
              if ($this->data['EncounterPointOfCare']['review_by'] == "true")
              {
              $this->data['EncounterPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
              $this->data['EncounterPointOfCare']['order_type'] = "Reviewed by";
              $this->data['EncounterPointOfCare']['injection_reviewed'] = $user['user_id'];
              $this->data['EncounterPointOfCare']['injection_reviewed_by'] = $user['firstname'].' '.$user['lastname'];

              $this->data['EncounterPointOfCare']['injection_reviewed_time'] = __date("Y-m-d, H:i:s");
              $this->EncounterPointOfCare->create();
              $this->EncounterPointOfCare->save($this->data);
              }
              else
              {
              $this->EncounterPointOfCare->deleteAll(array("AND" => array("EncounterPointOfCare.encounter_id" => $this->params['named']['encounter_id'], "order_type" => "Reviewed by", "injection_reviewed" => $user['user_id'])), false);
              }
              } break; */
            default: {
                    $this->paginate['EncounterPointOfCare'] = array(
                        'conditions' => array('EncounterPointOfCare.order_type' => 'Injection', 'EncounterPointOfCare.patient_id' => $patient_id),
                        'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                    );

                    $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));

                    /* $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', 
                      array(
                      'EncounterPointOfCare.order_type' => 'Injection',
                      'EncounterPointOfCare.patient_id' => $patient_id
                      )))); */

                    //$encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
                    // $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Injection'))));
                    /* $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('OR' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Injection'),'AND' => array('EncounterPointOfCare.patient_id' =>$patient_id
                      ))))); */

                    /* $mark_none = $this->EncounterPointOfCare->find(
                      'first',
                      array(
                      'conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $this->params['named']['encounter_id'], 'order_type' => 'Marked as None', 'injection_none' => $user['user_id']))
                      )
                      );
                      if (count($mark_none))
                      {
                      $this->set('MarkedNone', $this->sanitizeHTML($mark_none));
                      }

                      $review_by = $this->EncounterPointOfCare->find(
                      'first',
                      array(
                      'conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $this->params['named']['encounter_id'], 'order_type' => 'Reviewed by', 'injection_reviewed' => $user['user_id']))
                      )
                      );
                      if (count($review_by))
                      {
                      $this->set('ReviewedBy', $this->sanitizeHTML($review_by));
                      } */

                    $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Injection');
                } break;
        }
    }

    public function in_house_work_meds() {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $this->loadModel("Unit");

        $user = $this->Session->read('UserAccount');
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $this->set("units", $this->Unit->find('all'));

        switch ($task) {
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPointOfCare']['drug_date_given'] = __date("Y-m-d H:i:s", strtotime($this->data['EncounterPointOfCare']['drug_date_given'] . ' ' . $this->data['EncounterPointOfCare']['drug_given_time'] . ':00'));

                        $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Medication List - Point of Care');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->EncounterPointOfCare->find(
                                'first', array(
                            'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Medication List - Point of Care');
                            $this->EncounterPointOfCare->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);

                    if ($encounter_items) {
                        $this->paginate['EncounterPointOfCare'] = array(
                            'conditions' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds'),
                            'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                        );
                    } else {
                        $this->paginate['EncounterPointOfCare'] = array(
                            'conditions' => array('EncounterPointOfCare.encounter_id' => null),
                        );
                    }
                    $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));


                    //$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds'))));
                    $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Medication List - Point of Care');
                } break;
        }
    }

    public function in_house_work_supplies() {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");

        $user = $this->Session->read('UserAccount');
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {

                        $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                        $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                        $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                        $this->EncounterPointOfCare->create();
                        $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Supplies - Point of Care');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Supplies - Point of Care');
                        $this->EncounterPointOfCare->save($this->data);
                        $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->EncounterPointOfCare->find(
                                'first', array(
                            'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Supplies - Point of Care');
                            $this->EncounterPointOfCare->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
            default: {
                    $this->paginate['EncounterPointOfCare'] = array(
                        'conditions' => array('EncounterPointOfCare.patient_id' => $patient_id, 'EncounterPointOfCare.order_type' => 'Supplies'),
                        'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                    );

                    $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));
                    $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Supplies - Point of Care');
                } break;
        }
    }

    public function health_maintenance_plans() {
        $this->loadModel("EncounterPlanHealthMaintenanceEnrollment");
        $this->layout = "blank";

        $this->EncounterPlanHealthMaintenanceEnrollment->patientExecute($this);
    }

    public function load_vitals() {
        $this->loadModel("EncounterVital");
        $this->layout = 'empty';
        $patient_id = isset($this->params['named']['patient_id']) ? $this->params['named']['patient_id'] : null;
        $this->EncounterVital->patientData($this, $patient_id);
    }

    public function load_custom_plans() {
        $this->loadModel("EncounterPlanCustom");
        $this->layout = 'empty';
        $patient_id = isset($this->params['named']['patient_id']) ? $this->params['named']['patient_id'] : null;

        $this->loadModel('PracticePlanSection');
        $hasTreatmentPlans = $this->PracticePlanSection->hasTreatmentPlans();
        $this->set(compact('hasTreatmentPlans'));
        $this->EncounterPlanCustom->patientData($this, $patient_id);
    }

    public function load_treatment_plans() {
        $this->loadModel("EncounterPlanTreatment");
        $this->layout = 'empty';
        $patient_id = isset($this->params['named']['patient_id']) ? $this->params['named']['patient_id'] : null;

        $this->loadModel('PracticePlanSection');
        $hasCustomPlans = $this->PracticePlanSection->getCustomSections();
        $this->set(compact('hasCustomPlans'));
        $this->EncounterPlanTreatment->patientData($this, $patient_id);
    }

    public function patient_reminders() {
        $this->loadModel("PatientReminder");
        $this->layout = "blank";

        $this->PatientReminder->patientExecute($this);
    }

    public function referrals() {
        $this->loadModel("EncounterPlanReferral");
        $this->loadModel("EncounterMaster");
        $this->loadModel("DirectoryReferralList");
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        switch ($task) {

            case "addnew": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPlanReferral']['date_ordered'] = data::formatDateToStandard($this->__global_date_format, $this->data['EncounterPlanReferral']['date_ordered']);
                        $this->data['EncounterPlanReferral']['patient_id'] = $patient_id;
                        $this->data['EncounterPlanReferral']['modified_timestamp'] = __date("Y-m-d H:i:s");

                        if (isset($this->data['EncounterPlanReferral']['encounter_id']) && $this->data['EncounterPlanReferral']['encounter_id'] != 0) {
                            $this->data['EncounterPlanReferral']['visit_summary'] = 1;
                        }
                        $this->EncounterPlanReferral->create();
                        $this->EncounterPlanReferral->saveAudit('New');
                        $this->EncounterPlanReferral->save($this->data);

                        $plan_referral_id = $this->EncounterPlanReferral->getLastInsertId();

                        //visit summary attached is selected then only save related information
                        if (isset($this->data['EncounterPlanReferral']['encounter_id']) && $this->data['EncounterPlanReferral']['encounter_id'] != 0) {
                            $this->EncounterPlanReferral->getRelatedInfo($plan_referral_id);
                        }



                        if (isset($this->data['EncounterPlanReferral']['print_save_add']) && $this->data['EncounterPlanReferral']['print_save_add'] == 1) {
                            $this->Session->write('last_saved_id_referral', $plan_referral_id);
                            //$this->Session->write('last_encounter_id', $encounter_id);
                        }
                        if (isset($this->data['EncounterPlanReferral']['fax_save_add']) && $this->data['EncounterPlanReferral']['fax_save_add'] == 1) {
                            $this->Session->write('last_saved_id_referral_fax', $plan_referral_id);
                        }

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {

                        $this->EncounterMaster->virtualFields = array(
                            'location_name' => 'location_name',
                            'firstname' => 'firstname'
                        );
                        $this->EncounterMaster->hasMany['EncounterAssessment']['order'] = array('EncounterAssessment.diagnosis' => 'ASC');
                        $this->paginate['EncounterMaster'] = array(
                            'limit' => 20,
                            'conditions' => array('EncounterMaster.patient_id' => $patient_id),
//array('AND' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.encounter_status' => array('Closed', 'Open'))),
                            'order' => array('EncounterMaster.encounter_date' => 'DESC'),
                            'fields' => array('`EncounterMaster`.`encounter_id`', '`EncounterMaster`.`patient_id`', '`EncounterMaster`.`calendar_id`', '`EncounterMaster`.`encounter_date`', '`Provider`.`firstname`', '`Provider`.`lastname`', 'PracticeLocation.location_name', /* 'EncounterAssessment.diagnosis' , */'`EncounterMaster`.`encounter_status`', 'ScheduleCalendar.visit_type'),
                            'joins' => array(
                                /*
                                  array(
                                  'table' => 'encounter_assessment',
                                  'alias' => 'EncounterAssessment',
                                  'type' => 'left',
                                  'conditions' => array(
                                  'EncounterMaster.encounter_id = EncounterAssessment.encounter_id'
                                  )
                                  ),
                                 */
                                array(
                                    'table' => 'schedule_calendars',
                                    'alias' => 'ScheduleCalendar',
                                    'type' => 'inner',
                                    'conditions' => array(
                                        'EncounterMaster.calendar_id = ScheduleCalendar.calendar_id'
                                    )
                                ),
                                array(
                                    'table' => 'practice_locations',
                                    'alias' => 'PracticeLocation',
                                    'type' => 'inner',
                                    'conditions' => array(
                                        'PracticeLocation.location_id = ScheduleCalendar.location'
                                    )
                                ),
                                array(
                                    'table' => 'user_accounts',
                                    'alias' => 'Provider',
                                    'type' => 'inner',
                                    'conditions' => array(
                                        'Provider.user_id = ScheduleCalendar.provider_id'
                                    )
                                )
                            )
                        );
                        $this->set('pastvisit_items', $this->sanitizeHTML($this->paginate('EncounterMaster')));
                    }
                } break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->data['EncounterPlanReferral']['date_ordered'] = data::formatDateToStandard($this->__global_date_format, $this->data['EncounterPlanReferral']['date_ordered']);

                        if (isset($this->data['EncounterPlanReferral']['encounter_id']) && $this->data['EncounterPlanReferral']['encounter_id'] != 0) {
                            $this->data['EncounterPlanReferral']['visit_summary'] = 1;
                        }
                        $this->EncounterPlanReferral->saveAudit('Update');
                        $this->EncounterPlanReferral->save($this->data);

                        $plan_referral_id = $this->data['EncounterPlanReferral']['plan_referrals_id'];
                        //$encounter_id = $this->data['EncounterPlanReferral']['encounter_id'];	
                        if (isset($this->data['EncounterPlanReferral']['print_edit_add'])) {
                            $this->Session->write('last_edited_id_referral', $plan_referral_id);
                        }

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {

                        $plan_referrals_id = (isset($this->params['named']['plan_referrals_id'])) ? $this->params['named']['plan_referrals_id'] : "";
                        //echo $family_history_id;
                        $items = $this->EncounterPlanReferral->find(
                                'first', array(
                            'conditions' => array('EncounterPlanReferral.plan_referrals_id' => $plan_referrals_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                        $this->EncounterMaster->virtualFields = array(
                            'location_name' => 'location_name',
                            'firstname' => 'firstname'
                        );
                        $this->EncounterMaster->hasMany['EncounterAssessment']['order'] = array('EncounterAssessment.diagnosis' => 'ASC');
                        $this->paginate['EncounterMaster'] = array(
                            'limit' => 20,
                            'conditions' => array('EncounterMaster.patient_id' => $patient_id),
//array('AND' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.encounter_status' => array('Closed', 'Open'))),
                            'order' => array('EncounterMaster.encounter_date' => 'DESC'),
                            'fields' => array('`EncounterMaster`.`encounter_id`', '`EncounterMaster`.`patient_id`', '`EncounterMaster`.`calendar_id`', '`EncounterMaster`.`encounter_date`', '`Provider`.`firstname`', '`Provider`.`lastname`', 'PracticeLocation.location_name', /* 'EncounterAssessment.diagnosis' , */'`EncounterMaster`.`encounter_status`', 'ScheduleCalendar.visit_type'),
                            'joins' => array(
                                /*
                                  array(
                                  'table' => 'encounter_assessment',
                                  'alias' => 'EncounterAssessment',
                                  'type' => 'left',
                                  'conditions' => array(
                                  'EncounterMaster.encounter_id = EncounterAssessment.encounter_id'
                                  )
                                  ),
                                 */
                                array(
                                    'table' => 'schedule_calendars',
                                    'alias' => 'ScheduleCalendar',
                                    'type' => 'inner',
                                    'conditions' => array(
                                        'EncounterMaster.calendar_id = ScheduleCalendar.calendar_id'
                                    )
                                ),
                                array(
                                    'table' => 'practice_locations',
                                    'alias' => 'PracticeLocation',
                                    'type' => 'inner',
                                    'conditions' => array(
                                        'PracticeLocation.location_id = ScheduleCalendar.location'
                                    )
                                ),
                                array(
                                    'table' => 'user_accounts',
                                    'alias' => 'Provider',
                                    'type' => 'inner',
                                    'conditions' => array(
                                        'Provider.user_id = ScheduleCalendar.provider_id'
                                    )
                                )
                            )
                        );
                        $this->set('pastvisit_items', $this->sanitizeHTML($this->paginate('EncounterMaster')));
                    }
                } break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['EncounterPlanReferral']['plan_referrals_id'];

                        foreach ($ids as $id) {
                            $this->EncounterPlanReferral->saveAudit('Delete');
                            $this->EncounterPlanReferral->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }break;
            case "referral_search": {
                    if (!empty($this->data)) {
                        $search_keyword = $this->data['autocomplete']['keyword'];

                        $referral_items = $this->DirectoryReferralList->find('all', array(
                            'conditions' => array('DirectoryReferralList.physician LIKE ' => '%' . $search_keyword . '%'))
                        );
                        $data_array = array();

                        foreach ($referral_items as $referral_item) {
                            $data_array[] = $referral_item['DirectoryReferralList']['physician'] . '|' . $referral_item['DirectoryReferralList']['specialties'] . '|' . $referral_item['DirectoryReferralList']['practice_name'] . '|' . $referral_item['DirectoryReferralList']['address_1'] . '|' . $referral_item['DirectoryReferralList']['address_2'] . '|' . $referral_item['DirectoryReferralList']['city'] . '|' . $referral_item['DirectoryReferralList']['state'] . '|' . $referral_item['DirectoryReferralList']['zip_code'] . '|' . $referral_item['DirectoryReferralList']['country'] . '|' . $referral_item['DirectoryReferralList']['phone_number'];
                        }

                        echo implode("\n", $data_array);
                    }
                    exit();
                } break;

            default: {

                    $this->paginate['EncounterPlanReferral'] = array(
                        'conditions' => array('EncounterPlanReferral.patient_id' => $patient_id),
                        'order' => array('EncounterPlanReferral.modified_timestamp' => 'desc')
                    );

                    $combine = $this->Session->read('UserAccount.assessment_plan') ? true : false;

                    if ($combine) {
                        $this->paginate['EncounterPlanReferral']['group'] = array(
                            'EncounterPlanReferral.encounter_id', 'EncounterPlanReferral.referred_to'
                        );

                        $this->paginate['EncounterPlanReferral']['contain'] = array(
                            'EncounterMaster' => array(
                                'fields' => array('encounter_id', 'patient_id'),
                                'EncounterAssessment' => array(
                                    'fields' => array('diagnosis'),
                            )),
                        );
                    } else {
                        $this->paginate['EncounterPlanReferral']['contain'] = array(
                            'EncounterMaster' => array(
                                'fields' => array('encounter_id', 'patient_id'),
                            ),
                        );
                    }

                    $this->set('EncounterPlanReferral', $this->sanitizeHTML($this->paginate('EncounterPlanReferral')));
                    //$this->set('EncounterPlanReferral', $this->sanitizeHTML($this->paginate('EncounterPlanReferral', array('EncounterMaster.patient_id' => $patient_id))));
                    $this->EncounterPlanReferral->saveAudit('View');
                } break;
        }
    }

    public function pictures() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $this->set(compact('patient_id'));

        switch ($task) {
            case 'save_image':
                $this->loadModel('EncounterPhysicalExamImage');
                $source_file = $this->paths['temp'] . $this->data['image_file_name'];

                $this->paths['patient_encounter_img'] = $this->paths['patients'] . $patient_id . DS . 'images' . DS . '0' . DS;
                UploadSettings::createIfNotExists($this->paths['patient_encounter_img']);



                $destination_file = $this->paths['patient_encounter_img'] . $this->data['image_file_name'];
                @copy($source_file, $destination_file);
                @unlink($source_file); // remove temp file

                $this->EncounterPhysicalExamImage->save(array(
                    'EncounterPhysicalExamImage' => array(
                        'image' => $this->data['image_file_name'],
                        'patient_id' => $patient_id,
                    ),
                        ), false);

                exit;
                break;

            // added task to delete image
            case 'delete_image':
                $this->loadModel('EncounterPhysicalExamImage');
                $this->EncounterPhysicalExamImage->id = $this->data['image_file_id'];

                $peImage = $this->EncounterPhysicalExamImage->read();

                if (!$peImage) {
                    die('Image not found');
                }

                $encounter_id = intval($peImage['EncounterPhysicalExamImage']['encounter_id']);
                $patient_id = ($encounter_id) ? $peImage['EncounterMaster']['patient_id'] : $peImage['EncounterPhysicalExamImage']['patient_id'];

                $this->paths['patient_encounter_img'] = $this->paths['patients'] . $patient_id . DS . 'images' . DS . $encounter_id . DS;
                $filename = UploadSettings::existing(
                                $this->paths['encounters'] . $peImage['EncounterPhysicalExamImage']['image'], $this->paths['patient_encounter_img'] . $peImage['EncounterPhysicalExamImage']['image']
                );
                @unlink($filename);


                $this->EncounterPhysicalExamImage->delete($this->data['image_file_id']);
                exit;
                break;

            default:
                break;
        }
    }

    public function picture_search() {

        $this->layout = "blank";
        $this->loadModel('EncounterPhysicalExamImage');

        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        $conditions['OR'] = array(
            'EncounterPhysicalExamImage.patient_id' => $patient_id,
            'EncounterMaster.patient_id' => $patient_id,
        );

        $term = (isset($this->params['named']['term'])) ? trim($this->params['named']['term']) : "";

        if ($term) {
            $conditions['EncounterPhysicalExamImage.comment LIKE '] = '%' . $term . '%';
        }

        $this->paginate['EncounterPhysicalExamImage'] = array(
            'conditions' => $conditions,
            'limit' => 10,
            'order' => array(
                'EncounterPhysicalExamImage.physical_exam_image_id ' => 'DESC'
            ),
        );

        $pe_images = $this->paginate('EncounterPhysicalExamImage');
        $this->set(compact('pe_images'));
    }

    public function orders() {
        $this->loadModel('UserAccount');
        $this->loadModel('UserGroup');
        $this->loadModel('PracticePlanSection');
        $providers = $items_providers = $this->UserAccount->find('list', array(
            'conditions' => array(
                'UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, false)),
            'order' => array(
                'UserAccount.firstname' => 'ASC',
                'UserAccount.lastname' => 'ASC'
            ),
            'fields' => array('UserAccount.user_id', 'UserAccount.full_name'),
                ));

        $statusOpts = array(
            'open' => 'Open/Active',
            'closed' => 'Closed/Done',
        );

        $orderTypes = array(
            'Labs',
            'Radiology',
            'Procedure',
            'Immunization',
            'Injection',
            'Meds',
            'e-Rx',
            'Supplies',
            'Rx',
            'Referred To',
            'Referred By',
        );

        $sections = $this->PracticePlanSection->getCustomSections();
        if ($sections) {
            $orderTypes = array_merge($orderTypes, Set::extract('/PracticePlanSection/name', $sections));
        }



        $provider_name = $status = $order_type = array();
        $patient_name = $test_name = $date_ordered = $date_performed = '';

        $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
        $this->cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';
        $saved_search = Cache::read($this->cache_file_prefix . 'orders_search_' . $this->user_id, 'long_term');

        if (!empty($saved_search)) {
            $patient_name = (isset($saved_search['patient_name'])) ? $saved_search['patient_name'] : "";
            $test_name = (isset($saved_search['test_name'])) ? $saved_search['test_name'] : "";
            $order_type = (isset($saved_search['order_type'])) ? $saved_search['order_type'] : array();
            $status = (isset($saved_search['status'])) ? $saved_search['status'] : array();
            $provider_name = (isset($saved_search['provider_name'])) ? $saved_search['provider_name'] : array();
            $date_performed = (isset($saved_search['date_performed'])) ? $saved_search['date_performed'] : "";
            $date_ordered = (isset($saved_search['date_ordered'])) ? $saved_search['date_ordered'] : "";
        }
        $this->set('saved_search', $saved_search);
        //pr($saved_search); 
        $this->set('patient_name', $patient_name);
        $this->set('test_name', $test_name);
        $this->set('order_type', $order_type);
        $this->set('status', $status);
        $this->set('provider_name', $provider_name);
        $this->set('date_performed', $date_performed);
        $this->set('date_ordered', $date_ordered);
        $this->set('custom_field', 'on');
        //echo $date_ordered; die;

        $this->set(compact('providers', 'statusOpts', 'orderTypes'));

        $patient_mode = (isset($this->params['named']['patient_mode'])) ? $this->params['named']['patient_mode'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        if ($patient_mode == 1) {
            $this->layout = "empty";
            $this->set('patient_mode', 1);
            $this->set('patient_id', $patient_id);
        }

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        if ($task == 'rebuild') {
            $this->loadModel('Order');
            $this->Order->rebuildTable();
            $this->Session->setFlash('Order table successfully rebuilt');
            $this->redirect(array(
                'controller' => 'patients',
                'action' => 'orders',
            ));
            exit();
        }

        if ($task == 'fix_duplicates') {
            $this->loadModel('Order');
            $this->Order->fixDuplicateOrders();
            $this->Session->setFlash('Duplicates removed');
            $this->redirect(array(
                'controller' => 'patients',
                'action' => 'orders',
            ));
            exit();
        }

        if ($task == 'delete_saved_search') {
            Cache::delete($this->cache_file_prefix . 'orders_search_' . $this->user_id, 'long_term');
            echo 'true';
            exit;
        }

        $this->loadModel('PracticeSetting');
        $settings = $this->Session->read("PracticeSetting");
        $db_config = $this->PracticeSetting->getDataSource()->config;
        $cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';

        $dosespot_accessed = Cache::read($cache_file_prefix . 'dosespot_accessed');

        if (!$dosespot_accessed) {
            $dosespot_accessed = array();
        }

        //Import medications from Dosespot when for each patients 
        // who accessed the dosespot screen
        $practice_settings = $this->Session->read("PracticeSetting");
        $rx_setup = $practice_settings['PracticeSetting']['rx_setup'];
        $autoupdateMeds = intval($practice_settings['PracticeSetting']['autoupdate_meds']);
        if ($rx_setup == 'Electronic_Dosespot') {
            $this->loadModel('PatientDemographic');
            $this->loadModel('PatientMedicationList');
            foreach ($dosespot_accessed as $patient_id) {
                $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);

                //If the patient not exists in Dosespot, add the patient to Dosespot
                if ($dosespot_patient_id == 0 or $dosespot_patient_id == '') {
                    $this->PatientDemographic->updateDosespotPatient($patient_id);
                    $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
                }
                //must have $dosespot_patient_id to proceed
                if ($dosespot_patient_id && is_numeric($dosespot_patient_id)) {
                    $dosespot_xml_api = new Dosespot_XML_API();
                    $medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id);

                    foreach ($medication_items as $medication_item) {
                        $dosespot_medication_id = $medication_item['MedicationId'];
                        $items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.dosespot_medication_id' => $dosespot_medication_id)));

                        if (empty($items)) {
                            $start_date = __date('Y-m-d', strtotime($medication_item['date_written'] . '+' . $medication_item['days_supply'] . 'days'));
                            $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                            $this->data = array();
                            $this->data['PatientMedicationList']['patient_id'] = $patient_id;
                            $this->data['PatientMedicationList']['medication_type'] = "Electronic";
                            $this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
                            $this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
                            $this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
                            $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                            $this->data['PatientMedicationList']['status'] = $medication_item['status'];
                            $this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
                            $this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
                            $this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
                            $this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
                            //only set inactive_date IF days_supply was provided 
                            if (!empty($medication_item['days_supply'])) {
                                $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                            }
                            $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
                            $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                            $this->PatientMedicationList->create();
                            $this->PatientMedicationList->saveAudit('New');
                            $this->PatientMedicationList->save($this->data);
                        } else {
                            $start_date = __date('Y-m-d', strtotime($medication_item['date_written'] . '+' . $medication_item['days_supply'] . 'days'));
                            $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                            $this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
                            $this->data['PatientMedicationList']['patient_id'] = $patient_id;
                            $this->data['PatientMedicationList']['medication_type'] = "Electronic";
                            $this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
                            $this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
                            $this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
                            $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                            $this->data['PatientMedicationList']['status'] = $items['PatientMedicationList']['status'];
                            $this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
                            $this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
                            $this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
                            $this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];

                            //only set inactive_date IF days_supply was provided 
                            if (!empty($medication_item['days_supply'])) {
                                $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                            }

                            $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
                            $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");

                            //only set inactive_date IF days_supply was provided 
                            if (strtotime(date('Y-m-d')) >= strtotime($inactive_date) && !empty($medication_item['days_supply']) && $autoupdateMeds) {
                                $this->data['PatientMedicationList']['status'] = 'Completed';
                            }

                            $this->PatientMedicationList->saveAudit('Update');
                            $this->PatientMedicationList->save($this->data);
                        }
                        //Remove the dosespot data from the database.If removed in dosespot.	
                        $this->PatientMedicationList->removeDosespotDeletedData($patient_id, true, $medication_items);
                    }
                } // close loop $dosespot_patient_id
            }

            Cache::write($cache_file_prefix . 'dosespot_accessed', array());
        }
    }

    public function orders_grid() {
        $this->layout = "empty";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $checkPost = $this->data;

        $patient_name = (isset($this->data['patient_name'])) ? $this->data['patient_name'] : "";
        $test_name = (isset($this->data['test_name'])) ? $this->data['test_name'] : "";
        $order_type = (isset($this->data['order_type'])) ? $this->data['order_type'] : array();
        $status = (isset($this->data['status'])) ? $this->data['status'] : array();
        $provider_name = (isset($this->data['provider_name'])) ? $this->data['provider_name'] : array();
        $date_performed = (isset($this->data['date_performed'])) ? $this->data['date_performed'] : "";
        $date_ordered = (isset($this->data['order_date'])) ? $this->data['order_date'] : "";

        $isiPadApp = isset($_COOKIE["iPad"]);


        $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
        $this->cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        if ($task == 'save_search_order_preferences') {
            $adv_search['patient_name'] = $patient_name;
            $adv_search['provider_name'] = $provider_name;
            $adv_search['test_name'] = $test_name;
            $adv_search['order_type'] = $order_type;
            $adv_search['status'] = $status;
            $adv_search['date_performed'] = $date_performed;
            $adv_search['date_ordered'] = $date_ordered;

            $this->cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';
            Cache::write($this->cache_file_prefix . 'orders_search_' . $this->user_id, $adv_search, 'long_term');
            die;
        }

        if ($task == "delete") {
            Cache::delete($this->cache_file_prefix . 'orders_search_' . $this->user_id, 'long_term');
            echo 'true';
            exit;
        }

        if ($patient_name == '' && $test_name == '' && empty($order_type) && empty($status) && empty($provider_name) && $date_performed == '' && $date_ordered == '') {
            $saved_search = Cache::read($this->cache_file_prefix . 'orders_search_' . $this->user_id, 'long_term');

            if (!empty($saved_search)) {
                $patient_name = (isset($saved_search['patient_name'])) ? $saved_search['patient_name'] : "";
                $test_name = (isset($saved_search['test_name'])) ? $saved_search['test_name'] : "";
                $order_type = (isset($saved_search['order_type'])) ? $saved_search['order_type'] : array();
                $status = (isset($saved_search['status'])) ? $saved_search['status'] : array();
                $provider_name = (isset($saved_search['provider_name'])) ? $saved_search['provider_name'] : array();
                $date_performed = (isset($saved_search['date_performed'])) ? $saved_search['date_performed'] : "";
                $date_ordered = (isset($saved_search['order_date'])) ? $saved_search['order_date'] : "";
            }
        }

        $conditions = array();
        $test_array = array();

        if (strlen($patient_id) > 0) {
            $conditions['Order.patient_id ='] = $patient_id;
        }

        if (strlen($patient_name) > 0) {

            $search_keyword = str_replace(',', ' ', trim($patient_name));
            $search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);

            $keywords = explode(' ', $search_keyword);
            $patient_search_conditions = array();
            foreach ($keywords as $word) {
                $patient_search_conditions[] = array('OR' =>
                    array(
                        'CONVERT(DES_DECRYPT(patient_firstname) USING latin1) LIKE ' => $word . '%',
                        'CONVERT(DES_DECRYPT(patient_lastname) USING latin1) LIKE ' => $word . '%'
                    )
                );
            }

            $conditions['AND'] = $patient_search_conditions;
        }

        if ($order_type) {

            $cleanTypes = array();
            foreach ($order_type as $t) {
                $cleanTypes[] = trim($t);
            }

            if (count($cleanTypes) === 1) {
                $cleanTypes = $cleanTypes[0];
            }

            $conditions['Order.order_type'] = $cleanTypes;
        }

        if ($test_name) {
            $conditions['Order.test_name LIKE'] = $test_name . '%';
        }

        if ($status) {

            $statusList = array();

            foreach ($status as $s) {
                if ($s == 'open') {
                    $statusList[] = 'Open';
                    $statusList[] = 'Active';
                    $statusList[] = 'E';
                    $statusList[] = 'T';
                }

                if ($s == 'closed') {
                    $statusList[] = 'Closed';
                    $statusList[] = 'Done';
                    $statusList[] = 'Completed';
                    $statusList[] = 'Discontinued';
                    $statusList[] = 'InActive';
                    $statusList[] = 'Inactive';
                    $statusList[] = 'Cancelled';
                }
            }

            $conditions['Order.status'] = $statusList;
        }

        if ($provider_name) {

            $clean = array();

            foreach ($provider_name as $p) {
                $clean[] = trim($p);
            }

            if (count($clean) === 1) {
                $clean = $clean[0];
            }

            $conditions['Order.provider_name'] = $clean;
        }


        if ($date_performed) {
            if (!$isiPadApp) {
                $tmp = explode('/', $date_performed);
                $date_performed = __date('Y-m-d', strtotime($tmp[2] . '-' . $tmp[0] . '-' . $tmp[1]));
            }


            $conditions['Order.date_performed'] = $date_performed;
        }

        if ($date_ordered) {

            if (!$isiPadApp) {
                $tmp = explode('/', $date_ordered);
                $date_ordered = __date('Y-m-d', strtotime($tmp[2] . '-' . $tmp[0] . '-' . $tmp[1]));
            }

            $conditions['Order.date_ordered'] = $date_ordered;
        }

        $this->loadModel("Order");
        $this->paginate['Order'] = array(
            'limit' => 20,
            'page' => 1,
            'order' => array('Order.date_performed' => 'desc', 'Order.modified_timestamp' => 'desc'),
            'conditions' => $conditions
        );

        $data = $this->paginate("Order");

        $this->loadModel('MessagingPhoneCall');

        $orderEncounterIds = Set::extract('/Order/encounter_order_id', $data);
        $phoneCallIds = $this->MessagingPhoneCall->find('list', array(
            'conditions' => array(
                'MessagingPhoneCall.encounter_order_id' => $orderEncounterIds,
            ),
            'fields' => array(
                'MessagingPhoneCall.encounter_order_id', 'MessagingPhoneCall.phone_call_id'
            )
                ));

        $tmp = array();
        $referrals = array();
        $referralTypeMap = array();
        foreach ($data as $d) {
            $d['Order']['phone_call_id'] = 0;
            if ($d['Order']['order_type'] == 'Referral') {
                $referrals[] = $d['Order']['data_id'];
            }
            if (isset($phoneCallIds[$d['Order']['encounter_order_id']])) {
                $d['Order']['phone_call_id'] = $phoneCallIds[$d['Order']['encounter_order_id']];
            }

            $tmp[] = $d;
        }

        $data = $tmp;

        if ($referrals) {
            $this->loadModel('EncounterPlanReferral');
            $referralTypeMap = $this->EncounterPlanReferral->find('list', array(
                'conditions' => array(
                    'EncounterPlanReferral.plan_referrals_id' => $referrals,
                ),
                'fields' => array(
                    'EncounterPlanReferral.plan_referrals_id', 'EncounterPlanReferral.refer_type'
                ),
                    ));
        }

        $this->set('referralTypeMap', $referralTypeMap);
        $this->set('orders', $this->sanitizeHTML($data));

        $this->UserGroup = & ClassRegistry::init('UserGroup');
        $this->UserAccount = & ClassRegistry::init('UserAccount');
        $conditions = array('UserAccount.role_id  ' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, $include_admin = false));
        $users = $this->UserAccount->find('all', array('conditions' => $conditions));
        //all providers
        $this->set('users', $this->sanitizeHTML($users));
    }

    public function notes() {
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientNote");
        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            $this->data['PatientNote']['patient_id'] = $patient_id;
            $this->data['PatientNote']['date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['PatientNote']['date'])));
        }
        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        if (isset($this->data['PatientNote']['alert_encounter']) && $this->data['PatientNote']['alert_encounter'] == 'on') {
                            $this->data['PatientNote']['alert_encounter'] = 'Yes';
                        } else {
                            $this->data['PatientNote']['alert_encounter'] = 'No';
                        }

                        if (isset($this->data['PatientNote']['alert_chart']) && $this->data['PatientNote']['alert_chart'] == 'on') {
                            $this->data['PatientNote']['alert_chart'] = 'Yes';
                        } else {
                            $this->data['PatientNote']['alert_chart'] = 'No';
                        }

                        $this->PatientNote->create();
                        $this->PatientNote->saveAudit('New');
                        $this->PatientNote->save($this->data);

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                }
                break;
            case "edit": {

                    if (!empty($this->data)) {
                        if (isset($this->data['PatientNote']['alert_encounter']) && $this->data['PatientNote']['alert_encounter'] == 'on') {
                            $this->data['PatientNote']['alert_encounter'] = 'Yes';
                        } else {
                            $this->data['PatientNote']['alert_encounter'] = 'No';
                        }

                        if (isset($this->data['PatientNote']['alert_chart']) && $this->data['PatientNote']['alert_chart'] == 'on') {
                            $this->data['PatientNote']['alert_chart'] = 'Yes';
                        } else {
                            $this->data['PatientNote']['alert_chart'] = 'No';
                        }


                        $this->PatientNote->saveAudit('Update');
                        $this->PatientNote->save($this->data);

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    } else {
                        $note_id = (isset($this->params['named']['note_id'])) ? $this->params['named']['note_id'] : "";
                        $items = $this->PatientNote->find(
                                'first', array(
                            'conditions' => array('PatientNote.note_id' => $note_id)
                                )
                        );
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                }
                break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientNote']['note_id'];

                        foreach ($ids as $id) {
                            $this->PatientNote->saveAudit('Delete');
                            $this->PatientNote->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
                break;
            default: {
                    $this->paginate['PatientNote'] = array(
                        'conditions' => array('PatientNote.patient_id' => $patient_id),
                        'order' => array('PatientNote.date' => 'desc')
                    );

                    $this->set('patient_notes', $this->sanitizeHTML($this->paginate('PatientNote')));
                    //$this->set('patient_notes', $this->sanitizeHTML($this->paginate('PatientNote'), array('PatientNote.patient_id' => $patient_id)));
                    $this->PatientNote->saveAudit('View');
                }
        }
    }

    public function documents() {
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
        $this->cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';

        $this->loadModel("UserAccount");
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);

        $this->loadModel("PatientDocument");

        //save the filter variables to the cache for later use .
        if ($task == "save_filter") {
            $adv_search['doc_name'] = (isset($_POST['doc_name'])) ? $_POST['doc_name'] : "";
            $adv_search['doc_type'] = (isset($_POST['doc_type'])) ? $_POST['doc_type'] : "";
            $adv_search['doc_status'] = (isset($_POST['doc_status'])) ? $_POST['doc_status'] : "";
            $adv_search['doc_fromdate'] = (isset($_POST['doc_fromdate'])) ? $_POST['doc_fromdate'] : "";
            $adv_search['doc_todate'] = (isset($_POST['doc_todate'])) ? $_POST['doc_todate'] : "";

            Cache::write($this->cache_file_prefix . 'document_search_' . $this->user_id, $adv_search, 'long_term');

            echo "true";
            exit;
        }
        // delete the cache filter here 
        if ($task == "delete_filter") {

            Cache::delete($this->cache_file_prefix . 'document_search_' . $this->user_id, 'long_term');
            echo 'true';
            exit;
        }

        $saved_search = Cache::read($this->cache_file_prefix . 'document_search_' . $this->user_id, 'long_term');
        $saved_search_array = array();
        if (!empty($saved_search)) {

            foreach ($saved_search as $key => $save_search) {
                if ($save_search != "") {
                    if ($key == "doc_type") {
                        $saved_search_array[$key] = $save_search;
                    } else {
                        $saved_search_array[$key] = base64_decode($save_search);
                    }
                } else {
                    $saved_search_array[$key] = "";
                }
            }
            $this->set(compact('saved_search_array', $saved_search_array));
        }

        $this->PatientDocument->execute($this, $task, $patient_id);
    }

    public function patient_documents() {
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientDocument");
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);

        $this->PatientDocument->execute($this, $task, $patient_id);
        /* $this->paginate['PatientDocument'] = array(
          'conditions' => array('PatientDocument.document_type' => 'Lab','PatientDocument.patient_id' =>$patient_id),
          'order' => array('PatientDocument.service_date' => 'desc')
          ); */

        $this->paginate['PatientDocument'] = array(
            'conditions' => array('PatientDocument.patient_id' => $patient_id, 'PatientDocument.document_type' => 'Lab'),
            'order' => array('PatientDocument.service_date' => 'desc')
        );

        //$this->set('PatientDocument', $this->sanitizeHTML($this->paginate('PatientDocument')));
        $this->set('PatientDocument', $this->sanitizeHTML($this->paginate('PatientDocument')));
    }

    public function messages() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("MessagingMessage");

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        if ($task) {
            switch ($task) {
                case "delete": {
                        $ret = array();
                        $ret['delete_count'] = 0;

                        if (!empty($this->data)) {
                            $ids = $this->data['MessagingMessage']['message_id'];

                            foreach ($ids as $id) {
                                $this->MessagingMessage->saveAudit('Delete');
                                $this->MessagingMessage->delete($id, false);
                                $ret['delete_count']++;
                            }
                        }

                        echo json_encode($ret);
                        exit;
                        $this->redirect(array('action' => 'MessagingMessage'));
                    }
                    break;
                default: {
                        $this->set('patient_user_id', $this->UserAccount->getUserbyPatientID($patient_id));
                        $message_id = (isset($this->params['named']['message_id'])) ? $this->params['named']['message_id'] : "";

                        $items = $this->MessagingMessage->find(
                                'first', array(
                            'conditions' => array('MessagingMessage.message_id' => $message_id)
                                )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
            }
        } else {
            $this->set('patient_user_id', $this->UserAccount->getUserbyPatientID($patient_id));

            $this->paginate['MessagingMessage'] = array(
                'conditions' => array(
                    'MessagingMessage.patient_id' => $patient_id,
                    'MessagingMessage.status <>' => 'Draft',
                ),
                'order' => array('MessagingMessage.modified_timestamp' => 'desc')
            );

            $this->set('MessagingMessages', $this->sanitizeHTML($this->paginate('MessagingMessage')));
            //$this->set('MessagingMessages', $this->sanitizeHTML($this->paginate('MessagingMessage', array('MessagingMessage.patient_id' => $patient_id))));
        }

        $this->MessagingMessage->saveAudit('View');
    }

    public function phone_calls() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("MessagingPhoneCall");

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        if ($task) {
            switch ($task) {
                case "delete": {
                        $ret = array();
                        $ret['delete_count'] = 0;

                        if (!empty($this->data)) {
                            $ids = $this->data['MessagingPhoneCall']['phone_call_id'];

                            foreach ($ids as $id) {
                                $this->MessagingPhoneCall->saveAudit('Delete');
                                $this->MessagingPhoneCall->delete($id, false);
                                $ret['delete_count']++;
                            }
                        }

                        echo json_encode($ret);
                        exit;
                        $this->redirect(array('action' => 'phone_calls'));
                    }
                    break;
                default: {
                        $phone_call_id = (isset($this->params['named']['phone_call_id'])) ? $this->params['named']['phone_call_id'] : "";

                        $items = $this->MessagingPhoneCall->find(
                                'first', array(
                            'conditions' => array('MessagingPhoneCall.phone_call_id' => $phone_call_id)
                                )
                        );
                        $documented_by_Obj = $this->UserAccount->getUserByID($items['MessagingPhoneCall']['documented_by_user_id']);

                        if (!is_object($documented_by_Obj)) {
                            $this->redirect(array('action' => 'phone_calls', 'patient_id' => $patient_id));
                        }

                        $items['MessagingPhoneCall']['documented_by'] = $documented_by_Obj->full_name;
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
            }
        } else {
            $this->paginate['MessagingPhoneCall'] = array(
                'conditions' => array('MessagingPhoneCall.patient_id' => $patient_id),
                'order' => array('MessagingPhoneCall.modified_timestamp' => 'desc')
            );

            $this->set('MessagingPhoneCalls', $this->sanitizeHTML($this->paginate('MessagingPhoneCall')));

            //$this->set('MessagingPhoneCalls', $this->sanitizeHTML($this->paginate('MessagingPhoneCall', array('MessagingPhoneCall.patient_id' => $patient_id))));
        }

        $this->MessagingPhoneCall->saveAudit('View');
    }

    public function letters() {
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientLetter");
        $this->loadModel("PatientDemographic");
        $this->loadModel("LetterTemplate");
        $this->loadModel("PatientPreference");
        $this->loadModel("PracticeProfile");
        $template_type = (isset($this->data['template_type'])) ? $this->data['template_type'] : "";

        if ($task == "addnew" || $task == "edit") {
            $types = $this->LetterTemplate->getTemplates();
            $this->set("types", $this->sanitizeHTML($types));

            $items = $this->PatientPreference->find(
                    'first', array(
                'conditions' => array('PatientPreference.patient_id' => $patient_id)
                    )
            );
            $this->set('EditItem', $this->sanitizeHTML($items));
            $user = $this->UserAccount->getUserRealName($items['PatientPreference']['pcp']);
            $this->set('user', $this->sanitizeHTML($user));
        }

        if (!empty($this->data) && ($task == "addnew" || $task == "edit")) {
            $this->data['PatientLetter']['patient_id'] = $patient_id;
            $this->data['PatientLetter']['date_performed'] = __date("Y-m-d", strtotime($this->data['PatientLetter']['date_performed']));
        }

        $patient_items = $this->PatientDemographic->find(
                'first', array(
            'conditions' => array('PatientDemographic.patient_id' => $patient_id),
            'recursive' => -1
                )
        );

        $this->set('PatientDemo', $this->sanitizeHTML($patient_items));

        switch ($task) {
            case "addnew": {
                    if (!empty($this->data)) {
                        $this->PatientLetter->create();
                        $this->PatientLetter->saveAudit('New');
                        $this->PatientLetter->save($this->data);

                        $ret = array();

                        /* if($this->data['preview_mode'] == "true")
                          {
                          $ret['redir_url'] = Router::url(array('task' => 'edit', 'patient_id' => $patient_id, 'letter_id' => $this->PatientLetter->getLastInsertId()));
                          } */

                        echo json_encode($ret);
                        exit;
                    }
                }
                break;
            case "edit": {
                    if (!empty($this->data)) {
                        $this->PatientLetter->saveAudit('Update');
                        $this->PatientLetter->save($this->data);

                        $ret = array();

                        /* if($this->data['preview_mode'] == "true")
                          {
                          $ret['redir_url'] = Router::url(array('task' => 'edit', 'patient_id' => $patient_id, 'letter_id' => $this->PatientLetter->id));
                          } */

                        echo json_encode($ret);
                        exit;
                    } else {
                        $letter_id = (isset($this->params['named']['letter_id'])) ? $this->params['named']['letter_id'] : "";
                        $items = $this->PatientLetter->find(
                                'first', array(
                            'conditions' => array('PatientLetter.letter_id' => $letter_id)
                                )
                        );
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                }
                break;
            case "delete": {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data)) {
                        $ids = $this->data['PatientLetter']['letter_id'];

                        foreach ($ids as $id) {
                            $this->PatientLetter->saveAudit('Delete');
                            $this->PatientLetter->delete($id, false);
                            $ret['delete_count']++;
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
                break;
            case "get_content": {
                    $template_content = $this->LetterTemplate->find('first', array('conditions' => array('LetterTemplate.template_id' => $this->data['template_id'])));
                    $ret = array();
                    $ret['content'] = $template_content['LetterTemplate']['content'];
                    echo json_encode($ret);
                    exit;
                }break;
            case "letter_content": {
                    $template_id = $this->data['PatientLetter']['template_id'];
                    $template_data = $this->LetterTemplate->getTemplate($template_id);
                    $template_data['content'] = $this->data['PatientLetter']['content'];

                    $location_id = $template_data['location_id'];
                    $location = $this->PracticeLocation->getLocationItem($location_id);
                    $this->set('location', $location);
                    $this->set('template_data', $template_data);
                    $practice_profile = $this->PracticeProfile->find('first');
                    $practice_profile_logo = $practice_profile['PracticeProfile']['logo_image'];
                    $this->set('practice_profile', $practice_profile_logo);

                    $this->layout = 'empty';
                    $data = $this->render('../administration/template/letter_template');
                    $file_path = $this->paths['temp'];
                    $file_path = str_replace('//', '/', $file_path);
                    $file_name = 'lettertemplate' . $template_id . '.pdf';
                    site::write(pdfReport::generate($data), $file_path . $file_name);
                    $file_path_test = $this->url_rel_paths['temp'];
                    $ret = array();
                    $ret['target_file'] = $file_name;
                    echo json_encode($ret);
                    exit();
                }break;
            default: {
                    $types = $this->LetterTemplate->getTemplates();
                    $this->set("types", $this->sanitizeHTML($types));

                    $this->set('patient_letters', $this->sanitizeHTML($this->paginate('PatientLetter', array('PatientLetter.patient_id' => $patient_id))));
                    $this->PatientLetter->saveAudit('View');
                }
        }
    }

    public function past_visits() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("EncounterMaster");
        $this->EncounterMaster->virtualFields = array(
            'location_name' => 'location_name',
            'firstname' => 'firstname'
        );
        $this->EncounterMaster->hasMany['EncounterAssessment']['order'] = array('EncounterAssessment.diagnosis' => 'ASC');
        $this->paginate['EncounterMaster'] = array(
            'limit' => 20,
            'conditions' => array('EncounterMaster.patient_id' => $patient_id),
//array('AND' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.encounter_status' => array('Closed', 'Open'))),
            'order' => array('EncounterMaster.encounter_date' => 'DESC'),
            'fields' => array('`EncounterMaster`.`encounter_id`', '`EncounterMaster`.`patient_id`', '`EncounterMaster`.`calendar_id`', '`EncounterMaster`.`encounter_date`', '`Provider`.`firstname`', '`Provider`.`lastname`', 'PracticeLocation.location_name', /* 'EncounterAssessment.diagnosis' , */'`EncounterMaster`.`encounter_status`', 'ScheduleCalendar.visit_type'),
            'joins' => array(
                /*
                  array(
                  'table' => 'encounter_assessment',
                  'alias' => 'EncounterAssessment',
                  'type' => 'left',
                  'conditions' => array(
                  'EncounterMaster.encounter_id = EncounterAssessment.encounter_id'
                  )
                  ),
                 */
                array(
                    'table' => 'schedule_calendars',
                    'alias' => 'ScheduleCalendar',
                    'type' => 'inner',
                    'conditions' => array(
                        'EncounterMaster.calendar_id = ScheduleCalendar.calendar_id'
                    )
                ),
                array(
                    'table' => 'practice_locations',
                    'alias' => 'PracticeLocation',
                    'type' => 'inner',
                    'conditions' => array(
                        'PracticeLocation.location_id = ScheduleCalendar.location'
                    )
                ),
                array(
                    'table' => 'user_accounts',
                    'alias' => 'Provider',
                    'type' => 'inner',
                    'conditions' => array(
                        'Provider.user_id = ScheduleCalendar.provider_id'
                    )
                )
            )
        );
        $this->set('pastvisit_items', $this->sanitizeHTML($this->paginate('EncounterMaster')));
        $this->EncounterMaster->saveAudit('View', 'EncounterMaster', 'Attachments - Past Visits');
    }

    public function activities() {
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("PatientOrders");
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->paginate['PatientOrders'] = array(
            'limit' => 20,
            'conditions' => array('AND' => array('PatientOrders.ordered_by_id' => $user_id))
        );
        $this->set('patient_orders', $this->sanitizeHTML($this->paginate('PatientOrders')));
    }

    function activities_documents() {
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("PatientDocument");
        $patient_documents = $this->PatientDocument->getItemsByPatient($user_id);
        $this->set('patient_documents', $patient_documents);
        $this->loadModel("EncounterPlanProcedure");
        $patient_outside_order_items = $this->EncounterPlanProcedure->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }

    function outside_order() {
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanLab");
        $patient_outside_order_items = $this->EncounterPlanLab->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }

    function outside_order_radiology() {
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanRadiology");
        $patient_outside_order_items = $this->EncounterPlanRadiology->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }

    function outside_order_procedure() {
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanProcedure");
        $patient_outside_order_items = $this->EncounterPlanProcedure->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }

    function outside_order_rx() {
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanRx");
        $patient_outside_order_items = $this->EncounterPlanRx->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }

    function outside_order_referral() {
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanReferral");
        $patient_outside_order_items = $this->EncounterPlanReferral->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }

    function outside_order_advice_instruction() {
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanAdviceInstructions");
        $patient_outside_order_items = $this->EncounterPlanAdviceInstructions->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }

    public function audit_log() {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        if (!empty($this->data)) {
            $this->redirect(array(
                'controller' => 'patients',
                'action' => 'audit_log',
                'patient_id' => $patient_id,
                'truncate_output' => 1,
                'period' => $this->data['period'],
                'date_from' => str_replace('/', '-', $this->data['date_from']),
                'date_to' => str_replace('/', '-', $this->data['date_to']),
                'section' => $this->data['section']));
            exit();
        }

        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        if ($task == 'show_details') {
            $this->loadModel('AuditDetail');
            $audit_id = (isset($this->params['named']['audit_id'])) ? $this->params['named']['audit_id'] : "";

            $audit_details = $this->paginate('AuditDetail', array(
                'AuditDetail.audit_id' => $audit_id,
                    ));

            $this->set(compact('audit_details'));
        } else {
            $_SESSION['cancel_url'] = str_replace('truncate_output:1/', '', $_SERVER['REQUEST_URI']);
        }



        $this->loadModel('Audit');
        $this->loadModel('AuditSection');

        $period = (isset($this->params['named']['period'])) ? $this->params['named']['period'] : 'today';
        $date_from = (isset($this->params['named']['date_from'])) ? $this->params['named']['date_from'] : "";
        $date_to = (isset($this->params['named']['date_to'])) ? $this->params['named']['date_to'] : "";
        $section = (isset($this->params['named']['section'])) ? $this->params['named']['section'] : "";


        $this->set(compact('period', 'section'));
        $conditions = $this->Audit->getAuditConditions($patient_id, $period, $date_from, $date_to, $section);



        $this->Audit->virtualFields['full_name'] = "CONCAT(UserAccount.firstname, ' ', UserAccount.lastname)";

        $this->paginate['Audit'] = array(
            'fields' => array(
                'Audit.audit_type',
                'Audit.data_id',
                'Audit.patient_id',
                'Audit.table_name',
                'Audit.previous_hash',
                'Audit.hash',
                'Audit.modified_timestamp',
                'AuditSection.section_name',
                'Audit.full_name',
                'Audit.emergency',
                'AuditSection.audit_section_id'),
            'conditions' => $conditions,
            'limit' => 20,
            'page' => 1,
            'order' => array('Audit.modified_timestamp' => 'desc')
        );

        $audit_logs = $this->paginate('Audit');


        foreach ($audit_logs as &$a) {
            $a['Audit']['valid'] = $this->Audit->validateLog($a);
        }

        $this->set('audit_logs', $audit_logs);
        $this->set("audit_sections", $this->AuditSection->getAllSections());
    }

    /**
     * Override Controller::paginate() from cake/libs/controller/controller.php
     * 
     */
    public function paginate($object = null, $scope = array(), $whitelist = array()) {

        // For other models proceed normally
        return parent::paginate($object, $scope, $whitelist);
    }

    /**
     * Special pagination routine for Order model
     * 
     * This is almost identical to the original pagination method
     * 
     */
    private function __paginateOrder($object = null, $scope = array(), $whitelist = array()) {
        if (is_array($object)) {
            $whitelist = $scope;
            $scope = $object;
            $object = null;
        }
        $assoc = null;

        if (is_string($object)) {
            $assoc = null;
            if (strpos($object, '.') !== false) {
                list($object, $assoc) = pluginSplit($object);
            }

            if ($assoc && isset($this->{$object}->{$assoc})) {
                $object = & $this->{$object}->{$assoc};
            } elseif (
                    $assoc && isset($this->{$this->modelClass}) &&
                    isset($this->{$this->modelClass}->{$assoc}
            )) {
                $object = & $this->{$this->modelClass}->{$assoc};
            } elseif (isset($this->{$object})) {
                $object = & $this->{$object};
            } elseif (
                    isset($this->{$this->modelClass}) && isset($this->{$this->modelClass}->{$object}
            )) {
                $object = & $this->{$this->modelClass}->{$object};
            }
        } elseif (empty($object) || $object === null) {
            if (isset($this->{$this->modelClass})) {
                $object = & $this->{$this->modelClass};
            } else {
                $className = null;
                $name = $this->uses[0];
                if (strpos($this->uses[0], '.') !== false) {
                    list($name, $className) = explode('.', $this->uses[0]);
                }
                if ($className) {
                    $object = & $this->{$className};
                } else {
                    $object = & $this->{$name};
                }
            }
        }

        if (!is_object($object)) {
            trigger_error(sprintf(
                            __('Controller::paginate() - can\'t find model %1$s in controller %2$sController', true
                            ), $object, $this->name
                    ), E_USER_WARNING);
            return array();
        }
        $options = array_merge($this->params, $this->params['url'], $this->passedArgs);

        if (isset($this->paginate[$object->alias])) {
            $defaults = $this->paginate[$object->alias];
        } else {
            $defaults = $this->paginate;
        }

        if (isset($options['show'])) {
            $options['limit'] = $options['show'];
        }

        if (isset($options['sort'])) {
            $direction = null;
            if (isset($options['direction'])) {
                $direction = strtolower($options['direction']);
            }
            if ($direction != 'asc' && $direction != 'desc') {
                $direction = 'asc';
            }
            $options['order'] = array($options['sort'] => $direction);
        }


        // Since the order model does not map to an actual order,
        // skip checking/verification of fields to be sorted

        /*
          if (!empty($options['order']) && is_array($options['order'])) {
          $alias = $object->alias ;
          $key = $field = key($options['order']);

          if (strpos($key, '.') !== false) {
          list($alias, $field) = explode('.', $key);
          }
          $value = $options['order'][$key];
          unset($options['order'][$key]);

          if ($object->hasField($field)) {
          $options['order'][$alias . '.' . $field] = $value;
          } elseif ($object->hasField($field, true)) {
          $options['order'][$field] = $value;
          } elseif (isset($object->{$alias}) && $object->{$alias}->hasField($field)) {
          $options['order'][$alias . '.' . $field] = $value;
          }
          }
         */


        $vars = array('fields', 'order', 'limit', 'page', 'recursive');
        $keys = array_keys($options);
        $count = count($keys);

        for ($i = 0; $i < $count; $i++) {
            if (!in_array($keys[$i], $vars, true)) {
                unset($options[$keys[$i]]);
            }
            if (empty($whitelist) && ($keys[$i] === 'fields' || $keys[$i] === 'recursive')) {
                unset($options[$keys[$i]]);
            } elseif (!empty($whitelist) && !in_array($keys[$i], $whitelist)) {
                unset($options[$keys[$i]]);
            }
        }
        $conditions = $fields = $order = $limit = $page = $recursive = null;

        if (!isset($defaults['conditions'])) {
            $defaults['conditions'] = array();
        }

        $type = 'all';

        if (isset($defaults[0])) {
            $type = $defaults[0];
            unset($defaults[0]);
        }

        $options = array_merge(array('page' => 1, 'limit' => 20), $defaults, $options);
        $options['limit'] = (int) $options['limit'];
        if (empty($options['limit']) || $options['limit'] < 1) {
            $options['limit'] = 1;
        }

        extract($options);

        if (is_array($scope) && !empty($scope)) {
            $conditions = array_merge($conditions, $scope);
        } elseif (is_string($scope)) {
            $conditions = array($conditions, $scope);
        }
        if ($recursive === null) {
            $recursive = $object->recursive;
        }

        $extra = array_diff_key($defaults, compact(
                        'conditions', 'fields', 'order', 'limit', 'page', 'recursive'
                ));
        if ($type !== 'all') {
            $extra['type'] = $type;
        }

        if (method_exists($object, 'paginateCount')) {
            $count = $object->paginateCount($conditions, $recursive, $extra);
        } else {
            $parameters = compact('conditions');
            if ($recursive != $object->recursive) {
                $parameters['recursive'] = $recursive;
            }
            $count = $object->find('count', array_merge($parameters, $extra));
        }
        $pageCount = intval(ceil($count / $limit));

        if ($page === 'last' || $page >= $pageCount) {
            $options['page'] = $page = $pageCount;
        } elseif (intval($page) < 1) {
            $options['page'] = $page = 1;
        }
        $page = $options['page'] = (integer) $page;

        if (method_exists($object, 'paginate')) {
            $results = $object->paginate(
                    $conditions, $fields, $order, $limit, $page, $recursive, $extra
            );
        } else {
            $parameters = compact('conditions', 'fields', 'order', 'limit', 'page');
            if ($recursive != $object->recursive) {
                $parameters['recursive'] = $recursive;
            }
            $results = $object->find($type, array_merge($parameters, $extra));
        }
        $paging = array(
            'page' => $page,
            'current' => count($results),
            'count' => $count,
            'prevPage' => ($page > 1),
            'nextPage' => ($count > ($page * $limit)),
            'pageCount' => $pageCount,
            'defaults' => array_merge(array('limit' => 20, 'step' => 1), $defaults),
            'options' => $options
        );
        $this->params['paging'][$object->alias] = $paging;

        if (!in_array('Paginator', $this->helpers) && !array_key_exists('Paginator', $this->helpers)) {
            $this->helpers[] = 'Paginator';
        }
        return $results;
    }

    public function summary() {
        $this->layout = "empty";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        $content = $this->requestAction('/encounters/summary/', array('return', 'patient_id' => $patient_id));

        echo $content;
        exit();
    }

    function county_autocomplete() {
        $this->loadModel('CountyCodes');

        if (!empty($this->data)) {
            $search_keyword = $this->data['autocomplete']['keyword'];
            $search_limit = $this->data['autocomplete']['limit'];
            $county_names = $this->CountyCodes->find('all', array(
                'conditions' => array(
                    'OR' => array(
                        array('CountyCodes.county_name LIKE ' => $search_keyword . '%'),
                        array('CountyCodes.county_name LIKE ' => $search_keyword . '%'),
                        array('CountyCodes.county_name LIKE ' => '% ' . $search_keyword . '%'),
                        array('CountyCodes.county_name LIKE ' => '%[' . $search_keyword . '%')
                    )
                ),
                'limit' => $search_limit,
                'order' => array('CountyCodes.county_name' => 'DESC')
                    ));
            $data_array = array();
            foreach ($county_names as $county_name) {
                $data_array[] = $county_name['CountyCodes']['county_name'];
            }
            echo implode("\n", $data_array);
            $this->Layout = '';
            exit;
        }
    }

    public function get_relationship() {
        $this->layout = "blank";
        echo "ASC-Associate" . "\n" . "BRO-Brother" . "\n" . "CGV-Care giver" . "\n" . "CHD-Child" . "\n" . "DEP-Handicapped dependent" . "\n"
        . "DOM-Life partner" . "\n" . "EMC-Emergency contact" . "\n" . "EME-Employee" . "\n" . "EMR-Employer" . "\n" . "EXF-Extended family" . "\n"
        . "FCH-Foster child" . "\n" . "FMN-Form completed by (Name)Manufacturer" . "\n" . "FND-Friend" . "\n"
        . "FOT-Form completed by (Name)Other" . "\n" . "FPP-Form completed by (Name)--Patient/Parent" . "\n" . "FTH-Father" . "\n"
        . "FVP-Form completed by (Name)--Vaccine provider" . "\n" . "GCH-Grandchild" . "\n" . "GRD-Guardian" . "\n" . "GRP-Grandparent" . "\n"
        . "MGR-Manager" . "\n" . "MTH-Mother" . "\n" . "NCH-Natural child" . "\n" . "NON-None" . "\n" . "OAD-Other adult" . "\n" . "OTH-Other" . "\n"
        . "OWN-Owner" . "\n" . "PAR-Parent" . "\n" . "SCH-Stepchild" . "\n" . "SEL-Self" . "\n" . "SIB-Sibling" . "\n" . "SIS-Sister" . "\n" . "SPO-Spouse" . "\n"
        . "TRA-Trainer" . "\n" . "UNK-Unknown" . "\n" . "VAB-Vaccine administered by (Name)" . "\n" . "WRD-Ward of court";
        exit();
    }

    public function getInfoDetails() {
        $this->layout = "";
        $url = "";

        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $icdCode = (isset($this->params['named']['icdCode'])) ? $this->params['named']['icdCode'] : "";
        $rxnorm = (isset($this->params['named']['rxnorm'])) ? $this->params['named']['rxnorm'] : "";
        $loinc_code = (isset($this->params['named']['loinc_code'])) ? $this->params['named']['loinc_code'] : "";

        $language = 'en';

        if (strlen($patient_id) > 0) {
            $this->loadModel('PatientDemographic');
            $patient = $this->PatientDemographic->getPatient($patient_id);
            $preferred_language = $patient['preferred_language'];

            if ($preferred_language == 'Spanish') {
                $language = 'es';
            }
        }

        $cs = '';
        $v_cs = '';
        if ($icdCode != "") {
            $practice_settings = $_SESSION['PracticeSetting'];
            $icd_version = intval($practice_settings['PracticeSetting']['icd_version']);
            if ($icd_version == "10")
                $cs = "2.16.840.1.113883.6.90";
            else
                $cs = "2.16.840.1.113883.6.103";

            $v_cs = $icdCode;
        }
        else if ($rxnorm != "") {
            $cs = "2.16.840.1.113883.6.88";
            $v_cs = $rxnorm;
        } else if ($loinc_code != "") {
            $cs = "2.16.840.1.113883.6.1";
            $v_cs = $loinc_code;
        }

        $url = "";
        if ($loinc_code != '' && $rxnorm != '' && $icdCode != '') {
            $contentXML = file_get_contents("http://apps2.nlm.nih.gov/medlineplus/services/mpconnect_service.cfm?informationRecipient.languageCode.c=" . $language . "&mainSearchCriteria.v.cs=" . $cs . "&mainSearchCriteria.v.c=" . $v_cs);
            $contentArray = simplexml_load_string($contentXML);

            foreach ($contentArray->children() as $child) {

                $role = $child->attributes();

                foreach ($child as $key => $value) {
                    if (is_object($value)) {
                        $childNode = $value->attributes();
                        foreach ($childNode as $attribute => $avalue) {
                            if ($attribute == "href")
                                $url = $avalue;
                        }
                    }
                }
            }
        }

        if ($url == "")
            echo "Sorry! We are unable to get information on this topic.";
        else
            header("Location: $url");

        exit;
    }

}

?>