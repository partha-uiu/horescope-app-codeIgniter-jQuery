<!doctype html>

<html>

    <head>

        <?php $this->load->view($this->theme->path('_head')); ?>

    </head>
<!--Video Modal -->
<div class="modal fade bs-example-modal-sm" id="video_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog" style="width: 1000px">
        <div class="modal-content signup_content">
            <div class="signup_content_inner">
            <div class="modal-header signup_head">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <div class="">
                    <div class="center-block">
                        <img class="signup_logo" src="<?php echo $this->theme->url('assets/img/.png'); ?>" style="width: 100%; max-width: 286px;">
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="how-it-page-content-video">

                        <div class="how-it-page-content-video-inner">
                            <object class="how-it-page-content-video-inner" height="500"
                                data="https://www.youtube.com/v/owiAcQKkc1U?rel=0">                              
                            </object>

                        </div>

                    </div>
            </div>
            <style>.modal.fade .modal-dialog{transform:none !important;}</style>

            <div class="signin-page-footer-bg text-center signup_footer_section">
                
            </div>  
        </div>
    </div>
</div>
</div>
<!--Video Modal -->


    <body>

        <div class="content background">

            <div class="row custom-border">            

                <div class="col-sm-10 col-centered custom-border">

                    <div class="col-sm-4 text-left paddign-top-header col-center">

                        <a href="<?php echo site_url('home'); ?>"><img alt="" src="<?php echo $this->theme->url('assets/img/.png'); ?>"></a> 

                    </div>

                    <div class="col-sm-3 text-center custom-border">                        

                        <img alt="" src="<?php echo $this->theme->url('assets/img/.png'); ?>">

                    </div>

                    <div class="col-sm-5 text-center margin-header-mid bg">

                        <div class="col-center">

                            <div class="top-log-right cent">

                                <?php if ($this->ahruser->User('id')) { ?>

                                    <a href="<?php echo site_url('users/campaign/dashboard'); ?>"><input type="button" class="signin-bg" value="<?php echo $user_details->fname; ?>"></a>

                                    <a href="<?php echo site_url('login/logout'); ?>" ><input type="button" class="signup-bg" value="Logout"></a>

                                <?php } else { ?>

                                    <a href="<?php echo site_url('login'); ?>"><input type="button" class="signin-bg" value="Sign in"></a>

                                    <a href="<?php echo site_url('signup'); ?>" ><input type="button" class="signup-bg" value="Sign up"></a>

                                <?php } ?>

                            </div>

                            <div class="top-log-left cent">

                                <a class="top-link" style="cursor: pointer;" id="video_link">Watch Our Video</a> <span>|</span>

                                <a class="top-link" href="<?php echo site_url('home/support'); ?>">Help</a>

                            </div>

                            <div class="clearfix"></div>

                        </div>

                    </div>

                    <div class="clearfix"></div>

                </div>

            </div>



            <div class="top-menu">

                <div class="col-sm-10 col-centered custom-border">

                    <div class="col-sm-8 header-link">                       
                        
                        <p><a href="<?php echo site_url('signup');?>">Start A Campaign</a> <span><img src="<?php echo $this->theme->url('assets/img/menu-v-bar.png'); ?>" alt=""></span></p>
                         
                        <p><a href="<?php echo site_url('home/how_it_works');?>">How It Works</a> <span><img src="<?php echo $this->theme->url('assets/img/menu-v-bar.png'); ?>" alt=""></span></p>

                        <p><a href="<?php echo site_url('home/faqs'); ?>">Questions</a></p>

                    </div>
                    
                    <!--mobile menu area-->
                    <div class="col-sm-6 mobile-menu-view">
                        <div class="mobile-icon">
                            <a href=""><i class="fa fa-bars"></i></a>
                        </div>
                        <div class="menu-view" style="display: none;">
                            <ul>
                                <li><a href="<?php echo site_url('signup');?>">Start A Campaign</a></li>
                                <li><a href="<?php echo site_url('home/how_it_works');?>">How It Works</a></li>
                                <li><a href="<?php echo site_url('home/faqs'); ?>">Questions</a></li>
                            </ul>
                        </div>
                    </div>
                    <!--end mobile menu area-->

                    <div class="col-sm-4 top-menu-search-area-pading"> 

                        <div class="search-top-area">
                            <form name="" role="form" action="<?php echo site_url('home/search_result'); ?>" method="post" class="search-area-top">
                                <input type="text" name="search" class="search-input" value="" placeholder="Search">
                                <button type="submit" class="search-submit" id="search_btn2"><i class="glyphicon glyphicon-search"></i></button>
                            </form>
                        </div>

                        <div class="clearfix"></div>

                    </div>

                    <div class="clearfix"></div>

                </div>

            </div>



            <div class="row custom-border" style="">

                <?php echo @$ci_content; ?>

            </div>



            <?php $this->load->view($this->theme->path('_footer')); ?>

        <script>

            $(document).ready(function () {
                
                 //    for mobile menu
                    $(".mobile-icon").click(function(e){
                        e.preventDefault();
                        $(".menu-view").slideToggle("slow");
                    }); 
                // end for mobile menu

                $('#video_link').on('click',function(){

                    $('#video_modal').modal({show:true});

                });

            });

        </script>

    </body>

</html>

