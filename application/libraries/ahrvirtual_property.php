<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * virttual property
 * get field value proper way
 * 
 */
class ahrvirtual_property{
   private $__init = false;

   
	public function __construct($params=''){
      if($this->__init===false)
         $this->initialize($params);
	}
	
	public function initialize($params=''){
      $this->set($params); 
	}
	
	public function get($name,$print=false){
		if(!property_exists($this,$name))
        return false;
      if($print)
         echo $this->{$name}; 
      else
         return $this->{$name}; 

	}
   
   public function set($name,$value=''){
      if(empty($name))
         return false;				
      else if(is_array($name) || is_object($name)){
         $resetParams=$name;
         foreach($resetParams as $key=>$val)
            $this->{$key}=$val;
      }
      else
         $this->{$name}=$value;
   }

	public function delete($name,$print=false){
		if(!property_exists($this,$name))
        return false;
      unset ($this->{$name});
      return TRUE;
   }

	

    
}