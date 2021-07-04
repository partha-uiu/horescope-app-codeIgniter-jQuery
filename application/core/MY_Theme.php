<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');



//Theme core class
if (!class_exists('DynamicTheme')) {

    class DynamicTheme {

        private $isInit = false;
        public $layout = 'index';
        public $themeName = '';
        public $basePath;
        public $baseURL;
        public $siteBasePath;
        public $siteBaseURL;
        public $cssPath = '';
        public $cssURL = '';
        public $jsPath = '';
        public $jsURL = '';
        public $imagesPATH;
        public $imagesURL;
        public $pluginsPath;
        public $pluginsURL = '';
        public $uploadsURL = '';
        public $defaultProfileImage = '';
        public $siteDIR = 'site';
        public $themeDIR = 'theme';
        public $loadViewPath = '';

        public function __construct($name = '') {
            if (empty($name)) {
                return false;
            }
            $this->themeName = $name;
            if ($this->isInit === false) {
                $root_path = str_replace('\\', '/', dirname(realpath(APPPATH)));
                $app_path = str_replace('\\', '/', realpath(APPPATH));
                $suff_path = 'views/' . $this->themeDIR . '/' . $this->themeName;

                //settings for all users
                $CI = &get_instance();
                $this->siteDIR = $this->siteDIR . $CI->config->item('site_id');
                $this->siteBaseURL = rtrim(base_url(), '/');
                $this->siteBasePath = rtrim(str_replace("\\", "/", FCPATH), '/');
                $this->basePath = $lp = $app_path . '/' . $suff_path;

                $this->baseURL = trim(base_url(), '/') . '/' . trim(str_replace($this->siteBasePath, '', APPPATH), '/') . '/' . $suff_path;
                $this->cssPath = $this->basePath . '/css';
                $this->cssURL = $this->baseURL . '/css';
                $this->jsPath = $this->basePath . '/js';
                $this->jsURL = $this->baseURL . '/js';
                $this->imagesPath = $this->basePath . '/images';
                $this->imagesURL = $this->baseURL . '/images';
                $this->pluginsPath = $this->siteBasePath . "/" . APPPATH . 'media/plugin';
                $this->pluginsURL = $this->siteBaseURL . '/' . APPPATH . 'media/plugin';

                $this->assetPath = $this->siteBasePath . '/assets';
                $this->assetsUrl = $this->siteBaseURL . '/assets';
                $this->defaultProfileImage = $this->siteBaseURL . '/assets/files/default.jpg';
                $this->loadViewPath = $this->themeDIR . '/' . $this->themeName;
            }
            return $this;
        }

    }

}

//theme class.
class MY_Theme {

    private $isInit = false;
    private $registaredThemes;
    public $current_theme = '';
    public $current_prefix = '';

    public function __construct() {
        if ($this->isInit === false) {
            $CI = &get_instance();
            $this->registaredThemes = new StdClass();
            $this->detectPrefix();
            $this->registerThemes()->getCurrentTheme();
            $this->isInit = true;
        }
    }

    function detectPrefix() {
        $CI = &get_instance();
        $currentURL = 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $segments = array_filter(explode('/', str_replace($CI->config->item('base_url'), '', $currentURL)));

        $prefixes = $CI->config->item('prefixes');
        if (!empty($segments[0]) && !empty($prefixes)) {
            $currentPrefixInherited = $segments[0];
            $currentPrefix = @$prefixes[$currentPrefixInherited];
            if (!empty($currentPrefix)) {
                $this->current_prefix = $directory = is_array($currentPrefix) ? (!empty($currentPrefix['prefix']) ? $currentPrefix['prefix'] : $segments[0]) : $currentPrefix;
            }
        }
        return $this;
    }

    public function set($name, $val = '') {
        $resetParams = array();
        if (empty($name))
            return false;
        else if (is_string($name))
            $this->registaredThemes->{$this->current_theme}->{$name} = $val;
        else {
            foreach ($name as $key => $val)
                $this->registaredThemes->{$this->current_theme}->{$key} = $val;
        }
    }

    public function get($name, $print = false) {
        if (!isset($this->registaredThemes->{$this->current_theme}->{$name}))
            return false;
        $req = $this->registaredThemes->{$this->current_theme}->{$name};
        if (!$print)
            return $req;
        echo $req;
    }

    private function filterHMVCDirectory() {
        $CI = &get_instance();
        $return = $directory = $CI->router->directory;
        if ((!class_exists('Modules', false) && !class_exists('Modules', false)) || empty(Modules::$locations) || empty($directory))
            return $return;

        foreach (Modules::$locations as $key => $val) {
            $mkstring = substr($directory, 0, strlen($val));
            if ($mkstring == $val) {
                $return = substr($directory, strlen($val), strlen($directory));
                ;
                break;
            }
        }
        $return = str_replace('/controllers/', '', $return);
        return $return;
    }

    public function getCurrentTheme() {
        $CI = &get_instance();
        //if theme not is set in controller then auto detect
        $dt = $CI->config->item('defaultTheme');
        $theme = $dt ? $dt : 'default';

        if (empty($CI->current_theme)) {
            $prefixes = $CI->config->item('prefixes');
            $currentPrefix = isset($prefixes[CPREFIX]) ? $prefixes[CPREFIX] : '';
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

    public function setTheme($themeName = "") {
        //detected application wise theme. Ex: default, admin
        if (!isset($this->registaredThemes->{$themeName})) {
            $app = explode('/', $this->filterHMVCDirectory());
            if (isset($this->registaredThemes->{$app[0]}))
                $themeName = $app[0];
        }
        $dt = $CI->config->item('defaultTheme');
        $this->current_theme = (empty($themeName)) ? (!empty($dt) ? $dt : 'default') : $themeName;
        return $this;
    }

    public function register($themeName = '', $className = '') {
        //if class does not exists
        if (empty($themeName)) {
            return false;
        } else if (!empty($themeName) && !class_exists($className)) {
            $this->registaredThemes->{$themeName} = new DynamicTheme($themeName);
            return true;
        } else {
            $this->registaredThemes->{$themeName} = new $className;
            return true;
        }
    }

    /*
      -detect number of application enabled. Then load application Theme
      -Ex: site for defaultTheme, admin for adminTheme, dev for devTheme (every applications is dynamic class)
      -Note: Application name and Theme name is same by default. Ex: admin Theme name is admin/index.php in Theme directory.
     */

    private function registerThemes() {
        $CI = &get_instance();
        $dt = $CI->config->item('defaultTheme');
        $defaultTheme = !empty($dt) ? $dt : 'default';
        $this->register($defaultTheme, $defaultTheme . 'Theme');
        if (!$this->current_theme) {
            $defaultTheme = $this->current_theme;
        }
        $prefixes = $CI->config->item('prefixes');
        foreach ($prefixes as $prefix_key => $currentPrefix) {
            $theme = is_array($currentPrefix) ? (!empty($currentPrefix['theme']) ? $currentPrefix['theme'] : (!empty($currentPrefix['prefix']) ? $currentPrefix['prefix'] : $prefix_key)) : $prefix_key;
            $this->register($theme, $theme . 'Theme');
        }
        return $this;
    }

    function url($url = '') {
        return $this->get('baseURL') . '/' . trim($url, '/');
    }

    function resolve_url($url = '') {
        return str_replace($this->get('basePath'), $this->get('baseURL'), $url);
    }

    function path($url = '') {
        return $this->get('basePath') . '/' . trim($url, '/');
    }

    function css($path = '', $attr = array()) {
        $attr = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css'), $attr);
        $attr = is_array($path) ? array_merge($attr, $path) : $attr;
        $attr['href'] = !isset($attr['href']) ? $path : $attr['href'];

        $parse = parse_url($attr['href']);
        if (empty($parse['host'])) {
            $fixSource = trim($attr['href'], '/');
            $fileLocations = array(
                $this->get('cssPath') . '/' . $fixSource,
                $this->get('basePath') . '/' . $fixSource,
                $this->get('siteBasePath') . '/' . $fixSource
            );

            foreach ($fileLocations as $fileLocation) {
                if (file_exists($fileLocation)) {
                    $attr['href'] = $this->resolve_url($fileLocation);
                }
            }
        }
        
        $html = array();
        $html[] = '<link';
        foreach ($attr as $attribute => $value) {
            $html[] = "$attribute=\"$value\"";
        }
        $html[] = '/>';

        return implode(' ', $html);
    }

    function js($path = '', $attr = array()) {
        $attr = array_merge(array('type' => 'text/javascript'), $attr);
        $attr = is_array($path) ? array_merge($attr, $path) : $attr;
        $attr['src'] = (!isset($attr['src'])) ? $path : $attr['src'];

        $parse = parse_url($attr['src']);

        if (empty($parse['host'])) {
            $fixSource = trim($attr['src'], '/');
            $fileLocations = array(
                $this->get('jsPath') . '/' . $fixSource,
                $this->get('basePath') . '/' . $fixSource,
                $this->get('siteBasePath') . '/' . $fixSource
            );

            foreach ($fileLocations as $fileLocation) {
                if (file_exists($fileLocation)) {
                    $attr['src'] = $this->resolve_url($fileLocation);
                }
            }
        }


        $html = array();
        $html[] = '<script';
        foreach ($attr as $attribute => $value) {
            $html[] = "$attribute=\"$value\"";
        }
        $html[] = '></script>';

        return implode(' ', $html);
    }

}

//$Theme = new theme();
//$Theme->register('default','defaultTheme');
////print_r($Theme->registaredThemes);
//$Theme->get('Theme',1);
//$Theme->set('Theme_1','ssssssssssss');
//$Theme->get('Theme_1',1);