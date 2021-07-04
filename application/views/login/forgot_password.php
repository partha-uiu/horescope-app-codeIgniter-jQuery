<div class="content campaign-page-bg-banner-section">
    <div class="col-sm-10 campaign-page-banner-section">
        <p class="campaign-page-banner-section-line-1">Forgot Password</p>
        <p class="campaign-page-banner-section-line-2">  
            <span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque quis blandit ipsum.</span>
        </p>
    </div>
    <div class="clearfix"></div>
</div>
<div class="campaign-page-banner-bottom-section text-center"><img src="<?php echo $this->theme->url('assets/img/campaign-page-icon.png')?>" alt=""></div>    
<div class="home-bg-banner-bottom-section-line"></div>

<div class="content-campaign-section">
    <div class="col-sm-5 col-centered text-center custom-border content-signin-section-main-area">                   
        <div class="content-signin-section-inner">
                <div class="content-signin-section-row-1">
<!--                    <img src="<?php echo $this->theme->url('assets/img/.png')?>" alt="">-->
                </div>
                <!--<div class="signin-page-line-shadow"></div>-->
                <div class="content-signin-section-row-2">
                    <div class="content-signin-section-row-2-col-1">
                        <h3><b>Forgot Password</b></h3>
                        <?php if (isset($_SESSION['success'])) {
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                        } elseif(isset($_SESSION['error'])) {
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        } ?>
                        <!--<button type="button" onclick="Login();" class="fb_login"><img src="<?php echo $this->theme->url('assets/img/fb_4.png')?>" alt=""></button>-->
                    </div>                   
                    <div class="content-signin-section-row-2-col-2">
                        <p class="content-signin-section-row-2-col-2-p-1"><span>Use your email</span></p>
                        <p class="content-signin-section-row-2-col-2-p-2"></p>
                        <form name="send_mail" id="email_signin" method="post" action="<?php echo site_url('login/send_mail');?>">
                            <div class="input-field-1">
                                <input type="email" name="email" value="" required="" placeholder="Your Email Address"/>
                            </div>
<!--                            <div class="input-field-2">
                                <input type="password" name="password" value="" required="" placeholder="Your Password"/>
                            </div>-->
                            <div class="content-signin-section-row-2-col-3">
                                <button type="submit" name="btn" class="btn btn-primary btn-lg">Send Mail</button>
                            </div>
                        </form>
                    </div>
                    
                </div>
        
        </div>    
    </div>
</div>

<script>
        /* <![CDATA[ */
        (function($) {
            $(function() {
                //$('.process').addClass('hidden');
                jQuery.extend(jQuery.validator.messages, {
                     remote: jQuery.format("{0} Not Found. Please check your Email Address.")
                    });
                var email_signin = $('#email_signin');

                email_signin.validate({
                    ignore:[],
                    rules:{
                        email:{
                            required:true,
                            email: true
                        }
                    },
                    message: {
                        email: {
                                remote: jQuery.format("{0} not found..!!!")
                            } 
                    },
                    errorElement: 'p',
                    errorPlacement: function(error, element) {
                        $(element).tooltip('destroy').tooltip({
                            html: true,
                            trigger: 'manual',
                            //                    container: 'body',
                            title: error,
                            //template: '<div class="tooltip for-error"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                            template: '<div class="tooltip top for-error"><div class="tooltip-inner"></div><div class="tooltip-arrow"></div></div>'
                        }).tooltip('show');
                    },
                    unhighlight: function(element, errorClass, validClass) {
                        $(element).tooltip('destroy');
                    }
                });
            })
        })(jQuery)
        /* ]]> */

    </script>