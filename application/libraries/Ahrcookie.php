<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * to second
 1min = 60
 1hour = 3600
 1day = 86400
 1week = 604800
 */
class Ahrcookie{
   private $isInit = false;
   public $expire = 7200;
   public $path = '/';
   public $domain='';
   public $secure=false;
   public $httponly=false;
   public $prefix='';

    public function __construct(){
      if($this->isInit===false){
         $this->isInit = true;

         $instance= &get_instance();
         $this->expire = ($expire = $instance->config->item('sess_expiration')) ? (time()+$expire) : (time()+7200);
         $this->path = ($path = $instance->config->item('cookie_path')) ? $path : '/';
         $this->domain = ($domain = $instance->config->item('cookie_domain')) ? $domain : $_SERVER["SERVER_NAME"];
         $this->secure = ($secure = $instance->config->item('cookie_secure')) ? $secure : false;
         $this->httponly = ($secure = $instance->config->item('httponly')) ? $httponly : false;
         $this->prefix = ($prefix = $instance->config->item('cookie_prefix')) ? $prefix : false;
      }
    }


	public function get($name)
	{
      //pr($_COOKIE[$this->prefix.$name]);
      //return (isset($_COOKIE[$this->prefix.$name])) ? $_COOKIE[$this->prefix.$name] : 'not set';

		return (isset($_COOKIE[$this->prefix.$name])) ? $this->resolveSerialize($_COOKIE[$this->prefix.$name]) : false;
	}

	public function resolveSerialize($data=''){
		$usData = @unserialize($data);
		if( $usData === false ) return $data;
		else return $usData;
	}

	public function set($name,$value='',$expire='',$path='',$domain='',$secure=false, $httponly=false)
	{
		if(is_array($name) || is_object($name))
			extract($name);
      if(is_array($value) || is_object($value))
         $value = serialize($value);

      if($expire==='')
         $expire = $this->expire;
      else
         $expire = time()+$this->expire;

      if($path==='')
         $path = $this->path;
      if($domain==='')
         $domain = $this->domain;
      if(!is_bool($secure))
         $secure = $this->secure;
      if(!is_bool($httponly))
         $httponly = $this->httponly;

		//$_COOKIE[$this->prefix.$name] = $value;
   	return setcookie( ($this->prefix.$name),$value,$expire,$path,$domain,$secure,$httponly);
	}


	public function delete($name){
		unset($_COOKIE[$this->prefix.$name]);
      return setcookie($this->prefix.$name, NULL, -3600);
	}

}
/*
 * uses:
$ahrcookie = new ahrcookie();
$ts = $ahrcookie->get('count');
echo $ahrcookie->get('count');
 *
$ahrcookie->set('arr', array('ra1', 'ra2'));
print_r ($ahrcookie->get('arr'));
 *
 */
