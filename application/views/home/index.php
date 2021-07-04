<section>
    <div class="row ">
        <div class="col-md-12 col-sm-12">
            <div class="banner-area banner-area-custom">
                
            <nav class="navbar navbar-default home_header">
            <div class="nav-down">
                    <div class="row">                                  
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        </div>
                                     
                        <div class="col-md-12 col-sm-12">
                            <div id="bs-example-navbar-collapse-1" class="collapse navbar-collapse home_header_menu">
                                <ul class="nav navbar-nav">
                                    <li><a class="active" href="<?php echo site_url('home');?>">Home</a></li>
                                    <li><a  href="<?php echo site_url('about');?>">About Us</a></li>
                                    <?php if($this->session->userdata('user_id') && $this->session->userdata('user_type')==2): ?>
                                        <li><a  href="<?php echo site_url('home/getReport').'/'.$this->session->userdata('user_id');?>">Report</a></li>
                                    <?php else: ?>
                                        <li><a  href="<?php echo site_url('home/getReport');?>">Report</a></li>
                                    <?php endif; ?>
                                    <!--<li><a class="<?php if ($TNActive == 'TNContact'){ echo "active "; }?>" href="<?php echo site_url('contact');?>">Contact</a></li>-->
                                    <?php if($this->session->userdata('user_id') && $this->session->userdata('user_type')==2): ?>
                                            <li><a href="<?php echo site_url('login/logout'); ?>">Logout [<?php echo $this->session->userdata('user_name'); ?>]</a></li>
                                    <?php else: ?>
                                            <li><a href="<?php echo site_url('login');?>">Login</a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                                    
                    </div>
                </div>
        </nav>
                <!------------------------------------>
                <div class="color-line">
                    <h2>DECODE YOUR DESTINY</h2>
                    <p>At the moment of your birth, your expression in this lifetime was set in motion by the combination of your name and birth day.
                    Explore the qualities of your destiny and learn to master your challenges with the technology of bliss: kundalini yoga and meditation.
                    </p>
                    <?php if($this->session->userdata('user_id') && $this->session->userdata('user_type')==2): ?>
                          <a style="text-decoration: none;" href="<?php echo site_url('home/getReport').'/'.$this->session->userdata('user_id');?>">
                            <button class="btn btn-lg btn-primary btn-block" type="submit">GET STARTED HERE ➼</button>  
                          </a>
                    <?php else: ?>
                          <a style="text-decoration: none;" href="<?php echo site_url('home/getReport');?>">
                            <button class="btn btn-lg btn-primary btn-block" type="submit">GET STARTED HERE ➼</button>  
                          </a>
                     <?php endif; ?>
<!--                    <a style="text-decoration: none;" href="<?php// echo site_url('home/getReport');?>">
                        <button class="btn btn-lg btn-primary btn-block" type="submit">GET STARTED HERE ➼</button>  
                    </a>-->
                </div>
            </div>
        </div>
    </div> 
</section>
<section id="iconic-area">
    <!--<div class="container">-->
    <div class="row">
        <div class="col-md-3 col-sm-6 iconic-des">
            <span class="text-header"><b>EDUCATION</b></span>
            <img class="icon-image" src="<?php echo site_url("/assets/img/education.jpeg"); ?>" alt="" />
            <p>Modern, practical education and step-by-step training from some of the world's top numerology experts and teachers.</p>
        </div>
        <div class="col-md-3 col-sm-6 iconic-des">
            <span class="text-header"><b>CUSTOM REPORTS</b></span>
            <img class="icon-image1" src="<?php echo site_url("/assets/img/custom_report.jpg"); ?>" alt="" />
            <p>Request an in-depth report from our experts for guidance on a specific issue: career, relationships, changing habits,decreasing stress, personal transformation</p>
        </div>
        <div class="col-md-3 col-sm-6 iconic-des">
            <span class="text-header"><b>GUIDED MEDITATIONS</b></span>
            <img class="icon-image" src="<?php echo site_url("/assets/img/community.jpeg"); ?>" alt="" />
            <p>Connect with other like-minded members passionate about expanding their numerological wisdom & self-knowledge.</p>
        </div>
        <div class="col-md-3 col-sm-6 iconic-des">
            <span class="text-header"><b>BEYOND NUMBERS</b></span>
            <img class="icon-image1" src="<?php echo site_url("/assets/img/numbers.jpeg"); ?>" alt="" />
            <p>Forecasts, reports, and original content from the top experts in divination and personal growth. All on the blog, updated daily.</p>
        </div>
    </div>
    <!--</div>-->
</section>
<section id="client-area">
    <div class="row">
        <div class="col-md-12 col-sm-12 client-head">
            <h1 style="color: #333333;padding-bottom: 20px;">SOME REFERENCE IMAGE</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 col-sm-3 col-xs-12 ref_img">
            <img class="portfolio" src="<?php echo site_url("/assets/img/p1.jpg"); ?>" alt=""/>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 ref_img">
            <img class="portfolio" src="<?php echo site_url("/assets/img/p2.jpg"); ?>" alt=""/>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 ref_img">
            <img class="portfolio" src="<?php echo site_url("/assets/img/p3.jpg"); ?>" alt=""/>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 ref_img">
            <img class="portfolio" src="<?php echo site_url("/assets/img/p4.jpg"); ?>" alt=""/>
        </div>
    </div>

</section>
<!--<section>
    <div class="row">
        <div class="col-md-3 col-sm-3">

        </div>
        <div class="col-md-6 col-sm-6 newsletter" style="margin: 40px 0 40px 0;">
            <a style="text-decoration: none;" href="<?php echo site_url('home/getReport');?>">
                    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign Up for Newsletter</button>  
             </a>
        </div>
        <div class="col-md-3 col-sm-3">
            
        </div>
    </div>

</section>-->

<section>
    <div class="col-md-12 col-sm-12 client-head">
            <h1 style="color: #333333;padding-bottom: 20px;margin-top: 30px;">TESTIMONIALS</h1>
        </div>
    <div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: 809px; height: 150px; overflow: hidden;">

        <!-- Loading Screen -->
        <div u="loading" style="position: absolute; top: 0px; left: 0px;">
            <div style="filter: alpha(opacity=70); opacity:0.7; position: absolute; display: block;
                background-color: #000; top: 0px; left: 0px;width: 100%;height:100%;">
            </div>
            <div style="position: absolute; display: block; background: url(../img/loading.gif) no-repeat center center;
                top: 0px; left: 0px;width: 100%;height:100%;">
            </div>
        </div>

        <!-- Slides Container -->
        <div u="slides" style="cursor: move; position: absolute; left: 0px; top: 0px; width: 809px; height: 150px; overflow: hidden;">
            <?php foreach ($testimonial as $t): ?>
            <div>
                <div u="image" >
                    <p style="font-size: 13px;text-align: left;padding-right: 5px;"><i><?php echo $t->testimonial; ?></i></p>
                    <span style="display: block;float:left;font-size: 11px !important;color: #777;">--<?php echo $t->written_by; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            
        </div>
       
        <!-- bullet navigator container -->
<!--        <div u="navigator" class="jssorb03" style="bottom: 4px; right: 6px;">
             bullet navigator item prototype 
            <div u="prototype"><div u="numbertemplate"></div></div>
        </div>-->
        <!--#endregion Bullet Navigator Skin End -->
        
       
        <!-- Arrow Left -->
        <span u="arrowleft" class="jssora03l" style="top: 113px; left: 8px;">
        </span>
        <!-- Arrow Right -->
        <span u="arrowright" class="jssora03r" style="top: 113px; right: 8px;">
        </span>
        <!--#endregion Arrow Navigator Skin End -->
        <a style="display: none" href="http://www.jssor.com">Bootstrap Slider</a>
    </div>
</section>