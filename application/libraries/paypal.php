<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 *
 *Doc:
   HTML Variables for Recurring Payments Buttons
 * 
   https://cms.paypal.com/en/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables#id08A6HI00JQU
 *
 *
 *rm:
   Return method. The FORM METHOD used to send data to the URL specified by the return variable.
   Allowable values are:
   0 – all shopping cart payments use the GET method
   1 – the buyer’s browser is redirected to the return URL by using the GET method, but no payment variables are included
   2 – the buyer’s browser is redirected to the return URL by using the POST method, and all payment variables are included
   The default is 0.
   NOTE:The rm variable takes effect only if the return variable is set.
 *
 *
 * src:
   Recurring payments. Subscription payments recur unless subscribers cancel their subscriptions before the end of the current billing cycle or you limit the number of times that payments recur with the value that you specify for srt.
   Allowable values are:
    0 – subscription payments do not recur
    1 – subscription payments recur
   The default is 0.
 *
 *
 * t3:
    Regular subscription units of duration.
    Allowable values are:
       D – for days; allowable range for p3 is 1 to 90
       W – for weeks; allowable range for p3 is 1 to 52
       M – for months; allowable range for p3 is 1 to 24
       Y – for years; allowable range for p3 is 1 to 5
 *
 * p3:
   Subscription duration. Specify an integer value in the allowable range for the units of duration that you specify with t3.
 *
 *
 * sra:
   Reattempt on failure. If a recurring payment fails, PayPal attempts to collect the payment two more times before canceling the subscription.
   Allowable values are:
   0 – do not reattempt failed recurring payments
   1 – reattempt failed recurring payments before canceling
   The default is 1.
   For more information, see Reattempting Failed Recurring Payments with Subscribe Buttons.
 *
 * lc:
   valid paypal language
 *
 */



class Paypal{
   private $isInt = false;
   private $inputField = array();   
   private $getRecurringDefaultSettings = null;
   public $formSubmitManually = false;

   /*paypal sand box configure*/
   public $sandbox = false;
   public $sandboxBusinessAccount = "ranaar_1314430858_biz@gmail.com";
   public $sandboxURL = "https://www.sandbox.paypal.com/cgi-bin/webscr";
   

   /*paypal settings*/
   public $paypalBusinessAccount = "ranaar_1314430858_biz@gmail.com";
   public $paypalURL = "https://www.paypal.com/cgi-bin/webscr";


   public function __construct()
   {
		//$this->instance= &get_instance();
      if($this->isInt===false){
         $this->isInt = true;
         $this->getRecurringDefaultSettings();
      }
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

   /*
    * filter settings remove "false" and "empty string" value
    */
    public function filterSettings($settings=array()){
       if(empty ($settings))
          return $settings;
       
       foreach ($settings as $key => $val){
          if($val==='' || $val === false)
             unset ($settings[$key]);
       }
       return $settings;
    }

   /*
    * load directly/inline settings value. ex "$paypal=new Paypal(); $paypal->sandbox=true"
    */
    public function getInlineSettings($accept=array()){
       $data = array();
       if(empty ($accept))
         return $data;

       foreach ($accept as $property){
          if(property_exists($this, $property))
             $data[$property] = $this->{$property};
       }
       
       return $data;
    }  

    public function buildForm($fields=array())
    {
       if(empty ($fields)) return;
       ob_start();
       ?>
        <html>
         <head><title>Processing Payment...</title></head>
         <body onLoad="<?php if($this->formSubmitManually==false){  ?>document.forms['paypal_form'].submit();<?php } ?>">
            <form method="post" name="paypal_form" action="<?php echo ($this->sandbox===true)? $this->sandboxURL : $this->paypalURL ?>">
            <?php foreach ($fields as $name => $value){ echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n"; } ?>
            </form>
         </body>
      </html>
      <?php       
       $html = ob_get_contents();
       ob_end_clean();
       return $html;       
    }
    

     /*
      * recuring payment default config
      * Note: if you include others parameter in recuring payment, so include "key" with "value" in
      * "getRecurringDefaultSettings" method.
      */
	 public function getRecurringDefaultSettings()
    {
      if(empty ($this->getRecurringDefaultSettings)){
         $this->getRecurringDefaultSettings = array(
            "cmd" => "_xclick-subscriptions",
            "business" => ($this->sandbox===true)? $this->sandboxBusinessAccount : $this->paypalBusinessAccount,
            "currency_code" => "USD",
            'lc'=>'US',
            'rm' => 2,
            'src' => 1,
            'sra' => 1,
            'return' => site_url('paypal/success'),
            'cancel_return' => site_url('paypal/cancel'),
            'notify_url' => site_url('paypal/notify'), //call internaly by paypal
            'item_name' => (strtolower($_SERVER['SERVER_NAME']) ." new payment ". date('m-d-Y h:i:s') ),
            'a3' => 0, //amount of payment
            't3' => 'M', //monthly payment
            'p3' => '1', //payment in instalment (such as monthly 2 times, or yearly 5 times)
            'custom' => '' //$id.'~'.$type.'~'.$renew //set your custom field
         );
      }
      return $this->getRecurringDefaultSettings;
    }


    public function recurringPayments($opt = array())
    {
       //if manually set the class property and that  is "acceptable" for payment.
       //The include fields are include first then include directly passing value.       
       $this->getRecurringDefaultSettings = array_merge($this->getRecurringDefaultSettings, $this->getInlineSettings(array_keys($this->getRecurringDefaultSettings)));

       //extends directly pass settings
       if(!empty ($opt))
          $this->getRecurringDefaultSettings = array_merge($this->getRecurringDefaultSettings, $opt);
       
       $this->getRecurringDefaultSettings = $this->filterSettings($this->getRecurringDefaultSettings);

       echo $this->buildForm($this->getRecurringDefaultSettings);

    }





    /*
     * how to call paypal payment see the example of process.
     * Note: this function not need in for library internal/external process. just emaple.
     * */
    public function testRecuringPayment()
    {
      $this->load->library('paypal');
      $paypal = new Paypal();
      $paypal->sandbox=true;
      $paypal->return = site_url('signup/payment/success');
      $paypal->cancel_return = site_url('signup/payment/cancle');
      $paypal->notify_url = 'http://recolitus.com/cse/paypal-notify.php'; //site_url('signup/payment/notify')
      //$paypal->business = ''; //if need busniess account needed
      $paypal->item_name = $_SERVER["SERVER_NAME"].' registering new seo package.';
      $paypal->a3 = 10; //amount
      $paypal->t3 = 'M'; //amount
      $paypal->custom = "1_2_{$paypal->a3}_$paypal->t3"; //custom variable
      //$paypal->formSubmitManually = true;
      pr( $paypal->recurringPayments() );

    }


    
    /*
     * after success paypal payment see the example of process.
     * Note: this function not need in for library internal/external process. just emaple.
     * */
    public function testSucccess()
    {
      $body='';
      $response = $_REQUEST;
      foreach ($response as $key => $value) {
         $body.="$key: $value<br/>";
      }

      if( isset($_POST['custom']) && isset($_POST['mc_gross']))
      //if( isset($_POST['custom']) && isset($_POST['mc_gross']) && $_POST['payment_status']=='Completed')
      {
         list($userID,$packageID,$price,$paymentDuration)=explode('_',$_POST['custom']);
         $amount		=	$_POST['mc_gross'];
         $credits = new stdClass();
         list($credits->user_id,$credits->package_id,$credits->price,$credits->paymentDuration)=explode('_', $_POST['custom']);
         $credits->amount = $_POST['mc_gross'];
         $time = time();
         $credits->date           =		date("Y-m-d");
         if(strtolower($paymentDuration)=='m')
            $credits['next_date']	=		date("Y-m-d",  strtotime('+1 month',$time));
         else if(strtolower($paymentDuration)=='y')
            $credits['next_date']	=		date("Y-m-d",  strtotime('+1 year', $time));

         $credits->response = $response; //now process the result your self.
         mail($to='ranaars@gmail.com', $subject='paypal notify email', $message=$body);
      }
    }
    
}