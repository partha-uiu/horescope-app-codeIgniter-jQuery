<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Router extends CI_Router {

//	var $suffix = '_controller';
    var $suffix = '';

    function __construct() {
        parent::__construct();
        $currentURL = 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $segments = array_filter(explode('/', str_replace($this->config->item('base_url'), '', $currentURL)));
        $this->check_directory($segments);
    }

    function check_directory($segments) {
        //$CI = &get_instance();
        $prefixes = $this->config->item('prefixes');
        if (!empty($segments[0]) && !empty($prefixes)) {
            $currentPrefixInherited = $segments[0];            
            $currentPrefix = isset($prefixes[$currentPrefixInherited])?$prefixes[$currentPrefixInherited]:'';
            if (!empty($currentPrefix)) {
                
                $directory = is_array($currentPrefix) ? (!empty($currentPrefix['prefix']) ? $currentPrefix['prefix'] : $segments[0]) : $currentPrefix;
                
                $this->set_directory($directory);                
                defined('CPREFIX') or define('CPREFIX', $segments[0]);
                $this->current_prefix = CPREFIX;
                $this->current_prefix_inherited = $directory;
                if (!empty($segments[1])) {
                    $this->set_class($segments[1]);
                }
                $segments[0] = $directory;
//                array_shift($segments);
            } else {
                defined('CPREFIX') or define('CPREFIX', '');
            }
        } else {
            defined('CPREFIX') or define('CPREFIX', '');
        }


        return $segments;
    }

    function set_class($class) {
        $this->class = $class . $this->suffix;
    }

    function controller_name() {
        if (strstr($this->class, $this->suffix)) {
            return str_replace($this->suffix, '', $this->class);
        } else {
            return $this->class;
        }
    }

    function _validate_request($segments) {
        //route from data base
        if (true === $this->config->item('useDBRoute')) {
            $slug = implode('/', $segments);
            require_once( BASEPATH . 'database/DB' . EXT );
            $db = & DB();
            if (!$db->table_exists('routes')) {
                pr("Databse table routes does not exist. Please upload table routes/set useDBRoute=false in config file.");
            } else {
                $RouteFromDB = $db->where('url_alias', $slug)->get('routes')->row();
                if (!empty($RouteFromDB)) {
                    $segments = explode('/', $RouteFromDB->url);
                }
            }
        }
        $segments = $this->check_directory($segments);
        return parent::_validate_request($segments);
    }

}