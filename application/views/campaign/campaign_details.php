<?php// $share_id = $details->id;
//$share_title = $details->title;
//$share_description = $details->description;
//$share_url = site_url('campaign/' . $details->project_slug);
//$fb_page_id = $this->config->item('fb_page_id');
//echo $this->theme->js('assets/js/content_upload.js');
?>

<!--<div id="fb-root"></div>
<script title="initilize facebook SDK">
    window.fbAsyncInitConfig =
            {
                appId: '<?php echo $this->config->item("facebook_id"); ?>', // App ID
                status: true, // check login status
                cookie: true, // enable cookies to allow the server to access the session
                xfbml: true, // parse XFBML,

                version: 'v2.2'
            };
</script>

<script type="text/javascript" src="<?php echo site_url('/assets/js/facebook.js'); ?>"></script>
<script type="text/javascript" src="<?php echo site_url('/assets/js/campaign_details.js'); ?>"></script>-->


<!--<script title="social share related script">
    jQuery(document).ready(function ($) {
//        var event = jQuery.Event( "custom_fb_share" );
        var share_url = '<?php echo $share_url; ?>';
        //user under url to test the script
//        var share_url = 'http://www.facebook.com';
        $('body').data({'fb-total-share': 0, 'twitter-total-tweet': 0});

        //update share related script
        function updateShareCount() {
            $.get('http://api.facebook.com/restserver.php?method=links.getStats&urls=' + share_url + '&format=json', function (response) {
                var response = response[0];
                var share_count = 0;
                if (response.share_count) {
                    share_count = response.share_count;
                }
                $('body').data({'fb-total-share': share_count});
                $('body').find('[data-custom_fb="count-share"]').html(share_count);


                //twitter share will be add on share_count;
                $('body').find('[data-custom-total-share="count"]').html($('body').data('fb-total-share') + $('body').data('twitter-total-tweet'));
            }, 'json');
        }

        updateShareCount();

        $('body').delegate('[data-custom_fb="do-share"]', "click", function (event) {
            event.preventDefault();
            //share current page
            //
            //[Start] FB share script
            FB.ui({
                method: 'share',
                href: share_url,
                description: $('.share_details').html()
            }, updateShareCount);
            //[End]  FB share script
//            FB.ui({
//                method: 'share',
//                href: share_url,
//            }, function (response) {
//                console.log('response');
//                //update share count
//                updateShareCount();
//            });

        });



//count tweet
        function updateTweetCount() {
            var url = '<?php echo $share_url; ?>';
            $.getJSON('http://urls.api.twitter.com/1/urls/count.json?url=' + url + '&callback=?', function (data) {
                tweets = data.count;
                $('body').data({'twitter-total-tweet': tweets});
                $('body').find('[data-custom_twitter="count-tweet"]').html(tweets);
                $('body').find('[data-custom-total-share="count"]').html($('body').data('fb-total-share') + $('body').data('twitter-total-tweet'));
            });
        }

        updateTweetCount();

        var tweetWindow = function () {
            var url = '<?php echo $share_url; ?>';
            var text = $(document).find('meta[name="og:title"]').attr('content');
            if (!$.trim(text)) {
                text = $(document).find('meta[name="title"]').attr('content');
            }
            text = '<?php echo $share_title; ?>';

//        var myWindow = window.open("http://twitter.com/share?url=" +
            var myWindow = window.open("https://twitter.com/intent/tweet?" +
                    "url=" + encodeURIComponent(url) +
                    "&text=" + encodeURIComponent(text) +
                    "&via=fundmypet",
                    "tweet", "height=300,width=550,resizable=1");
        };

        $('body').delegate('[data-custom_twitter="do-tweet"]', "click", function (event) {
            event.preventDefault();
            tweetWindow();
            setTimeout(function () {
                updateTweetCount();
            }, 3000);
        });
    });
</script>



<div class="content campaign-page-bg-banner-section">
    <div class="col-sm-10 campaign-page-banner-section">
        <p class="campaign-page-banner-section-line-1"><?php echo $details->title; ?></p>
        <p class="campaign-page-banner-section-line-2">
            <span>Created <?php echo date("F j, Y", strtotime($details->created)); ?> &nbsp;|&nbsp;  <?php echo $details->fname . ' ' . $details->lname; ?></span><img src="<?php echo $this->theme->url('assets/img/invelop-icon.png'); ?>">
        </p>
    </div>
    <div class="clearfix"></div>
</div>
<div class="campaign-page-banner-bottom-section text-center"><img src="<?php echo $this->theme->url('assets/img/campaign-page-icon.png'); ?>" alt=""></div>
<div class="home-bg-banner-bottom-section-line"></div>

<div class="content-campaign-section">
    <div class="col-sm-10 col-centered text-center custom-border content-campaign-section-main-area">
        <div class="content-campaign-section-inner">
            <div class="col-sm-8 custom-border">
                <div class="row content-campaign-section-left-top">
                     <img class="img-thumbnail" src="<?php echo ($details->media_url != '')? (($details->media_thumb_url != '') ? $details->media_thumb_url : $details->media_url) : site_url('assets/img/noImageAvailable.jpg'); ?>"> 
                    <?php if($details->video_url != ''){?>
                    <div class="expand-video">
                        <a style="display:block;" title="Play" class="<?php echo $details->video_type; ?>" href="<?php echo $details->video_url; ?>"><i class="play_button fa fa-play-circle-o"></i>
                    <?php } ?>
                    <img class="img-thumbnail" src="<?php echo ($details->video_url == '') ? (($details->media_url != '')? (($details->media_thumb_url != '') ? $details->media_thumb_url : $details->media_url) : site_url('assets/img/noImageAvailable.jpg')) : $details->media_url ;?>">
                    <?php if($details->video_url != ''){?>
                        </a>
                    </div>
                    <?php } ?>
                    <div class="content-campaign-section-left-top-img-caption">
                        <div class="content-campaign-section-left-top-img-caption-top">
                            <span data-custom_fb="count-total-share" class="content-campaign-section-left-top-img-caption-top-span-1">0</span>
                            <span class="content-campaign-section-left-top-img-caption-top-span-2">TOTAL SHARES</span>
                        </div>
                        <div class="content-campaign-section-left-top-img-caption-center">
                            <button data-custom_fb="do-share"  class="campaign-page-fb-share-btn campaign-page-fb-share-btn-res"></button>
                            <span data-custom_fb="count-share">0</span>
                        </div>
                        <div class="content-campaign-section-left-top-img-caption-bottom">
                            <button data-custom_twitter="do-tweet" class="campaign-page-fb-tweet-btn campaign-page-fb-tweet-btn-res"></button>
                            <span>0</span>
                        </div>
                    </div>
                </div>
                <div class="row content-campaign-section-left-content-text">
                    <?php echo $details->description; ?>
                </div>

                <?php if ($updates) { ?>
                    <div class="container-fluid">
                        <div class="updates">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Updated #<?php echo $updates_count; ?>
                                </div>
                                <div class="panel-body">
                                    <?php echo $updates->comment; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php } ?>

                <div class="row content-campaign-section-left-content-text rewrite-left-content-text-top">
                    <div class="content-campaign-section-left-bottom-wrapper">
                        <div class="content-campaign-section-left-bottom-row-1">
                            <div class="col-sm-3 well content-campaign-section-left-bottom-row-1-col-1">
                                <span data-custom_fb="count-total-share" class="row-1-col-1-span-1">0</span>
                                <span class="row-1-col-1-span-2">TOTAL SHARES</span>
                            </div>
                            <div class="col-sm-3 well content-campaign-section-left-bottom-row-1-col-2">
                                <button data-custom_fb="do-share" class="campaign-page-fb-share-btn"></button>
                            </div>
                            <div class="col-sm-3 well well content-campaign-section-left-bottom-row-1-col-3">
                                <button data-custom_twitter="do-tweet" class="campaign-page-fb-tweet-btn"></button>
                            </div>
                            <div class="col-sm-3 well well well content-campaign-section-left-bottom-row-1-col-4">
                                <button class="campaign-page-fb-donate-btn"></button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="row content-campaign-section-left-bottom-row-2">
                            <p><span>COPY, PASTE & SHARE:</span> <a href="#"><?php echo site_url(); ?><?php echo $details->project_slug; ?></a></p>
                        </div>
                    </div>
                </div>

                <div class="" style="width: auto; height: 92px; background: none; margin: 20px;">
                    <a href="<?php echo site_url(); ?>">
                        <div style="float: left; width: 280px; padding: 10px; border: 1px #d9d9d9 solid; border-radius: 5px;">
                            <img style="width: 100%;" src="<?php echo $this->theme->url('assets/img/fundmypet_logo.png'); ?>" alt="">
                        </div>
                    </a>
                    <a href="#contactModal" data-toggle="modal" data-target="#contactModal">
                        <div style="float: right; width: 90px; height: 100%; padding: 20px 10px; border: 1px #d9d9d9 solid; border-radius: 5px; margin-left: 10px;">
                            <i class="glyphicon glyphicon-envelope" style="font-size: 30px; padding-bottom: 10px;"></i>
                            <p>CONTACT</p>
                        </div>
                    </a>
                    <a href="#myModalPoster" data-toggle="modal" data-target="#myModalPoster">
                        <div style="float: right; width: 90px; height: 100%; padding: 20px 10px; border: 1px #d9d9d9 solid; border-radius: 5px; margin-left: 10px;">
                            <i class="glyphicon glyphicon-print" style="font-size: 30px; padding-bottom: 10px;"></i>
                            <p>POSTER</p>
                        </div>
                    </a>
                </div>

                <div id="mainCommentBox"class="row content-campaign-section-left-content-text rewrite-left-content-text-bottom">
                    <style type="text/css">
                        #mainCommentBox{
                            position: relative;
                            z-index: 1;
                        }
                        #mainCommentBox #leaveCommentBox{
                            position: absolute; left: 0px; right: 0px; z-index: 10; background: none repeat scroll 0% 0% #FFF;
                        }
                         #mainCommentBox #commentInputBox {
                            margin-top: 157px;
                        }
                        body.fb-loggedin #mainCommentBox #leaveCommentBox{
                            display: none;
                        }
                        body.fb-loggedin #mainCommentBox #commentInputBox{
                             margin-top: 0;
                        }
                    </style>
                    <div class="rewrite-left-content-text-bottom-row-1"><span>
                            <fb:comments-count href="<?php echo $share_url; ?>"/></fb:comments-count>
                            COMMENTS</span></div>

                    <div id="leaveCommentBox" class="rewrite-left-content-text-bottom-row-2">
                        <p class="rewrite-left-content-text-bottom-row-2-p-1">Please use Facebook to leave a comment below:</p>
                        <p>
                            <a class="doFbComment" href="#">
                                <img src="<?php echo $this->theme->url('assets/img/campaign-page-fb-continue-btn.png'); ?>" alt="">
                                <button class="campaign-page-fb-continue-btn"></button>
                            </a>
                        </p>
                        <p class="rewrite-left-content-text-bottom-row-2-p-3"><span class="span-1">Nothing</span> <span class="span-2">gets posted to your wall. Only your Facebook name & photo are used.</span></p>
                    </div>

                    <div id="commentInputBox" style="position: relative; z-index: 1;">
                        <fb:comments href="<?php echo $share_url; ?>" numposts="5" data-mobile="TRUE" data-width="100%"></fb:comments>
                    </div>


                </div>
            </div>

            <div class="col-sm-4 custom-border campaign-page-right-block-wrapper">
                <div class="row campaign-page-right-block-area">
                    <div class="campaign-page-right-block-area-title-bar">
                        <p class="campaign-page-right-block-area-title-bar-p-1">
                            <span><img src="<?php echo $this->theme->url('assets/img/campaign-title-pin.png'); ?>" alt=""></span>
                            <span class="span-1"><?php echo $details->primary_city . ' , ' . $details->state ?></span>
                        </p>
                        <p class="campaign-page-right-block-area-title-bar-p-2">
                            <span><img src="<?php echo $this->theme->url('assets/img/campaign-title-location.png'); ?>" alt=""></span>
                            <span class="span-2"><?php echo $category->name; ?></span>
                        </p>
                        <div class="clearfix"></div>
                    </div>
                    <p class="campaign-page-right-block-line-1"><span class="span-left">$<?php echo $details->collected_amount; ?></span> <span class="span-right">of $<?php echo $details->needed_amount; ?></span></p>
                    <?php
                    $values = $details->collected_amount / $details->needed_amount;
                    $percent = number_format($values * 100, 2);
                    $in_percent = number_format($values * 100, 2) . '%';
                    ?>
                    <div class="campaign-page-right-block-progress-bar">
                        <div class="progress progress-rewrite">
                            <div class="progress-bar progress-bar-rewrite progress-rewrite-once" role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo ($percent > 100) ? 100 : $percent; ?>%">
                                <span class="sr-only"><?php echo $in_percent; ?> Complete</span>
                            </div>
                        </div>
                    </div>
                    <?php
                    $details_date = new DateTime($details->created);
                    $now = new DateTime();
                    $interval = $details_date->diff($now);
                    $in_date = $interval->format('%a days');
                    ?>
                    <p class="campaign-page-right-block-line-2">Raised by <?php echo count($donations); ?> people in <?php echo $in_date; ?></p>
                    <div class="campaign-page-right-donate-area">
                        <a href="<?php echo site_url('donate/' . $details->project_slug); ?>"><div class="campaign-page-right-donate-btn">Donate Now</div></a>
                    </div>
                    <div class="campaign-page-right-donate-area">
                        <a data-custom_fb="do-share" href="#"><div class="campaign-page-share-fb-btn">SHARE ON FACEBOOK</div></a>
                    </div>
                </div>
                <div class="row campaign-page-right-block-area">
                    <div class="campaign-page-right-block-area-title-bar">
                        <p>
                            <span class="campaign-page-right-block-bottom-title-bar-left"><?php echo count($donations); ?> DONATIONS</span>
                            <span class="campaign-page-right-block-bottom-title-bar-right">RECENT <img src="<?php echo $this->theme->url('assets/img/recent-arrow.png'); ?>" alt=""></span>
                        </p>
                    </div>

                    <?php
					$font_size = 40; 
                    foreach ($donations as $donation) {
                        $datetime1 = new DateTime($donation->created);
                        $datetime2 = new DateTime();
                        $interval = $datetime1->diff($datetime2);
                        $donation_in_date = $interval->format('%a days');
						$amnt = $donation->amount;
						$lenth = strlen($amnt);
						if($lenth > 4){
							$font_size = 25;
						} elseif ($lenth > 3){
							$font_size = 30;
						} elseif ($lenth > 2){
							$font_size = 35;
						} else {
							$font_size;
						}
                        ?>

                        <div class="campaign-page-right-block-bottom-content">
                            <div class="campaign-page-right-block-bottom-content-left"><span style="<?php echo 'font-size:'.$font_size.'px !important;'; ?>">$<?php echo $donation->amount ?></span></div>
                            <div class="campaign-page-right-block-bottom-content-right">
                                <div class="campaign-page-right-block-bottom-content-right-text">
                                    <p class="campaign-page-right-block-bottom-content-right-text-1"><?php echo $donation->fname . ' ' . $donation->lname ?></>
                                    <p class="campaign-page-right-block-bottom-content-right-text-2"><?php echo $donation_in_date; ?> ago</p>
                                    <p class="campaign-page-right-block-bottom-content-right-text-3"><?php echo $donation->comment; ?></p>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

Start myModalPoster Modal 

<div class="modal fade bs-example-modal-sm" id="myModalPoster" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog">       
        <div class="poster_content_inner">
            <div class="modal-header signup_head">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <div class="">
                    <div class="center-block">
                        <img class="signup_logo" src="<?php echo $this->theme->url('assets/img/signup_logo.png'); ?>" style="width: 100%; max-width: 286px;">
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="free_signup_text">
                    <p class="text-center signup_banner-title-2">Print a Campaign Sign</p>
                    <p class="text-center" style="font-size: 18px; margin-top: 15px; color: #777;">A campaign sign is perfect for the office or local businesses.</p>
                </div>
                <div id="start_print_div" class="div_wrapper">
					<style>
                        @page { size: A4; margin: 0; } @media print { html, body { width: 210mm; height: 297mm; margin: 0 auto; margin-top: 40px; } /* ... the rest of the rules ... */ }
                    </style>
                    <div class="div_wrapper_inner" style="text-align: center;">
                        <img class="poster_logo" src="<?php echo $this->theme->url('assets/img/fundmypet_logo.png'); ?>">
                        <p class="poster_logo_bar"></p>

                        <p class="text-center poster_title"><?php echo $details->title; ?></p>

                        <img class="img-thumbnail poster_campaign_img" src="<?php echo ($details->media_url != '') ? $details->media_url : site_url('assets/img/noImageAvailable.jpg'); ?>">
                        <p class="text-center poster_campaign_msg">Show your support by going to this link</p>
                        <p class="text-center poster_campaign_link"><a href="#"><?php echo site_url(); ?><?php echo $details->project_slug; ?></a></p>
                    </div>
                    <div class="poster_clear"></div>
                    <div class="poster_token_div_wrapper">
                    <div class="poster_token_wrap">
                        <?php for ($i=0; $i<10; $i++){ ?>
                        <div class="poster_token_inner" style="<?php if($i==9){echo 'border:0;';} ?>">
                            <div class="poster_token_div">
                                <span class="poster_token_title">
                                    <?php echo $details->title; ?>
                                </span>
                                <span class="poster_token_link">
                                    <?php echo site_url(); ?><?php echo $details->project_slug; ?>
                                </span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    </div>            
                </div> 
            </div>
            <div class="signup-email-footer-bg text-center" style="margin: 0!important;">
                <button class="btn btn-primary btn-lg" id="print_button" onClick="printdiv('start_print_div');">Print Sign</button>               
            </div> 
        </div>
    </div>
  
</div>

<script>
    function printdiv(printdivname)
    {
    var headstr = '<html><head><title>Campaign Poster</title></head><body>';
    var footstr = "</body>";
    var newstr = document.getElementById(printdivname).innerHTML;
    var oldstr = document.body.innerHTML;
    document.body.innerHTML = newstr;
    window.print();
    document.body.innerHTML = oldstr;
	location.reload();
    return false;
    }
</script>
End myModalPoster Modal 

Start contactModal Modal 
<div class="modal fade bs-example-modal-sm" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content signup_content">
            <div class="signup_content_inner">
                <div class="modal-header signup_head">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <div class="">
                        <div class="center-block">
                            <img class="signup_logo" src="<?php echo $this->theme->url('assets/img/signup_logo.png'); ?>" style="width: 100%; max-width: 286px;">
                        </div>
                    </div>
                </div>
                <div class="modal-body modal-body-hide">
                    <div id="email_login_div">
                        <div class="clear"></div>
                        <form id="campaign_contact_form" class="" role="form" action="<?php echo site_url('campaign/email_campaign_owener'); ?>" method="post" novalidate>
                            <div style="width: 100%; padding: 0 20px;">
                                <div class="well">
                                    <div style="margin-left: 100px; text-align: left;">
                                        <p><?php echo "You are sending a message to..."; ?></p>
                                        <p><?php echo $details->fname . ' ' . $details->lname; ?></p>
                                    </div>
                                    <input type="hidden" name="email_to" value="<?php echo $details->email ?>">
                                    <input type="hidden" name="email_to_fname" value="<?php echo $details->fname ?>">
                                    <input type="hidden" name="email_to_lname" value="<?php echo $details->lname ?>">
                                    <div class="row" style="margin-top: -60px;" >
                                        <div class="col-xs-6 col-md-4">
                                            <a href="#">
                                                <img class="thumbnail" src="" alt="" style="height:90px; width: 90px; margin-bottom: 0;">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group content-donate-section-form-div2-input">
                                    <div class="content-donate-section-form-div2-input-group1 col-sm-6 ">
                                        <input type="text" class="" name="fname" id="fname" placeholder="First Name">
                                    </div>
                                    <div class="content-donate-section-form-div2-input-group2 col-sm-6">
                                        <input type="text" class="" name="lname" id="lname" placeholder="Last Name">
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="content-donate-section-form-div4" style="margin-top: 10px; margin-bottom: 0px !important;">
                                    <div class="form-group content-donate-section-form-div4-input" style="margin-bottom: 0px !important;">
                                        <input type="email" class="" name="email" id="email" placeholder="Your Email Address">
                                    </div> 
                                </div>
                                <div class="clearfix"></div>
                                <div class="content-donate-section-form-div4" style="margin-top: 10px; margin-bottom: 0px !important;">
                                    <div class="input-group content-donate-section-form-div6-input-group">
                                        <textarea name="message" id="message" placeholder="Message"></textarea>
                                    </div>
                                </div>  
                                <div class="clearfix"></div>
                                <div class="form-group content-donate-section-form-div2-input" style="margin-top: 10px;">
                                    <div class="content-donate-section-form-div2-input-group1 col-sm-6 ">
                                        <input type="text" class="" name="input_word" id="input_word" placeholder="Enter Captcha">
                                    </div>
                                    <div class="captcha_div" style="float: left; width: 150px; height: 60px; background: none;">
                                    </div>
                                    <input type="hidden" name="captcha_word" id="captcha_word" value=""> 
                                </div>
                                <button name="refresh_captcha" id="refresh_captcha" style="height: 30px; width: 30px; margin-top:8px;background-size: contain;"><i class="fa fa-refresh fa-spin"></i>
                                </button>                                                                                                                                
                                <div class="clearfix"></div>
                                <div id="error_placeholder" class="form-message"></div> 
                                <div class="signup-email-footer-bg text-center">
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg">Send</button>
                                </div>
                            </div>
                        </form> 
                    </div>          
                </div>
                <div class="success-msg" style="display: none; text-align: center;">
                    <img src="<?php echo $this->theme->url('assets/img/env.png'); ?>" style="width: 60px; height: 60px; margin-top: 20px;">
                    <h3>THANK YOU!</h3>
                    <h5>Your message has been sent!</h5>  
                    <div class="signup-email-footer-bg text-center">
                        <button name="ok-btn" class="btn btn-primary btn-lg ok-btn">CLOSE WINDOW</button>
                    </div>      
                </div>

                <div class="error-msg" style="display: none; text-align: center;">
                    <img src="<?php echo $this->theme->url('assets/img/env.png'); ?>" style="width: 60px; height: 60px; margin-top: 20px;">
                    <h3>MESSAGE SENDING FAILED!</h3>
                    <h5>Please try again!</h5>  
                    <div class="signup-email-footer-bg text-center">
                        <button name="ok-btn" class="btn btn-primary btn-lg ok-btn">CLOSE WINDOW</button>
                    </div>      
                </div>

                <div class="loading-msg" style="display: none; text-align: center;">
                    <i class="fa fa-spinner fa-spin fa-4x"></i>
                    <h3>LOADING...</h3>
                    <h5>Please wait!</h5>
                    <div class="signup-email-footer-bg text-center">
                        <button name="ok-btn" class="btn btn-primary btn-lg ok-btn">CLOSE WINDOW</button>
                    </div>
                </div>	
            </div>
        </div>
    </div>
</div>
End contactModal Modal 

<script>
    jQuery(document).ready(function ($) {
        $('#contactModal').on('shown.bs.modal', function (e) {
            $.ajax({
                url: "<?php echo site_url('campaign/load_captcha') ?>",
                dataType: 'json'
            }).done(function (res) {
                console.log(res);
                $('.captcha_div').html(res.captcha_image);
                $('#captcha_word').val(res.captcha_word);
            });
        });

        $('#refresh_captcha').on('click', function (e) {
            $.ajax({
                url: "<?php echo site_url('campaign/load_captcha') ?>",
                dataType: 'json'
            }).done(function (res) {
                console.log(res);
                $('.captcha_div').html(res.captcha_image);
                $('#captcha_word').val(res.captcha_word);
            });
        });

        $('#contactModal').on('hidden.bs.modal', function (e) {
            $('.success-msg').hide();
            $('.error-msg').hide();
            $('.modal-body-hide').show();
            $('#campaign_contact_form')[0].reset();
        });
    });

    var campaign_contact_form = $('#campaign_contact_form');
    //var low_word = $('#input_word').attr("checked", true).val().toLowerCase();
    campaign_contact_form.validate({
        ignore: "", //validate hidden field
        rules: {
            fname: {
                required: true
            },
            lname: {
                required: true
            },
            email: {
                required: true
            },
            message: {
                required: true
            },
            input_word: {
                required: true,
                equalTo: "#captcha_word"
            }
        },
        messages: {
            input_word: {
                equalTo: "Wrong Capcha"
            }
        },
        errorClass: 'help-inline',
        errorElement: 'span',
        errorPlacement: function (error, element) {
            $(element).tooltip('destroy').tooltip({
                html: true,
                trigger: 'manual',
                //                    container: 'body',
                title: error,
                //template: '<div class="tooltip for-error"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                template: '<div class="tooltip top for-error"><div class="tooltip-inner"></div><div class="tooltip-arrow"></div></div>'
            }).tooltip('show');
        },
        unhighlight: function (element, errorClass, validClass) {
            var elem = $(element);
            $(element).tooltip('destroy');
        }
    });

    campaign_contact_form.ajaxForm({
        type: 'POST',
        dataType: "json",
        url: $(this).attr('url'),
        beforeSubmit: function showRequest(formData, jqForm, options) {
            var valid = $(jqForm).valid();
            if (valid) {
            }
            $('.modal-body-hide').hide();
            $('.loading-msg').show();
            return $(jqForm).valid();
        },
        uploadProgress: function (event, position, total, percentComplete) {
            //$('.modal-body-hide').hide();
        },
        success: function showResponse(statusText, responseText, xhr, jqForm) {
            $('.loading-msg').hide();

            if (statusText.status)
            {
                $('.success-msg').show();
            } else {
                $('.error-msg').show();
            }
        }
    });
    $('.ok-btn').on('click', function (e)
    {
        $('#contactModal').modal('hide');

    });
</script>-->