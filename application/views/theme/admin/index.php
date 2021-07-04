<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?php echo $title_for_layout; ?> | Technology of Bliss</title>
        <script type="text/javascript">
            window.location.base_url = '<?php echo site_url() ?>';
            window.location.header_nav = {
                active: '<?php echo @$active_header_nav ?>'
            }
        </script>
        <?php
        echo $this->theme->css('assets/bootstrap/css/bootstrap.css');
        echo $this->theme->css('assets/bootstrap/css/bootstrap-responsive.css');
        echo $this->theme->css('assets/font-awesome/css/font-awesome.min.css');
        echo $this->theme->js('assets/js/common/jquery-1.9.1.min.js');
        echo $this->theme->js('assets/js/common/jquery.validate.js');

        echo $this->theme->css('assets/jquery-ui/css/smoothness/jquery-ui-1.10.1.custom.min.css');
        echo $this->theme->js('assets/jquery-ui/js/jquery-ui-1.10.1.custom.min.js');

        echo $this->theme->js('assets/bootstrap/js/bootstrap.min.js');
        ?>
        <?php
        echo $this->theme->js('assets/pnotify/jquery.pnotify.min.js');
        echo $this->theme->css('assets/pnotify/jquery.pnotify.default.css');
        echo $this->theme->css('assets/pnotify/jquery.pnotify.default.icons.css');
        ?>
        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
                    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
                <![endif]-->
        <?php echo $this->theme->js('assets/js/general.js'); ?>
        <?php echo $this->theme->js('assets/js/common/jquery.sd.common.js'); ?>
        <!--datatable initializing-->
        <?php echo $this->theme->css('assets/datatable/DT_bootstrap.css'); ?>
        <?php echo $this->theme->js('assets/datatable/jquery.dataTables.min.js'); ?>
        <?php echo $this->theme->js('assets/datatable/jquery.dataTables.Bootstrap.Pagination.js'); ?>
        <?php
//        echo $this->theme->js('assets/colorpicker/bootstrap-colorpicker.js');
//        echo $this->theme->css('assets/colorpicker/colorpicker.css');
//        echo $this->theme->js('assets/datepicker/bootstrap-datepicker.js');
//        echo $this->theme->css('assets/datepicker/datepicker.css');
        ?>
        <?php echo $this->theme->css('assets/css/styles.css'); ?>
    </head>

    <body>
        <div class="container-fluid nopadding">
            <div class="row-fluid">
                <div class="span12">
                    <div id="header">
                        <div class="hleft">
                            <div class="column">
                                <a href="<?php echo site_url('/') ?>">
                                    <h1>Technology of Bliss<h3>Admin Panel</h3></h1>
                                </a>
                            </div>
                        </div>
                        <div class="hright">
                            <a class="header-logout" style="color: #FFF; float: right;" href="<?php echo site_url(CPREFIX . "/logout"); ?>"><span class="ico"><i class="icon-off icon-white"></i></span> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" id="container">
            <div class="row-fluid">
                <div class="span3 leftmenu" >
                    <ul class="nav">                        
                        <li class="active"><a href="<?php echo site_url(CPREFIX . '/dashboard'); ?>"><span class="ico"><i class="icon-home"></i></span><span class="text">Dashboard</span></a></li>                        
                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/traits'); ?>" ><span class="ico"><i class="icon-th-large"></i></span><span class="text">Trait</span><span class="indicator"></span></a>
                            <ul>
                                <li><a href="<?php echo site_url(CPREFIX . '/traits/traits_add'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Add Trait</span></a></li>
<!--                                <li><a href="--><?php //echo site_url(CPREFIX . '/traits/traits_details_add'); ?><!--"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Add Trait Details</span></a></li>-->

                                <li><a href="<?php echo site_url(CPREFIX . '/traits/show_male'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Trait list for Male</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . '/traits/show_female'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Trait list for  Female</span></a></li>
                            </ul>                        
                        </li>
                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/traits_details'); ?>" ><span class="ico"><i class="icon-th-large"></i></span><span class="text">Trait Details</span><span class="indicator"></span></a>
                            <ul>

                                <li><a href="<?php echo site_url(CPREFIX . '/traits/add_trait_details'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Add Trait Details</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . '/traits/trait_details_male'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Trait Details For Male</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . '/traits/trait_details_female'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Trait Details For Female</span></a></li>
                            </ul>
                        </li>

                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/birthday_value'); ?>" ><span class="ico"><i class="icon-th-large"></i></span><span class="text">Birthday Value</span><span class="indicator"></span></a>
                            <ul>

                                <li><a href="<?php echo site_url(CPREFIX . '/birthday/upload_birthday'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Upload Birthday Data</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . '/birthday/search_birthday'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Search Birthday</span></a></li>


                            </ul>
                        </li>

                        <li><a href="<?php echo site_url(CPREFIX . '/users_report'); ?>"  data-ref-child="" ><span class="ico"><i class="icon-th-large"></i></span><span class="text">All Users Report</span><span class="indicator"></span></a></li>
                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/quotes/quotes_list'); ?>" ><span class="ico"><i class="icon-th-large"></i></span><span class="text">Quotes</span><span class="indicator"></span></a>
                            <ul>
                                <li><a href="<?php echo site_url(CPREFIX . '/quotes/add_quotes'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Add Quotes</span></a></li>

                                <li><a href="<?php echo site_url(CPREFIX . '/quotes/quotes_list'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Quotes List</span></a></li>
                            </ul>                        
                        </li>
                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/about/about_list'); ?>" ><span class="ico"><i class="icon-th-large"></i></span><span class="text">About</span><span class="indicator"></span></a>
                            <ul>
                                <!--<li><a href="<?php echo site_url(CPREFIX . '/about/add_about'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Add About Text</span></a></li>-->

                                <li><a href="<?php echo site_url(CPREFIX . '/about/about_list'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">View About Text</span></a></li>
                            </ul>                        
                        </li>
                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/testimonials/testimonial_list'); ?>" ><span class="ico"><i class="icon-th-large"></i></span><span class="text">Testimonials</span><span class="indicator"></span></a>
                            <ul>
                                <li><a href="<?php echo site_url(CPREFIX . '/testimonials/add_testimonial'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Add Testimonial</span></a></li>

                                <li><a href="<?php echo site_url(CPREFIX . '/testimonials/testimonial_list'); ?>"><span class="ico"><i class="icon-chevron-right"></i></span><span class="text">Testimonial List</span></a></li>
                            </ul>                        
                        </li>



                        <li><a href="<?php echo site_url(CPREFIX . "/logout"); ?>"><span class="ico"><i class="icon-off"></i></span>Logout</a></li>                    
                    </ul>
                </div>

                <script type="text/javascript">
                    //auto active left menu
                    jQuery(document).ready(function($) {
                        //active last menu
                        var lf_nav = $(".leftmenu ul");
                        var actURL = (window.location.header_nav.active ? window.location.header_nav.active : window.location.href);
                        if (0 == lf_nav.find('[href="' + actURL + '"]').length) {
                            var tact = actURL.split('/');
                            tact.pop();
                            actURL = tact.join('/');
                        }
                        lf_nav.find('.active').removeClass('active').end();
                        lf_nav.find('[href="' + actURL + '"]')
                        .parents('li:first').addClass('active')
                        .parents('li').find('a:first').trigger('click')
                        ;
                    });
                </script>
                <script type="text/javascript">
                    /*/auto active left menu
                        jQuery(document).ready(function($) {
                        //active last menu
                        var lf_nav = $(".leftmenu ul");
                        //link to reference menu
                        lf_nav.find('[data-ref-child]').on('click',function() {
                        redirectto = $(this).attr('data-ref-child');
                        if (redirectto)
                        window.location = redirectto;
                        });
                        });*/
                </script>
                <div class="span9" id="content">
                    <?php
                    if (!function_exists('getFlushMsg')) {
                        function getFlushMsg($Session) {
                            global $lastFlushMessage;
                            $class = '';

                            $flash = $Session->flash('flash', array('element' => false));
                            if ($flash) {
                                $class = 'alert alert-info';
                                return $lastFlushMessage = $flash = '<div class="span alert ' . $class . '">' . '<button type="button" class="close" data-dismiss="alert">×</button>' . $flash . '</div>';
                            }

                            $flash = $Session->flash('success', array('element' => false));
                            if ($flash) {
                                $class = 'alert alert-success';
                                return $lastFlushMessage = $flash = '<div class="span alert ' . $class . '">' . '<button type="button" class="close" data-dismiss="alert">×</button>' . $flash . '</div>';
                            }

                            $flash = $Session->flash('error', array('element' => false));
                            if ($flash) {
                                $class = 'alert alert-error';
                                return $lastFlushMessage = $flash = '<div class=" span alert ' . $class . '">' . '<button type="button" class="close" data-dismiss="alert">×</button>' . $flash . '</div>';
                            }

                            $flash = $Session->flash('warning', array('element' => false));
                            if ($flash) {
                                $class = 'alert alert-warning';
                                return $lastFlushMessage = $flash = '<div class="span alert ' . $class . '">' . '<button type="button" class="close" data-dismiss="alert">×</button>' . $flash . '</div>';
                            }

                            $flash = $Session->flash('info', array('element' => false));
                            if ($flash) {
                                $class = 'alert alert-info';
                                return $lastFlushMessage = $flash = '<div class="span  alert ' . $class . '">' . '<button type="button" class="close" data-dismiss="alert">×</button>' . $flash . '</div>';
                            }
                        }

                    }
                    $msg = getFlushMsg($this->ahrsession);
//                    echo!empty($msg) ? '<div class="row-fluid" style="display: inline-table;">' . $msg . '</div>' : '';
                    echo @$msg;
                    ?>

                    <!--start body content-->
                    <?php echo $this->fn->warningMessage(); ?>
                    <?php echo @$ci_content; ?>
                    <!--end body content-->

                </div>
            </div>
        </div>
    </body>
</html>