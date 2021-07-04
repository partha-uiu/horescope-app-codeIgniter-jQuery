<!doctype html>
<html lang="en-gb" class="no-js">
    <head>
        
        <?php $this->load->view($this->theme->path('_head')); ?>

    </head>

<body id="home">
  <?php if ($TNActive != 'TNHome'): ?>
     <header>
        <!--<div class="container">-->
        <nav class="navbar navbar-default">
            <div class="nav-down">
                    <div class="row">
                        <div class="col-md-8 col-sm-6">
                            <div class="logo">
                                <a href="<?php echo base_url(); ?>"><img src="<?php echo site_url("/assets/img/logo.jpg"); ?>" alt=""/></a>
                            </div>
                        </div>
                                    
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        </div>
                                     
                        <div class="col-md-4 col-sm-6">
                            <div id="bs-example-navbar-collapse-1" class="collapse navbar-collapse updated_menu">
                                <ul class="nav navbar-nav">
                                    <li><a class="<?php if ($TNActive == 'TNHome'){ echo "active "; }?>" href="<?php echo site_url('home');?>">Home</a></li>
                                    <li><a class="<?php if ($TNActive == 'TNAbout'){ echo "active "; }?>" href="<?php echo site_url('about');?>">About Us</a></li>
                                    <?php if($this->session->userdata('user_id') && $this->session->userdata('user_type')==2): ?>
                                        <li><a class="<?php if ($TNActive == 'TNReport'){ echo "active "; }?>" href="<?php echo site_url('home/getReport').'/'.$this->session->userdata('user_id');?>">Report</a></li>
                                    <?php else: ?>
                                        <li><a class="<?php if ($TNActive == 'TNReport'){ echo "active "; }?>" href="<?php echo site_url('home/getReport');?>">Report</a></li>
                                    <?php endif; ?>
                                    <!--<li><a class="<?php if ($TNActive == 'TNContact'){ echo "active "; }?>" href="<?php echo site_url('contact');?>">Contact</a></li>-->
                                    <?php if($this->session->userdata('user_id') && $this->session->userdata('user_type')==2): ?>
                                            <li><a href="<?php echo site_url('login/logout'); ?>">Logout [<?php echo $this->session->userdata('user_name'); ?>]</a></li>
                                    <?php else: ?>
                                            <li><a class="<?php if ($TNActive == 'TNLogin'){ echo "active "; }?>" href="<?php echo site_url('login');?>">Login</a></li>
                                    <?php endif; ?>
                                    
                                </ul>
                            </div>
                        </div>
                                    
                    </div>
                </div>
        </nav>         
        <!--</div>-->
    </header>
    
    <?php endif; ?>

<!-- hero area (the grey one with a slider) -->
<?php echo @$ci_content; ?>


<?php $this->load->view($this->theme->path('_footer')); ?>
</body>

</html>

