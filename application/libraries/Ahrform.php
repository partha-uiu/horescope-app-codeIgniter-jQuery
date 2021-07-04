<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ahrform {

    public $ahr; // project instance define personaly
    public $action = false;
    public $status = false;
    public $ci = '';
    private $_template = '';
    private $_input_default = array();

    public function __construct($params = '') {
        $this->initialize($params)->set_error_delimiters();
    }

    public function initialize($params = '') {
        $CI = & get_instance();
        $this->set(array('class' => $CI->router->class, 'method' => $CI->router->method));
        $this->set($params)->set($_REQUEST);
        return $this;
    }

    public function get($name) {
        if (!property_exists($this, $name))
            return false;
        return $this->{$name};
    }

    public function set($name, $value = '') {
        if (empty($name))
            return $this;
        else if (is_array($name) || is_object($name)) {
            $resetParams = $name;
            foreach ($resetParams as $key => $val)
                $this->{$key} = $val;
        }
        else
            $this->{$name} = $value;
        return $this;
    }

    /*
     * Delete multiple value by calling
     * delete($name = 'title')
     * delete(array('title', 'description'))
     * delete('title', 'description')
     *
     */

    public function delete($name = '') {
        if (func_num_args() > 0) {
            $fields = is_array(func_get_arg(0)) || is_object(func_get_arg(0)) ? func_get_arg(0) : func_get_args();
            foreach ($fields as $field) {
                if (property_exists($this, $field))
                    unset($this->{$field});
            }
        }
        return $this;
    }

    function convertOption($data = array(), $selected = '', $f = array('id', 'name')) {
        $ns = '';
        if ($this->is_multidimensional($data)) {
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

    function is_multidimensional($data = array()) {

        $rv = array_filter($data, 'is_array');
        $rv2 = array_filter($data, 'is_object');
        if (count($rv) > 0 || count($rv2) > 0) {
            return true;
        }
        return false;
    }

    function set_template($template) {
        $this->_template = $template;
    }

    function set_input_default($val = array()) {
        $this->_input_default = array_merge($this->_input_default, $val);
    }

    /*
     * given parameter string|array     *
     * @param $data string|array
     * @return string label tag
     */

    function label($data = array()) {
        if (empty($data))
            return false;

        if (is_string($data)) {
            $data = array(
                'text' => Inflector::humanize($data),
                'for' => Inflector::slug($data)
            );
        } else if (is_array($data)) {
            if (isset($data['text'])) {
                $data['text'] = Inflector::humanize($data['text']);
                if (!isset($data['for']))
                    $data['for'] = Inflector::slug($data['text']);
            }
        }
        $data = array_merge(empty($this->_input_default['label']) ? array() : $this->_input_default['label'], $data);

        $text = @$data['text'];
        if (isset($data['text']))
            unset($data['text']);


        $html = array();
        $html[] = '<label';
        foreach ($data as $attribute => $value) {
            $html[] = "$attribute=\"$value\"";
        }
        $html[] = ">$text</label>";
        return implode(' ', $html);
    }

    function error($input) {
        return form_error($input);
    }

    function set_error_delimiters($prefix = '<span class="help-inline error">', $suffix = '</span>') {
        $CI = &get_instance();
        $CI->form_validation->set_error_delimiters($prefix, $suffix);
        return $this;
    }

    function _input_attribute($input = array()) {
        if (!(is_array($input) || is_object($input)))
            return '';

        $html = array();
        foreach ($input as $attribute => $attr_value) {
            $html[] = "$attribute=\"$attr_value\"";
        }
        return $inputField = implode(' ', $html);
    }

    function input($inputs) {
        if (empty($inputs))
            return false;

//        if (is_string($inputs)) {
//            $strs = explode(',', $inputs);
//            $inputs = array();
//            foreach ($strs as $str) {
//                $inputs = array('name' => $str);
//            }
//        }

//        if (isset($inputs['name'])) {
//            foreach ($strs as $str) {
//                $inputs = array('name' => $str);
//            }
//        }


        if (isset($inputs['name'])) {
            $inputs = array($inputs);
        } elseif (!$this->is_multidimensional($inputs)) {
            $inputs = array($inputs);
        }

        $dataForRender = array();
        foreach ($inputs as $input) {
            $html = array();
            $input = is_string($input) ? array('name' => $input) : (is_object($input) ? (array)$input : $input);
            $input = array_merge(empty($this->_input_default['input']) ? array() : $this->_input_default['input'], $input);

            if (!isset($input['label'])) {
                $input['label'] = $this->label($input['name']);
            } else {
                $input['label'] = $this->label(is_string($input['label']) ? array('text' => $input['label'], 'for' => $input['name']) : $input['label']);
            }
            $label = $input['label'];
            unset($input['label']);

            if (!isset($input['id']))
                $input['id'] = Inflector::slug($input['name']);


            if (!isset($input['value']) && isset($input['default']))
                $value = @$input['default'];
            elseif ($this->get($input['name']))
                $value = $this->get($input['name']);
            else
                $value = @$input['value'];

            $error = $this->error($input['name']);

            if (isset($input['default'])) {
                $default = $input['default'];
                unset($input['default']);
            }

            $template = $this->_template;

            if (isset($input['template']) && $input['template'] == false)
                $template = FALSE;
            elseif (isset($input['template']) && !empty($input['template']))
                $template = $input['template'];

            if (isset($input['template']))
                unset($input['template']);


            $input['type'] = isset($input['type']) ? $input['type'] : 'text';

            if ($input['type'] == 'textarea') {
                $inputField = '<textarea ' . $this->_input_attribute($input) . " >$value</textarea>";
            } elseif ($input['type'] == 'select') {
                $empty = false;
                if (isset($input['empty'])) {
                    $empty = $input['empty'];
                    unset($input['empty']);
                }

                $options = array();
                if (isset($input['options'])) {
                    $options = $input['options'];
                    unset($input['options']);
                }

                if (is_object($options) || is_string($options))
                    $options = (array) $options;

                $html[] = '<select ' . $this->_input_attribute($input) . " >";
                if ($empty !== FALSE)
                    $html[] = "<option value=\"\">" . (is_string($empty) ? $empty : '') . "</option>";
                $html[] = $this->convertOption($options, $value);
                $html[] = "</select>";
                $inputField = implode(' ', $html);
            }
            else if (preg_match('/checkbox|radio/', $input['type'])) {
                if (!empty($input['defaultChecked']) || $this->get($input['name'])) {
                    $input['checked'] = 'checked';
                }
                if (!$value) {
                    $value = 1;
                }

                if ($value)
                    $input['value'] = $value;
                $inputField = '<input ' . $this->_input_attribute($input) . ">";

                if ( FALSE !== @$input['hiddenField']) {
                    $input2 = $input;
                    $input2['id'] .= '_';
                    $input2['type'] = 'hidden';
                    $input2['value'] = isset($input2['hiddenField']) ? $input2['hiddenField'] : '0' ;
                    $inputField = '<input ' . $this->_input_attribute($input2) . ">" . $inputField;
                }
            }
            else {
//            if (preg_match('/text|file|password|checkbox|radio|hidden|color|date|datetime|datetime-local|email|month|quantity|range|search|tel|time|url|week/', $input['type'])) {
                if ($value)
                    $input['value'] = $value;
                $inputField = '<input ' . $this->_input_attribute($input) . ">";
            }



            if ($template != FALSE) {
                $dataForRender[] = str_replace(array('{label}', '{input}', '{error}'), array($label, $inputField, $error), $this->_template);
            } else {
                $dataForRender[] = $label . $inputField . $error;
            }
        }

        $dataForRender = implode('', $dataForRender);

        return $dataForRender;
    }

}