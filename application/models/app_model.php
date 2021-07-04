<?php

class App_model extends CI_Model {

     public $validate = array();
     public $_table_name = array();
     public $_fields_array = array();
     public $_alias = '';
     public $_validation_errors = '';
     public $_validate = array();
     public $_belongs_to = array();

     function __construct() {
           parent::__construct();
//        $this->setup();
     }

     function randomAlphaNumber($length = 4, $case = "both") {
           $original_string = array_merge(range('a', 'z'),range(0, 9),range('A', 'Z'));
           $original_string = implode("", $original_string);

           $case = strtolower($case);
           if ($case == 'lower')
                 $original_string = strtolower($original_string);
           if ($case == 'upper')
                 $original_string = strtoupper($original_string);

           return substr(str_shuffle($original_string), 0, $length);
     }

     function randomNumber($length = 4) {
           $original_string = array_merge(range(0, 9), range(0, 9), range(0, 9));
           $original_string = implode("", $original_string);
           return substr(str_shuffle($original_string), 0, $length);
     }

     function get_validation_errors() {
           return $this->_validation_errors;
     }

     function create() {
           $clone = clone $this;
           return $clone->setup();
     }

     function setup() {
           //loadDBFields if not loaded previous
           if (empty($this->_fields_array)) {
                 $this->_table_name = $this->db->dbprefix($this->_table_name);
                 $columns = $this->db->query('SHOW COLUMNS FROM ' . $this->_table_name)->result();
                 foreach ($columns as $column) {
                       $this->_fields_array[] = $column->Field;
                 }
           }
           //set up fields
           if (!empty($this->_fields_array)) {
                 foreach ($this->_fields_array as $field) {
                       if (isset($this->{$field}))
                             unset($this->{$field});
                 }
           }

           $this->_validation_errors = '';
           //set default value if not set
           $temp = array(
               'site_id' => $this->config->item('site_id'),
               'created' => date('Y-m-d h:i:s'),
               'modified' => date('Y-m-d h:i:s')
           );
           foreach ($temp as $name => $value) {
                 if (!property_exists($this, $name) && in_array($name, $this->_fields_array)) {
                       $this->{$name} = $value;
                 }
           }
           return $this;
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
                 return $this;
           else if (is_array($name) || is_object($name))
                 $resetParams = $name;
           else
                 $resetParams = array($name => $value);

           foreach ($resetParams as $key => $val) {
                 $this->{$key} = $val;
           }
           return $this;
     }

     /*
      * @uses
      *    $this->mode_name->remove('password', 'username', 'email');
      */

     public function remove($name = '') {
           if (func_num_args() > 0) {
                 $fields = is_array(func_get_arg(0)) || is_object(func_get_arg(0)) ? func_get_arg(0) : func_get_args();
                 foreach ($fields as $field) {
                       if (property_exists($this, $field))
                             unset($this->{$field});
                 }
           }
           return $this;
     }

     //$onlyFieldValue means "table field name =form input name". if same then load.
     function load_input_value($values = array(), $onlyFieldValue = true) {
           $values = !empty($values) ? $values : $_REQUEST;
           if ($onlyFieldValue === true) {
                 foreach ($values as $n => $v) {
                       if (in_array($n, $this->_fields_array))
                             $this->set($n, $v);
                 }
           }
           else
                 $this->set($values);
           return $this;
     }

     /*
      * This function will return all input value associate by this    request method
      */

     function getValues() {
           $temp = array();
           foreach ($this->_fields_array as $n) {
                 if (isset($this->{$n})) {
                       $temp[$n] = $this->{$n};
                 }
           }
           return $temp;
     }

     function filterSaveFieldOnly($values = array(), $filterFieldsOnly = array()) {
           if (empty($filterFieldsOnly))
                 return $values;

           $temp = array();
           foreach ($values as $field => $value) {
                 if (in_array($field, $filterFieldsOnly))
                       $temp[$field] = $value;
           }
           return $temp;
     }

     /*
      * Save data to database
      *
      * @param array key pair value like array('id' => 5, 'name'=> 'Anwar Hossain', 'gender' => 'Male' )
      * @param bool possible value TURE|FALSE
      * @param array list field name want to save like array('name'). So only save name field.
      * @param array key pair value. Such as you want to update condationaly like your conditions array('id' => 5, 'gender' => 'Male' ) for thsis you want to update array('name' => 'Rana' )
      *
      * @uses ->save(array, true|false, array|NULL|false, array)
      */

     function save($values = array(), $vlidate = true, $saveFieldOnly = array(), $conditions = array()) {
           if (!empty($vlidate)) {
                 $valid = $this->validate(is_array($vlidate) ? $vlidate : array());
                 if (!$valid)
                       return $valid; //return true|false
           }

           if (!empty($conditions['id'])) {
                 $this->set('id', $conditions['id']);
           }

           $return = FALSE;
           $form_value = $this->set($values)->getValues();
           $form_value = $this->filterSaveFieldOnly($form_value, $saveFieldOnly);

           if (empty($this->id)) {
                 if (isset($form_value['id']))
                       unset($form_value['id']);

                 $return = $this->db->insert($this->_table_name, $form_value);
                 $this->id = $this->db->insert_id();
           } else {
                 if (isset($form_value['created']))
                       unset($form_value['created']);

                 $conditions['id'] = $this->id;
                 if (!empty($form_value)) {
                       $this->_setWhereCondition(array('conditions' => $conditions));
                       if (isset($form_value['id']))
                             unset($form_value['id']);

                       $this->db->set($form_value);
                       $return = $this->db->update($this->_table_name);
                 }
           }
           return $return;
     }

     function saveAll($values = array(), $vlidate = true, $saveFieldOnly = array(), $conditions = array()) {
           if (empty($values))
                 return false;

           foreach ($values as $values_) {
                 $this->save($values_, $vlidate, $saveFieldOnly, $conditions);
           }
     }

     function getData($conditions = array(), $singleResult = false) {
           if (!empty($conditions)) {
                 $this->db->select("$this->_alias.*", false)->from("$this->_table_name {$this->_alias}")->where($conditions);
                 if ($singleResult == true)
                       return $this->db->get()->row();
                 else
                       return $this->db->get()->result();
           }
           return false;
     }

     function validate($set_rule = '') {
           if (count($_POST) == 0) {
                 return FALSE;
           }

           //if validation field has submited by form
           if (empty($this->_validate) && empty($set_rule))
                 return true;


           //set validation roles by model setting
           if (!empty($this->_validate)) {
                 $validationRule = array();
                 foreach ($this->_validate as $rule) {
                       $validationRule[] = isset($_POST[$rule['field']]) ? $rule : '';
                 }
                 $this->form_validation->set_rules(array_filter($validationRule));
           }

           if (!empty($set_rule) && is_array($set_rule))
                 $this->form_validation->set_rules($set_rule);


           $validate = $this->form_validation->run();
           $this->_validation_errors = validation_errors();
           return $validate;
     }

     public function get_errors() {
           if (empty($this->_validation_errors))
                 $this->_validation_errors = array();
           return $this->_validation_errors;
     }

     function count($condition = array()) {
           $con = array();
           if (is_numeric($condition))
                 $con['conditions'][$this->_alias . '.id'] = $condition;
           else
                 $con = $condition;

           $this->_setSelect($con)->_setWhereCondition($con)->_setOrder($con);
           $data = $this->db->from("$this->_table_name AS {$this->_alias}")->get()->num_rows();
           return $data;
     }

     function row($condition = array()) {
           $con = array();
           if (is_numeric($condition))
                 $con['conditions'][$this->_alias . '.id'] = $condition;
           else
                 $con = $condition;

           $data = $this->_setSelect($con)->_setWhereCondition($con)->_setOrder($con)->_limit($con)->_result($type = 'object', $single = true);
           return $data;
     }

     function result($condition = array()) {
           $con = array();
           if (is_numeric($condition))
                 $con['conditions'][$this->_alias . '.id'] = $condition;
           else
                 $con = $condition;

           $data = $this->_setSelect($con)->_setWhereCondition($con)->_setOrder($con)->_limit($con)->_result('object');
           return empty($data) ? array() : $data;
     }

     function result_array($condition = array()) {
           $con = array();
           if (is_numeric($condition))
                 $con['conditions'][$this->_alias . '.id'] = $condition;
           else
                 $con = $condition;

           $data = $this->_setSelect($con)->_setWhereCondition($con)->_setOrder($con)->_limit($con)->_result('array');
           return empty($data) ? array() : $data;
           ;
     }

     public function _limit($value = array(), $offset = '') {
           if (!empty($value['limit']))
                 $value = $value['limit'];
           if (is_numeric($value) || is_string($value))
                 $this->db->limit($value, $offset);
           return $this;
     }

     function _result($type = 'object', $single = false) {
           $table_name = $this->_table_name;

           if ($this->db->dbprefix) {
                 if (preg_match('/^' . $this->db->dbprefix . '/', $table_name))
                       $table_name = preg_replace('/^' . $this->db->dbprefix . '/', '', $table_name);
           }


           if ($single === true) {
                 $type = "row_{$type}";
                 return $this->db->from("$table_name AS `{$this->_alias}`")->get()->$type();
           } else {
                 $type = "result_{$type}";
                 return $this->db->from("$table_name AS `{$this->_alias}`")->get()->$type();
           }
     }

     function _setSelect($conditions = array(), $escape = FALSE) {
           if (!empty($conditions['fields']))
                 $this->db->select($conditions['fields'], $escape);

           return $this;
     }

     function _setOrder($conditions = array()) {
           if (empty($conditions))
                 return $this;

           if (!empty($conditions['order']) && is_string($conditions['order'])) {
                 $this->db->order_by($conditions['order']);
           } elseif (!empty($conditions['order']) && is_array($conditions['order'])) {
                 foreach ($conditions['order'] as $field => $order) {
                       if (is_numeric($field))
                             $this->db->order_by($order);
                       else
                             $this->db->order_by($field, $order);
                 }
           }
           return $this;
     }

     function _belongsTo() {
           if (empty($this->_belongs_to))
                 return $this;

           extract(array(
               'table' => '',
               'alias' => '',
               'foreign_key' => '',
               'conditions' => '', //string|array,
               'ondelete' => '', //cascade
           ));
           extract($this->_belongs_to, EXTR_OVERWRITE);

//            $table = $this->db->dbprefix($table);
           !empty($alias) OR $alias = $table;

           pr($this->_alias);

           $curalias = !empty($this->_alias) ? $this->_alias : $this->db->dbprefix($this->_table_name);
           pr("{$curalias}.{$foreign_key} = {$alias}.id");
           $this->db->join("$table AS `$alias`", "`{$curalias}`.`{$foreign_key}` = `{$alias}`.`id`", 'LEFT');
           return $this;
     }

     function _setWhereCondition($conditions = array()) {
           if (empty($conditions))
                 return $this;
           $conditions = (isset($conditions['conditions'])) ? $conditions['conditions'] : array();

           if (empty($conditions))
                 return $this;
           if (isset($conditions['order']))
                 unset($conditions['order']);


           foreach ($conditions as $key => $val) {
                 if ($key === 'OR') {
                       if (is_array($val)) {
//                              foreach ($val as $k => $v) {
                             $this->db->or_where($val);
//                              }
                       }
                 } else if ($key === 'NOT') {
                       if (is_array($val)) {
                             foreach ($val as $k => $v) {
                                   $this->db->where_not_in($k, $v);
                             }
                       }
                 } else if (is_numeric($key)) {
                       $this->db->where($val, false, false);
                 } else if (is_array($val)) {
                       $this->db->where_in($key, $val);
                 } else {
                       $this->db->where($key, (is_numeric($val) ? $val : "'$val'"), FALSE);
                 }
           }
           return $this;
     }

     function get_list($condition = array()) {
           $con = array();
           is_numeric($condition) ? $con['conditions'][$this->_alias . '.id'] = $condition : $con = $condition;

           $fields = !empty($con['fields']) ? $con['fields'] : array_filter(array(
                       in_array('id', $this->_fields_array) ? 'id' : '',
                       in_array('name', $this->_fields_array) ? 'name' : '',
                       !in_array('name', $this->_fields_array) && in_array('title', $this->_fields_array) ? 'title' : '',
           ));
           $con['fields'] = $fields;

           $items = $this->_setSelect($con)->_setWhereCondition($con)->_setOrder($con)->_limit($con)->_result('object');

           $return = array();
           foreach ($items as $item) {
                 if (count($fields) == 1) {
                       if (isset($item->{$fields[0]}))
                             $return[] = $item->{$fields[0]};
                 }
                 elseif (count($fields) > 1) {
                       $return[@$item->{$fields[0]}] = @$item->{$fields[1]};
                 }
           }
           return empty($return) ? array() : $return;
     }

     function delete($condition = array()) {
           $con = array();
           if (is_numeric($condition))
                 $con['conditions']['id'] = $condition;
           else
                 $con = $condition;

           $this->_setWhereCondition($con);

           $data = $this->db->delete("$this->_table_name");
           return $data;
     }

     function deleteAll($condition = array()) {
           return $this->delete($condition);
     }

     function get_last_query() {
           return $this->db->last_query();
     }

     /*
      * @param string  $field
      * @param string  $value
      * @param array|object $field
      *
      * is_field_exist($field, $value)
      * is_field_exist(array('conditions' => array($field => $val))
      *
      * */

     public function is_field_exist($field, $value = '') {
           $field = is_object($field) ? (array) $field : $field;
           $conditions = (is_string($field) && $field) ? array('conditions' => array($field => $value)) : $field;
           return $response = $field && $this->count($conditions) > 0;
     }

     /*
      * @param string  $field
      * @param string  $value
      * @param array|object $field
      *
      * is_field_exist($field, $value)
      * is_field_exist(array('conditions' => array($field => $val))
      *
      * */

     public function exist($field, $value = '') {
           $field = is_object($field) ? (array) $field : $field;
           $conditions = (is_string($field) && $field) ? array('conditions' => array($field => $value)) : $field;
           return $response = $field && $this->count($conditions) > 0;
     }

     /**
      * get all fields of table associate by table.
      *
      * @param string|bool $prefix valid value TRUE|FALSE|{custom name}
      *    - FALSE -> to use table name for table alias according to field prefix
      *    - TRUE -> to use table alias defined in model for table alias according to field prefix
      *    - String -> Custom alias name for table alias according to field prefix
      *
      * @param bool $sqlalis valid value TRUE|FALSE
      *    - FALSE -> to get field name with alias (alias is "user" here) like array("user_first_name", "user_last_name");
      *    - TRUE -> to get field name with alias (alias is "user" here) like array("user.first_name AS user_first_name", "user.last_name AS user_last_name");
      *
      * @param bool $returnAsString valid value TRUE|FALSE
      *    - FALSE -> to get all fields as array;
      *    - TRUE -> to get all fields as array like "user.first_name AS user_first_name, user.last_name AS user_last_name";
      *
      */
     public function getFields($prefix = TRUE, $sqlalis = FALSE, $returnAsString = false) {
           $fields = array();
           $response = array();
           $fieldseAlias = array();
           $palias = (FALSE == $prefix) ? $this->_table_name : (TRUE === $prefix ? $this->_alias : $prefix);

           $fields = $this->_fields_array;
           if ($prefix) {
                 $fields = array();
                 foreach ($this->_fields_array as $field) {
                       $fields[] = "{$palias}.{$field}";
                       $fieldseAlias[] = "{$palias}.{$field} AS {$palias}_{$field}";
//                        $fieldseAlias[] = "{$palias}.{$field} AS {$palias}_{$field}";
                 }
           }

           $response = TRUE === $sqlalis ? $fieldseAlias : $fields;
           if ($returnAsString)
                 $response = implode(', ', $response);

           return $response;
     }

     function toConditions($con = array(), $returnAsString = TRUE) {
           $response = array();

           if (is_string($con) || is_object($con))
                 $con = (array) $con;

           if (empty($con))
                 $con = array();

           else if (is_bool($con))
                 $con = array();

           foreach ($con as $key => $val) {
                 if (is_numeric($key)) {
                       $response[] = $val;
                 } else if (is_string($key)) {
                       $response[] = "$key = $val";
                 }
           }

           if ($returnAsString)
                 $response = implode(' AND ', $response);

           return $response;
     }

     function toOrderBy($con = array(), $returnAsString = TRUE) {
           $response = array();

           if (is_string($con) || is_object($con))
                 $con = (array) $con;

           if (empty($con))
                 $con = array();

           else if (is_bool($con))
                 $con = array();

           foreach ($con as $key => $val) {
                 if (is_numeric($key)) {
                       $response[] = $val;
                 } else if (is_string($key)) {
                       $response[] = "$key $val";
                 }
           }

           if ($returnAsString)
                 $response = implode(', ', $response);

           return $response;
     }

     function query($sql) {
           return $this->db->query($sql);
     }

     function table_name() {
           return $this->_table_name;
     }

}