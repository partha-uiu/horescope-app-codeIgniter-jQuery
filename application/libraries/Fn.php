<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('p')) {

    function p($val = '') {
        echo '<pre style="color:#000; background: #FFF; display:block;">';
        print_r($val);
        echo '</pre>';
    }

}

if (!function_exists('pr')) {

    function pr($val = '') {
        echo '<pre style="color:#000; background: #FFF; display:block;">';
        print_r($val);
        echo '</pre>';
    }

}


if (!function_exists('__')) {

    function __($line = '') {
        $CI = & get_instance();
        $line = $CI->lang->line($line);


        $arg_list = func_get_args();
        if (count($arg_list) > 1) {
            unset($arg_list[0]);
            ob_start();
            vprintf($line, $arg_list);
            $data = ob_get_contents();
            ob_end_clean();
            return $data;
        }
        return $line;
    }

}

if (!function_exists('or_value')) {

    function or_value() {
        $arr = func_get_args();
        foreach ($arr as $val) {
            if (!empty($val)) {
                $return = $val;
                break;
            }
        }
        return isset($return) ? $return : false;
    }

}

class Fn {

    public function __construct() {
        $this->ci = &get_instance();
        $this->initialize();
    }

    public function p($val = '') {
        echo '<pre>';
        print_r($val);
        echo '</pre>';
    }

    public function get($name, $print = false) {
        if (!property_exists($this, $name))
            return false;
        if ($print)
            echo $this->{$name};
        else
            return $this->{$name};
    }

    public function set($name, $value = '') {
        if (empty($name))
            return false;
        else if (is_array($name) || is_object($name))
            $resetParams = $name;
        else
            $resetParams = array($name => $value);

        foreach ($resetParams as $key => $val) {
            $this->{$key} = $val;
        }
    }

    public function delete($name, $print = false) {
        if (!property_exists($this, $name))
            return false;
        unset($this->{$name});
        return TRUE;
    }

    public function initialize($params = '') {

    }

    function RandomAlphaNumber($length = 3) {
        $rangeMin = pow(36, $length - 1); //smallest number to give length digits in base 36
        $rangeMax = pow(36, $length) - 1; //largest number to give length digits in base 36
        $base10Rand = mt_rand($rangeMin, $rangeMax); //get the random number
        $newRand = base_convert($base10Rand, 10, 36); //convert it
        return $newRand; //spit it out
    }

    function RandomNumber($length = 3) {
        $rand_min = (int) ('1' . str_repeat("0", ($length - 1)));
        $rand_max = (int) (str_repeat("9", ($length)));
        $result = rand($rand_min, $rand_max);
        ;
        return $result; //spit it out
    }

    function array_to_list($data, $list = array('id', 'name')) {
        $final_data = array();
        foreach ($data as $v) {
            if (is_array($v)) {
                $v = (object) $v;
            }
            $final_data[$v->{$list[0]}] = $v->{$list[1]};
        }
        return $final_data;
    }

    function toList($data, $list = array('id', 'title')) {
        return $this->array_to_list($data, $list);
    }

    function boolean_option($input = 1) {
        $final_data = array();
        switch ($input) {
            case 1:
                $final_data = array(0 => 'Inactive', 1 => 'Active');
                break;
            case 2:
                $final_data = array(0 => 'Un Publish', 1 => 'Publish');
                break;
            case 3:
                $final_data = array(0 => 'Disabled', 1 => 'Enabled');
                break;
            case 4:
                $final_data = array(0 => 'Hide', 1 => 'Show');
                break;
            case 5:
                $final_data = array(0 => 'No', 1 => 'Yes');
                break;
            default:
                $final_data = 0;
                break;
        }
        return $final_data;
    }

    function detect_animal($input = 1) {
        $final_data = array();
        switch ($input) {
            case 1:
                $final_data = array('Male' => 'Male', 'Female' => 'Female');
                break;
            default:
                $this->detect_animal(1);
                break;
        }
        return $final_data;
    }

    function get_verb($array_or_object = '', $verb = 'item') {
        if ('item' == $verb) {
            return count($array_or_object) > 1 ? 'items' : 'item';
        }
        if ('is' == $verb) {
            return count($array_or_object) > 1 ? 'are' : 'is';
        }
    }

    function isMultidimensional($var) {
        //return (count($array) != count($array, COUNT_RECURSIVE));
        $return = false;
        if (is_array($var) || is_object($var)) {

            foreach ($var as $v) {
                if (is_array($v) || is_object($v)) {
                    $return = true;
                    break;
                }
            }
        }
        return $return;
    }

    function convertOption($data = array(), $selected = '', $f = array('id', 'name')) {
        $ns = '';
        if ($this->isMultidimensional($data)) {
            foreach ($data as $k => $v) {
                if ($selected == $v->{$f[0]}) {
                    $ns.='<option selected="selected" value="' . $v->{$f[0]} . '">' . $v->{$f[1]} . '</option>';
                } else {
                    $ns.='<option value="' . $v->{$f[0]} . '">' . $v->{$f[1]} . '</option>';
                }
            }
        } else {
            foreach ($data as $k => $v) {
                if ($selected == $k) {
                    $ns.='<option selected="selected" value="' . $k . '">' . $v . '</option>';
                } else {
                    $ns.='<option value="' . $k . '">' . $v . '</option>';
                }
            }
        }
        return $ns;
    }

    function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest");
    }

    function arraySearch($array, $key, $value) {
        $returnType = "array";
        if (is_object($array)) {
            $array = (array) $array;
            $returnType = "object";
        }

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                return ($returnType == "object") ? (object) $array : $array;
            }

            $results = array();
            $returnType = "array";
            foreach ($array as $subarray) {
                if (is_object($subarray)) {
                    $returnType = "object";
                    $subarray = (array) $subarray;
                }
                if (isset($subarray[$key]) && $subarray[$key] == $value) {
                    $results = $subarray;
                    break;
                }
                $returnType = "array";
            }
            return ($returnType == "object") ? (object) $results : $array;
        }

        return false;
    }

    function userTypes($return_which = '', $options = array()) {
        $cl = array(
            //"General user" => "General user", "Employee" => "Employee", "Subscriber" => "Subscriber",
            "Administrator" => "Administrator", "User" => "User"
                //"Editor" => "Editor", "Author" => "Author", "Contributor" => "Contributor"
        );
        switch ($return_which) {
            case "key":
                $final_data = array();
                foreach ($cl as $k => $v) {
                    $final_data[] = $k;
                }
                break;
            case "value":
                $final_data = array();
                foreach ($cl as $k => $v) {
                    $final_data[] = $v;
                }
                break;
            case "filter_value":
                $existing_value = user_credit_list('value');
                $final_data = array();
                foreach ($options as $v) {
                    if (in_array($v, $existing_value)) {
                        $final_data[] = $v;
                    }
                }
                break;
            default:
                $final_data = $cl;
                break;
        }
        return $final_data;
    }




    function warning($prefix = '', $postfix = '', $remove = true) {
        $instance = & get_instance();
        $warningCls = 'warning';
        $warningKey = 'warning';
        $str = '';
        if ($warning = $instance->ahrsession->get('warning')) {
            
        } else if ($warning = $instance->ahrsession->get('warningFail')) {
            $warningCls = 'warningFailMessage';
            $warningKey = 'warningFail';
        } else if ($warning = $instance->ahrsession->get('warningSuccess')) {
            $warningCls = 'warningSuccessMessage';
            $warningKey = 'warningSuccess';
        }
        if ($remove === true)
            $instance->ahrsession->delete($warningKey);

        if (empty($warning))
            return FALSE;

        if (is_array($warning) || is_object($warning)) {
            if (empty($postfix))
                $postfix = '<br/>';
            $str = $prefix . implode($postfix, $warning);
        }
        else if (is_string($warning)) {
            $str = $prefix . $warning . $postfix;
        }
        return $str;
    }

    function warningMessage($prefix = '', $postfix = '', $remove = true) {
        $instance = & get_instance();
        $warningCls = 'warning';
        $warningKey = 'warning';
        $str = '';
        if ($warning = $instance->ahrsession->get('warning')) {
            
        } else if ($warning = $instance->ahrsession->get('warningFail')) {
            $warningCls = 'warningFailMessage';
            $warningKey = 'warningFail';
        } else if ($warning = $instance->ahrsession->get('warningSuccess')) {
            $warningCls = 'warningSuccessMessage';
            $warningKey = 'warningSuccess';
        }
        if ($remove === true)
            $instance->ahrsession->delete($warningKey);

        if (empty($warning))
            return FALSE;

        if (is_array($warning) || is_object($warning)) {
            if (empty($postfix))
                $postfix = '<br/>';
            $str = $prefix . implode($postfix, $warning);
        }
        else if (is_string($warning)) {
            $str = $prefix . $warning . $postfix;
        }
        $str = '<div class="' . $warningCls . '" style="">' . $str . '</div>';
        ;
        return $str;
    }


    var $googleCountryList = '{"":"Country (Any Region)","countryAF":"Afghanistan","countryAL":"Albania","countryDZ":"Algeria","countryAS":"American Samoa","countryAD":"Andorra","countryAO":"Angola","countryAI":"Anguilla","countryAQ":"Antarctica","countryAG":"Antigua and Barbuda","countryAR":"Argentina","countryAM":"Armenia","countryAW":"Aruba","countryAU":"Australia","countryAT":"Austria","countryAZ":"Azerbaijan","countryBS":"Bahamas","countryBH":"Bahrain","countryBD":"Bangladesh","countryBB":"Barbados","countryBY":"Belarus","countryBE":"Belgium","countryBZ":"Belize","countryBJ":"Benin","countryBM":"Bermuda","countryBT":"Bhutan","countryBO":"Bolivia","countryBA":"Bosnia and Herzegovina","countryBW":"Botswana","countryBV":"Bouvet Island","countryBR":"Brazil","countryIO":"British Indian Ocean Territory","countryVG":"British Virgin Islands","countryBN":"Brunei","countryBG":"Bulgaria","countryBF":"Burkina Faso","countryBI":"Burundi","countryKH":"Cambodia","countryCM":"Cameroon","countryCA":"Canada","countryCV":"Cape Verde","countryKY":"Cayman Islands","countryCF":"Central African Republic","countryTD":"Chad","countryCL":"Chile","countryCN":"China","countryCX":"Christmas Island","countryCC":"Cocos [Keeling] Islands","countryCO":"Colombia","countryKM":"Comoros","countryCD":"Congo [DRC]","countryCG":"Congo [Republic]","countryCK":"Cook Islands","countryCR":"Costa Rica","countryCI":"C\u00f4te d\u2019Ivoire","countryHR":"Croatia","countryCU":"Cuba","countryCY":"Cyprus","countryCZ":"Czech Republic","countryDK":"Denmark","countryDJ":"Djibouti","countryDM":"Dominica","countryDO":"Dominican Republic","countryEC":"Ecuador","countryEG":"Egypt","countrySV":"El Salvador","countryGQ":"Equatorial Guinea","countryER":"Eritrea","countryEE":"Estonia","countryET":"Ethiopia","countryFK":"Falkland Islands [Islas Malvinas]","countryFO":"Faroe Islands","countryFJ":"Fiji","countryFI":"Finland","countryFR":"France","countryGF":"French Guiana","countryPF":"French Polynesia","countryTF":"French Southern Territories","countryGA":"Gabon","countryGM":"Gambia","countryGE":"Georgia","countryDE":"Germany","countryGH":"Ghana","countryGI":"Gibraltar","countryGR":"Greece","countryGL":"Greenland","countryGD":"Grenada","countryGP":"Guadeloupe","countryGU":"Guam","countryGT":"Guatemala","countryGN":"Guinea","countryGW":"Guinea-Bissau","countryGY":"Guyana","countryHT":"Haiti","countryHM":"Heard Island and McDonald Islands","countryHN":"Honduras","countryHK":"Hong Kong","countryHU":"Hungary","countryIS":"Iceland","countryIN":"India","countryID":"Indonesia","countryIR":"Iran","countryIQ":"Iraq","countryIE":"Ireland","countryIL":"Israel","countryIT":"Italy","countryJM":"Jamaica","countryJP":"Japan","countryJO":"Jordan","countryKZ":"Kazakhstan","countryKE":"Kenya","countryKI":"Kiribati","countryKW":"Kuwait","countryKG":"Kyrgyzstan","countryLA":"Laos","countryLV":"Latvia","countryLB":"Lebanon","countryLS":"Lesotho","countryLR":"Liberia","countryLY":"Libya","countryLI":"Liechtenstein","countryLT":"Lithuania","countryLU":"Luxembourg","countryMO":"Macau","countryMK":"Macedonia [FYROM]","countryMG":"Madagascar","countryMW":"Malawi","countryMY":"Malaysia","countryMV":"Maldives","countryML":"Mali","countryMT":"Malta","countryMH":"Marshall Islands","countryMQ":"Martinique","countryMR":"Mauritania","countryMU":"Mauritius","countryYT":"Mayotte","countryMX":"Mexico","countryFM":"Micronesia","countryMD":"Moldova","countryMC":"Monaco","countryMN":"Mongolia","countryMS":"Montserrat","countryMA":"Morocco","countryMZ":"Mozambique","countryMM":"Myanmar [Burma]","countryNA":"Namibia","countryNR":"Nauru","countryNP":"Nepal","countryNL":"Netherlands","countryNC":"New Caledonia","countryNZ":"New Zealand","countryNI":"Nicaragua","countryNE":"Niger","countryNG":"Nigeria","countryNU":"Niue","countryNF":"Norfolk Island","countryKP":"North Korea","countryMP":"Northern Mariana Islands","countryNO":"Norway","countryOM":"Oman","countryPK":"Pakistan","countryPW":"Palau","countryPS":"Palestinian Territories","countryPA":"Panama","countryPG":"Papua New Guinea","countryPY":"Paraguay","countryPE":"Peru","countryPH":"Philippines","countryPN":"Pitcairn Islands","countryPL":"Poland","countryPT":"Portugal","countryPR":"Puerto Rico","countryQA":"Qatar","countryRE":"R\u00e9union","countryRO":"Romania","countryRU":"Russia","countryRW":"Rwanda","countrySH":"Saint Helena","countryKN":"Saint Kitts and Nevis","countryLC":"Saint Lucia","countryPM":"Saint Pierre and Miquelon","countryVC":"Saint Vincent and the Grenadines","countryWS":"Samoa","countrySM":"San Marino","countryST":"S\u00e3o Tom\u00e9 and Pr\u00edncipe","countrySA":"Saudi Arabia","countrySN":"Senegal","countryRS":"Serbia","countrySC":"Seychelles","countrySL":"Sierra Leone","countrySG":"Singapore","countrySK":"Slovakia","countrySI":"Slovenia","countrySB":"Solomon Islands","countrySO":"Somalia","countryZA":"South Africa","countryGS":"South Georgia and the South Sandwich Islands","countryKR":"South Korea","countryES":"Spain","countryLK":"Sri Lanka","countrySD":"Sudan","countrySR":"Suriname","countrySJ":"Svalbard and Jan Mayen","countrySZ":"Swaziland","countrySE":"Sweden","countryCH":"Switzerland","countrySY":"Syria","countryTW":"Taiwan","countryTJ":"Tajikistan","countryTZ":"Tanzania","countryTH":"Thailand","countryTG":"Togo","countryTK":"Tokelau","countryTO":"Tonga","countryTT":"Trinidad and Tobago","countryTN":"Tunisia","countryTR":"Turkey","countryTM":"Turkmenistan","countryTC":"Turks and Caicos Islands","countryTV":"Tuvalu","countryUM":"U.S. Minor Outlying Islands","countryVI":"U.S. Virgin Islands","countryUG":"Uganda","countryUA":"Ukraine","countryAE":"United Arab Emirates","countryGB":"United Kingdom","countryUS":"United States","countryUY":"Uruguay","countryUZ":"Uzbekistan","countryVU":"Vanuatu","countryVA":"Vatican City","countryVE":"Venezuela","countryVN":"Vietnam","countryWF":"Wallis and Futuna","countryEH":"Western Sahara","countryYE":"Yemen","countryZM":"Zambia","countryZW":"Zimbabwe"}';

    function getGoogleSearchCountryOptions($selected = '') {
        $countries = $this->googleCountryList;
        $options = '';
        foreach (json_decode($countries) as $k => $v) {
            if ($selected == $k)
                $options .= '<option selected="selected" value="' . $k . '">' . $v . '</option>';
            else
                $options .= '<option value="' . $k . '">' . $v . '</option>';
        }
        return $options;
    }
    function getGoogleSearchCountryList() {
        $countries = json_decode($this->googleCountryList);
        return $countries;
    }

    function getGoogleSearchCountryName($key = '') {
        if (empty($key))
            return FALSE;
        $country = json_decode($this->googleCountryList);
        return @$country->{$key};
    }


    /** 
     * Site related
     * @param mixed slug page id or slug
     * @param string $name Description
     */
    function getPage($slug, $type='page'){
        $conditions = array();
        is_int($slug)? ($conditions['id'] = $slug) : ($conditions['slug'] = $slug);        
        if(empty($conditions)){
            return $response = new ahrvirtual_property();
        }
        $conditions['type'] = $type;
        $CI = & get_instance();
        $CI->load->model('Page_model', 'Page');
         $response = new ahrvirtual_property($CI->Page->row(array(
            'conditions' => $conditions,
            'order' => array('created' => "DESC"))));
         return $response;
    }

}
