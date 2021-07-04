<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Auto_layout_and_view {

    public $CI_data = array('title_for_layout' => '', 'ci_content' => '');
    public $applications = array();
    private $themeDIR = "theme";
    private $defaultLayout = "index";

    public function __construct() {
        
    }

    public function isHMVC() {
        $CI = & get_instance();
        $return = false;
        if ((!class_exists('Modules', false) && !class_exists('Modules', false)) || empty(Modules::$locations) || empty($CI->router->directory))
            return $return;

        foreach (Modules::$locations as $key => $val) {
            $mkstring = substr($CI->router->directory, 0, strlen($val));
            if ($mkstring == $val) {
                $return = true;
                break;
            }
        }
        return $return;
    }

    public function HMVCBasePath() {
        $CI = & get_instance();
        $return = dirname(BASEPATH) . '/' . APPPATH;
        foreach (Modules::$locations as $key => $val) {
            $mkstring = substr($CI->router->directory, 0, strlen($val));
            if ($mkstring == $val) {
                $return .= str_replace('../', '', $val);
                break;
            }
        };
        return rtrim($return, '/');
    }

    public function templateExists() {
        //Note: hmvc auto add hmvc view folder "module_name/views/"
        $CI = & get_instance();
        $preDir = explode('/', $this->filterHMVCDirectory());
        $preDir = (empty($preDir[0])) ? '' : ($preDir[0] . '/');
        $class = $CI->router->class;
        if (!isset($CI->template)) {
            return $preDir . $class . '/' . $CI->router->method;
        }

        $template = $CI->template;
        $templatePath =  APPPATH . 'views/' . $preDir . $class . '/' . $template . EXT;
        if (file_exists($templatePath)) {            
            $path = $preDir . $class . '/' . $template;            
            return $path;
        }

        $templatePath =  APPPATH . 'views/' . $preDir . $template . EXT;
        if (file_exists($templatePath)) {
            $path = $preDir . $template;
            return $path;
        }

        $templatePath =  APPPATH . 'views/' . $template . EXT;
        if (file_exists($templatePath)) {
            $path = $template;
            return $path;
        }


        $isHMVC = $this->isHMVC();
        if ($isHMVC) {
            $HMVCDir = $this->filterHMVCDirectory();
            $HMVCBasePath = $this->HMVCBasePath();
            $templatePath = $HMVCBasePath . '/' . $HMVCDir . '/views/' . $class . '/' . $template . EXT;
            if (file_exists($templatePath)) {
                return $class . '/' . $template;
            }
            $templatePath = $HMVCBasePath . '/' . $HMVCDir . '/views/' . str_replace($HMVCDir . '/', '', $template) . EXT;
            if (file_exists($templatePath)) {
                return $template;
            }
        }
        return $template;
    }

    public function getTemplateData() {
        /* view/template management ------------------------------------> */
        $CI = & get_instance();
        //if templete is disable
        if (isset($CI->template) && $CI->template === false)
            return false;
        return $CI->load->view($this->templateExists(), $this->ci_data, true);
    }

    public function getLayoutData() {
        /*         * *******************layout management *************************> */
        $CI = & get_instance();


        if (isset($CI->layout) && $CI->layout === false) {
            //if layout in active, then template data send in output
            return $this->ci_data['ci_content'];
        }


        $themeBasePath = dirname(BASEPATH) . '/' . APPPATH . 'views' . '/' . $this->themeDIR;
        $themeBasePath =  APPPATH . 'views' . '/' . $this->themeDIR;
        $current_theme = strtolower($this->getCurrentTheme());
        if (!file_exists($themeBasePath . '/' . $current_theme)) {
            die("Theme not found. Please make theme <b>$this->themeDIR/$current_theme</b>");
        }


        //if layout not set in controller
        if (empty($CI->layout)) {
            $layoutLocation = $this->themeDIR . '/' . $current_theme . '/' . $this->defaultLayout;
        } else {
            //Ex: user is set $this->layout="login", then pre: "theme/default/" , final: "theme/default/layout"
            if (file_exists($themeBasePath . '/' . $current_theme . '/' . $CI->layout . EXT)) {
                $layoutLocation = $this->themeDIR . '/' . $current_theme . '/' . $CI->layout;
            }
            //Ex: user is set $this->layout="default/product", then pre: "theme/" , final: "theme/default/product"
            else if (file_exists($themeBasePath . '/' . $CI->layout . EXT)) {
                $layoutLocation = $this->themeDIR . '/' . $CI->layout;
            }
            //Ex: user is set $this->layout="theme/default/blog", then pre: "" , final: "theme/default/blog"
            else
                $layoutLocation = $CI->layout;

            //Note: not need to added theme folder name like as "theme" and with theme name like as "theme/default".
            //just call the layout name from your current folder. Ex: login, product, blog etc
        }

        $temp_layoutLocation = explode('/', $layoutLocation);
        if(1 == count($temp_layoutLocation)){
            die("Layout not found. Please make layout <b>$this->themeDIR/$current_theme/{$layoutLocation}.php</b>");
        }
        return $CI->load->view($layoutLocation, $this->ci_data, true);
    }

    /*
     * remember by default application name and theme name is same. 
     * But it is different when user set $this->current_theme="theme name here"; then forcely theme is set
     * 
     */

    public function getCurrentTheme() {
        $CI = &get_instance();
        //if theme not is set in controller then auto detect
        $dt = $CI->config->item('defaultTheme');
        $theme = $dt ? $dt : 'default';

        $prefixes = $CI->config->item('prefixes');
        $currentPrefix = @$prefixes[CPREFIX];

        if (empty($CI->current_theme)) {
            $prefixes = $CI->config->item('prefixes');
            $currentPrefix = @$prefixes[CPREFIX];
            if ($currentPrefix) {
                $theme = is_array($currentPrefix) ? (!empty($currentPrefix['prefix']) ? $currentPrefix['prefix'] : $segments[0]) : $currentPrefix;
                if (is_array($currentPrefix)) {
                    $theme = empty($currentPrefix['theme']) ? $theme : $currentPrefix['theme'];
                }
            }
        } else {
            $theme = $CI->current_theme;
        }
        return $this->current_theme = $CI->current_theme = $theme;
    }

    /*
     * if no directory added then return false
     * ex: http://localhost/example/ here return ""
     * ex: http://localhost/example/admin here return "admin"
     * if is pre difine in config file
     * 
     */

    private function filterHMVCDirectory() {
        $CI = &get_instance();
        $return = $directory = $CI->router->directory;
        /* if ci hmvc extentions is installed */
        if ((!class_exists('Modules', false) && !class_exists('Modules', false)) || empty(Modules::$locations) || empty($directory))
            return $return;
        //this section is execute if CI HMVC enable/implement on this system
        foreach (Modules::$locations as $key => $val) {
            $mkstring = substr($directory, 0, strlen($val));
            if ($mkstring == $val) {
                $return = substr($directory, strlen($val), strlen($directory));
                break;
            }
        }
        $return = str_replace('/controllers/', '', $return);
        return $return;
    }

    public function setParams() {
        $CI = & get_instance();
        $this->ci_data['title_for_layout'] = isset($CI->title_for_layout) ? $CI->title_for_layout : '';
        /* params/variable management */
        if (!empty($CI->params) && !is_string($CI->params)) {
//            $this->ci_data = array_merge_recursive($this->ci_data, (is_object($CI->params) ? (array) $CI->params : $CI->params));
            $this->ci_data = array_merge($this->ci_data, (is_object($CI->params) ? (array) $CI->params : $CI->params));
        }
        return $this;
    }
    
    public function getParams() {
        return $this->setParams()->ci_data;
    }

    public function render_layout_view() {
        $CI = & get_instance();
        $this->setParams();
        /* view/template management ------------------------------------> */
        $this->ci_data['ci_content'] = $this->getTemplateData();
        /*         * *******************layout management *************************> */
        $output = $this->getLayoutData();
        //set final output of this invironment	
        $output .= $CI->output->get_output();
        $CI->output->set_output($output);
    }

}