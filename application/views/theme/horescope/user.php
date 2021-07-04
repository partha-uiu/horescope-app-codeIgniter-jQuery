<!--<!doctype html>

<html>

    <head>

        <?php// $this->load->view($this->theme->path('_head')); ?>
		
		<script>
                    $(document).ready(function() {
                        $("#scroll_top").click(function() {
                            $("html, body").animate({ scrollTop: 0 }, 3000);
                            return false;
                        });
                                   
           //    for mobile menu
                    $(".mobile-icon").click(function(e){
                        e.preventDefault();
                        $(".menu-view").slideToggle("slow");
                    }); 
           // end for mobile menu
                    });
               </script>

    </head>
Video Modal 
<div class="modal fade bs-example-modal-sm" id="video_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog" style="width: 1000px">
        <div class="modal-content signup_content">
            <div class="signup_content_inner">
            <div class="modal-header signup_head">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <div class="">
                    <div class="center-block">
                        <img class="signup_logo" src="<?php// echo $this->theme->url('assets/img/signup_logo.png'); ?>" style="width: 100%; max-width: 286px;">
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
Video Modal 
 

    <body>

        <div class="content background">

            <div class="row custom-border">            

                <div class="col-sm-10 col-centered custom-border">

                    <div class="col-sm-4 text-left paddign-top-header col-center">

                        <a href="<?php// echo site_url('home');?>"><img alt="" src="<?php //echo $this->theme->url('assets/img/.png'); ?>"></a> 

                    </div>

                    <div class="col-sm-3 text-center custom-border">                        

                        <div style=""><img alt="" src="<?php// echo $this->theme->url('assets/img/.png'); ?>"></div>

                    </div>

                    <div class="col-sm-5 text-center margin-header-mid bg">

                        <div class="col-center">

                            <div class="top-log-right cent">



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

                    <div class="col-sm-8 col-xs-12 header-link">                       

			<?php if(isset($slug_edit_campaign) && !empty($slug_edit_campaign)){
                            $slug = '/'.$slug_edit_campaign;
                        }  else {
                           $slug = ''; 
                        } 
                        
                        ?>
                        <p><a href="<?php echo site_url('users/campaign/dashboard');?>">Dashboard</a> <span><img src="<?php echo $this->theme->url('assets/img/menu-v-bar.png'); ?>" alt=""></span></p>
                        <p class="<?php if (isset($hide_edit_campaign) && ($hide_edit_campaign)){ echo 'hidden';}  ?>"><a href="<?php echo site_url('users/campaign/add'.$slug);?>">Edit Campaign</a> <span><img src="<?php echo $this->theme->url('assets/img/menu-v-bar.png'); ?>" alt=""></span></p>
                       
                        <p><a href="<?php echo site_url('users/campaign/add');?>">Edit Campaign</a> <span><img src="<?php echo $this->theme->url('assets/img/menu-v-bar.png'); ?>" alt=""></span></p>

                        <p><a href="#" id="withdraw_link">Withdraw</a> <span><img src="<?php echo $this->theme->url('assets/img/menu-v-bar.png'); ?>" alt=""></span></p>

                        <p><a href="<?php echo site_url('users/campaign/settings');?>">Settings</a></p>
			<p class="<?php if (isset($hide_new_campaign) && ($hide_new_campaign)){ echo 'hidden';} ?>"><span><img src="<?php echo $this->theme->url('assets/img/menu-v-bar.png'); ?>" alt=""></span><a href="<?php echo site_url('users/campaign/add/new'); ?>">Create New Campaign</a></p>

                    </div>
                    mobile menu area
                    <div class="col-sm-6 mobile-menu-view">
                        <div class="mobile-icon">
                            <a href=""><i class="fa fa-bars"></i></a>
                        </div>
                        <div class="menu-view" style="display: none;">
                            <ul>
                                <li><a href="<?php echo site_url('users/campaign/dashboard');?>">Dashboard</a></li>
                                <li class="<?php if (isset($hide_edit_campaign) && ($hide_edit_campaign)){ echo 'hidden';} unset($hide_edit_campaign); ?>"><a href="<?php echo site_url('users/campaign/add'.$slug);?>">Edit Campaign</a></li>
                                <li><a href="#" id="withdraw_link">Withdraw</a></li>
                                <li><a href="<?php echo site_url('users/campaign/settings');?>">Settings</a></li>
                                <li class="<?php if (isset($hide_new_campaign) && ($hide_new_campaign)){ echo 'hidden';} unset($hide_new_campaign); ?>"><a href="<?php echo site_url('users/campaign/add/new'); ?>">Create New Campaign</a></li>
                            </ul>
                        </div>
                    </div>
                end mobile menu area

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

            

</div>

    <div class="row custom-border" style="bottom: 0px !important;">

        <div class="">

            <div class="footer-ground-bottom text-center">

                <img style="cursor:pointer;" alt="" id="scroll_top" alt="" src="<?php echo $this->theme->url('assets/img/footer_bottom_arrow.png'); ?>">

            </div>

        </div>                

    </div>





withdraw Modal 

<div class="modal fade bs-example-modal-sm" id="withdraw_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">

    <div class="modal-dialog">

        <div class="modal-content signup_content">
            <div class="modal-header signup_head">

                <div class="">

                    <div class="center-block">

                        <h3 id="this_modal_title"></h3>

                    </div>

                </div>

            </div>

            <div class="modal-body">
                <div id="content_balance" style="display:none">
                    <p>**Please ensure your Stripe account is verified. Your donations will only be transferred to your bank account if your Stripe account is verified. To verify your account <a href="https://dashboard.stripe.com/account/verifications" target="_blank">click here</a>**</p>
                    <p>All of your donations are bundled up each day and automatically transferred to your account within 7 days</p>
                    <p class="this_modal_body"></p>
                    <p>To view all of your recent bank account transfers <a href="https://dashboard.stripe.com/transfers/overview" target="_blank">click here</a></p>
                    <p>To change transfer schedule <a href="https://dashboard.stripe.com/account/transfers" target="_blank">click here</a></p>
                </div>

                <div id="content_null" style="display:none">
                    <p class="this_modal_body" style="text-align:center;"></p>
                </div>

                <div class="loading-msg" style="display:none;text-align: center;">

                   <i class="fa fa-spinner fa-spin fa-4x"></i>

                   <h3>Please wait...</h3>

                </div>

                     
                
            </div>

            <div class="modal_footer text-center">
                     <p></p>
            </div>
            
            
        </div>

    </div>

</div>

withdraw Modal 



    <script>

       $(document).ready(function() {

            
            $('#video_link').on('click',function(){

                    $('#video_modal').modal({show:true});

            });

            $('#withdraw_link').on('click',function(){

                $('#withdraw_modal').modal({show:true});

                $('#this_modal_title').empty();

                $('.this_modal_body').empty();

            });



            $('#withdraw_modal').on('show.bs.modal', function (e) {

                $('.loading-msg').show();

                $.ajax({

                    type:"POST",

                    url:"<?php echo site_url(CPREFIX.'/campaign/check_stripe_withdraw');?>",

                    dataType: "json",

                    data:{req:"user"},

                    success:function(res){

                        $('.loading-msg').hide();

                        if(res.status){

                            if(res.stat){
                                    $('#content_balance').show();

                                    $('#this_modal_title').html(res.info.this_modal_title);

                                    $('.this_modal_body').html(res.info.this_modal_body);

                                }else{
                                    $('#content_null').show();

                                    $('#this_modal_title').html(res.info.this_modal_title);

                                    $('.this_modal_body').html(res.info.this_modal_body);
                                }
                        }else{

                            $('.this_modal_body').html(res.msg);

                        }

                    }

                });

            });

                //Wepay Donation

       });

    </script>

</body>

</html>
-->
