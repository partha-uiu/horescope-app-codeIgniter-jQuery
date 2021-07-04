<?php
function campaign_category($limit) {
    $CI = &get_instance();
    $CI->load->model('category_model', 'Category');
    
    $categories = $CI->Category->result(array('conditions' => array('is_active' => 1), 'order' => array('sort_order' => "ASC"),'limit'=>$limit));
//    pr($categories);
//    exit();
    return $categories;
}

function proid_to_proname($p_id)
	{
		$CI =& get_instance();
                $CI->load->model('products_model', 'Product');
                $name = $CI->Product->getproname($pro_id);
		if ($name != false) {
                    return $name[0]['p_title'];
		}
		return false;
	}
        
function get_quotes(){
    $CI = &get_instance();
    $CI->load->model('quote_model', 'Quote');
    $quotes = $CI->db->query("SELECT * FROM hr_quote ORDER BY RAND() LIMIT 3")->result();
    return $quotes;
}
?>
