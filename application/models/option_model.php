<?php

class Option_model extends App_model {

    public $_table_name = 'options';
    public $_alias = 'Option';
    public $_validate = array();

    function __construct() {
        parent::__construct();
        $this->setup();
    }

    function getOption($name) {
        $row = $this->row(array('conditions' => array("option_name" => $name)));
        if (!empty($row)) {
            return $row->value;
        }
        return '';
    }

    function getOptions($name, $all_auto_load_options = false) {
        $response = array();
        if ($all_auto_load_options) {
            $rows = $this->result(array('conditions' => array("autoload" => TRUE), 'fields' => array('option_name', 'option_value')));
        } else {
            $rows = $this->result(array('conditions' => array("option_name" => $name)));
        }
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $response[$row->name] = $row->value;
            }
        }
        return $response;
    }

    function saveOption($name, $value = '', $autoload = '0') {
        if (is_array($name))
            extract($name, EXTR_OVERWRITE);
        if (!@$name)
            return false;
        $data = array('option_name' => $name, 'option_value' => $value, 'autoload' => $autoload);
        if (@$option_for)
            $data['option_for'] = $option_for;
        if (@$reference_type)
            $data['reference_type'] = $reference_type;
        if (@$reference_id)
            $data['reference_id'] = $reference_id;
        $row = $this->row(array('conditions' => array("option_name" => $name)));
        if (!empty($row)) {
            $data['id'] = $row->id;
        }
        $this->save($data, false);
        return true;
    }

    function deleteOption($name) {
        $row = $this->row(array('conditions' => array("option_name" => $name)));
        if (!empty($row)) {
            $this->delete($row->id);
        }
        return true;
    }

}
