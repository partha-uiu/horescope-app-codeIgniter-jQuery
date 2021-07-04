<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2009, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

/**
 * CSV Class
 *
 * Usage:
 *
 * $params = array(
 *		'file_name' => '/FULL/PATH/FILENAME',
 *		'delimiter' => ',',
 *		'parse_header' => true,
 *		'length' => '1024',
 *	)
 *
 * $this->load->library('csv', $params);
 *
 * var_dump($this->csv->parse()); //parse_header FALSE
 *
 *   array
 *     0 => 
 *       array 					<----------------- [HEADER]
 *         0 => string 'Contact_Name' (length=12)
 *         1 => string 'Company_Name' (length=12)
 *         2 => string 'Address' (length=7)
 *         3 => string 'City' (length=4)
 *         4 => string 'State' (length=5)
 *         5 => string 'Zipcode' (length=7)
 *         6 => string 'phone' (length=5)
 *         7 => string 'fax' (length=3)
 *     1 => 
 *       array
 *         0 => string 'X1' (length=2)
 *         1 => string 'X2' (length=2)
 *         2 => string 'X3' (length=2)
 *         3 => string 'X4' (length=2)
 *         4 => string 'X5' (length=2)
 *         5 => string 'X6' (length=2)
 *         6 => string 'X7' (length=2)
 *         7 => string 'X8' (length=2)
 *     2 => 
 *       array
 *         0 => string 'Y1' (length=2)
 *         1 => string 'Y2' (length=2)
 *         2 => string 'Y3' (length=2)
 *         3 => string 'Y4' (length=2)
 *         4 => string 'Y5' (length=2)
 *         5 => string 'Y6' (length=2)
 *         6 => string 'Y7' (length=2)
 *         7 => string 'Y8' (length=2)
 *
 *
 * var_dump($this->csv->parse()); //parse_header TRUE
 *
 *   array
 *     0 => 
 *       array
 *         'Contact_Name' => string 'X1' (length=2)
 *         'Company_Name' => string 'X2' (length=2)
 *         'Address' => string 'X3' (length=2)
 *         'City' => string 'X4' (length=2)
 *         'State' => string 'X5' (length=2)
 *         'Zipcode' => string 'X6' (length=2)
 *         'phone' => string 'X7' (length=2)
 *         'fax' => string 'X8' (length=2)
 *     1 => 
 *       array
 *         'Contact_Name' => string 'Y1' (length=2)
 *         'Company_Name' => string 'Y2' (length=2)
 *         'Address' => string 'Y3' (length=2)
 *         'City' => string 'Y4' (length=2)
 *         'State' => string 'Y5' (length=2)
 *         'Zipcode' => string 'Y6' (length=2)
 *         'phone' => string 'Y7' (length=2)
 *         'fax' => string 'Y8' (length=2)
 *
 *
 *
 * Tested on PHP 5.2.4
 *
 *
 *
 * @category	Libraries
 * @author		Irimia Suleapa
 * @email		irimia.suleapa@unknownsoftware.ro
 * @date		September 2009
 * @link		http://www.unknownsoftware.ro
 */
	class Csv {
		
		private $file_name = '';
		private $parse_header = FALSE;
		private $delimiter = ',';
		private $length = 0;
		
		private $header;
		private $file_pointer;
		
		function __construct($config = array()) {
			
			if(count($config > 0)) {
				$this->initialize($config);
			}
				
			log_message('debug', "CSV Class Initialized");
		}
		
		function initialize($config = array()) {
			
			foreach($config as $key => $val) {
				
				if(isset($this->$key)) {
					$this->$key = $val;
				}
				
			}
			
			if(is_file($this->file_name))
				$this->file_pointer = fopen($this->file_name, 'r');
				
			if($this->parse_header) {
				$this->header = fgetcsv($this->file_pointer, $this->length, $this->delimiter);
			}
		}
		
		function parse() {
			
			$data = array();
			
			while(($row = fgetcsv($this->file_pointer, $this->length, $this->delimiter)) !== FALSE) {
				if($this->parse_header) {
					
					foreach ($this->header as $i => $heading_i)
						$row_new[$heading_i] = $row[$i];
					
					$data[] = $row_new;
				} else
					$data[] = $row;
			}
			
			return $data;
		}
		
		function __destruct() {
			
			if($this->file_pointer) {
				fclose($this->file_pointer);
			}
		}
	}
?>