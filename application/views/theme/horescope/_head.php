<meta charset="utf-8">
<title><?php echo $title_for_layout; ?> | Technology of Bliss</title>
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="viewport" content="width=device-width, initial-scale=1.0">


<meta property="fb:app_id"          content="<?php echo @$this->config->item("facebook_id"); ?>" /> 
<meta property="og:type"            content="social-cookbook:recipe" /> 
<meta property="og:url"             content="<?php echo site_url('campaign/' . @$details->project_slug); ?>" /> 
<meta property="og:title"           content="<?php echo @$details->title; ?>" /> 
<meta property="og:image"           content="<?php echo (@$details->media_url != '') ? @$details->media_url : site_url('assets/img/noImageAvailable.jpg'); ?>" /> 

<?php     
   // echo $this->theme->css('assets/css/reset.css');
    echo $this->theme->css('assets/css/bootstrap.min.css');
    echo $this->theme->css('assets/css/style.css');
    echo $this->theme->css('assets/css/responsive.css');
    echo $this->theme->css('assets/css/my_slide.css');
    //echo $this->theme->css('assets/css/custom.css');
   // echo $this->theme->css('assets/css/normalize.css');
   
?>
    
    <?php
	//css
   
//    echo $this->theme->css('assets/js/select2/select2.css');
//    echo $this->theme->css('assets/js/select2/select2-bootstrap.css');
    
    
    echo $this->theme->css('assets/css/print.css');
    echo $this->theme->css('assets/font-awesome/css/font-awesome.min.css');
    
?>
<!--<link rel="shortcut icon" href="<?php // echo $this->theme->url('/assets/img/favicon.ico');?>"  type="image/x-icon">-->
<link rel="apple-touch-icon" href="img/touch-icon.html" />
<link rel="image_src" href="img/touch-icon.html" />

<!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>-->
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script src="http://maps.googleapis.com/maps/api/js" type="text/javascript"></script>

<?php //echo $this->theme->js('assets/js/main.js');?>
<?php echo $this->theme->js('assets/js/countries.js');?>
<?php echo $this->theme->js('assets/js/jssor.js');?>
<?php echo $this->theme->js('assets/js/jssor.slider.js');?>
<?php echo $this->theme->js('assets/js/my_slider.js');?>



<?php 
//js
//    echo $this->theme->js('assets/js/jquery.js');
//    echo $this->theme->js('assets/js/jquery.cookie.js');
    echo $this->theme->js('assets/js/bootstrap.js');
    
    echo $this->theme->js('assets/js/jquery.validate.js');
    echo $this->theme->js('assets/js/jquery.form.min.js');
    echo $this->theme->js('assets/jquery-cookie-master/src/jquery.cookie.js');
//    echo $this->theme->js('assets/js/jquery.cookie.js');
    
    
    
?>
    
    <!-- Google fonts - witch you want to use - (rest you can just remove) -->
    <link href='http://fonts.googleapis.com/css?family=Fjalla+One|Archivo+Narrow|Oswald:400,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=DroidSans">
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Arvo">
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Arimo">
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Vollkorn">
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=SansitaOne">
    
    <!-- style switcher -->
    <?php echo $this->theme->js('assets/js/style-switcher/color-switcher.css');?>
        
    <!-- ######### JS FILES ######### -->
        
    <!-- style switcher -->

    
    <!-- main menu -->  
     <?php echo $this->theme->js('assets/js/mainmenu/ddsmoothmenu.js');?>

     <?php echo $this->theme->js('assets/js/mainmenu/selectnav.js');?>


