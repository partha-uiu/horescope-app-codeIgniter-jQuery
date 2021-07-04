<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="shortcut icon" href="<?php echo site_url('/assets/img/BlueOwl-16x16.png')?>">
        <title><?php echo $title_for_layout; ?> | FundMyPet</title>
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
                                    <h1>Fund My Pet<h3>Admin Panel</h3></h1>
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
                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/users'); ?>" ><span class="ico"><i class=" icon-user"></i></span><span class="text">Users</span><span class="indicator"></span></a>
                            <ul>
                                <li><a href="<?php echo site_url(CPREFIX . '/users/add'); ?>"><span class="ico"><i class="icon-plus"></i></span><span class="text">Add User</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . '/users'); ?>"><span class="ico"><i class="icon-user"></i></span><span class="text">Users</span></a></li>
                            </ul>
                        </li>

                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/category'); ?>" ><span class="ico"><i class=" icon-user"></i></span><span class="text">Category</span><span class="indicator"></span></a>
                            <ul>
                                <li><a href="<?php echo site_url(CPREFIX . '/category/add'); ?>"><span class="ico"><i class="icon-plus"></i></span><span class="text">Add Category</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . '/category'); ?>"><span class="ico"><i class="icon-user"></i></span><span class="text">Categories</span></a></li>
                            </ul>
                        </li>
<!--                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/tags'); ?>" ><span class="ico"><i class=" icon-user"></i></span><span class="text">Tags</span><span class="indicator"></span></a>
                            <ul>
                                <li><a href="<?php echo site_url(CPREFIX . '/tags/add'); ?>"><span class="ico"><i class="icon-plus"></i></span><span class="text">Add Tags</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . '/tags'); ?>"><span class="ico"><i class="icon-user"></i></span><span class="text">Tags List</span></a></li>
                            </ul>
                        </li>-->


                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/campaign'); ?>" ><span class="ico"><i class=" icon-mobile-phone"></i></span><span class="text">Campaign</span><span class="indicator"></span></a>
                            <ul>
                                <li><a href="<?php echo site_url(CPREFIX . '/campaign/add'); ?>"><span class="ico"><i class="icon-plus"></i></span><span class="text">Add Campaign</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . '/campaign'); ?>"><span class="ico"><i class="icon-th-list"></i></span><span class="text">Campaign</span></a></li>
                            </ul>
                        </li>
<!--                        <li> <a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/payment'); ?>" ><span class="ico"><i class=" icon-mobile-phone"></i></span><span class="text">Payment</span><span class="indicator"></span></a>
                            <ul>
                                <li><a href="<?php echo site_url(CPREFIX . '/payment/transactions'); ?>"><span class="ico"><i class="icon-plus"></i></span><span class="text">Transactions</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . 'payment/payment-gateway'); ?>"><span class="ico"><i class="icon-th-list"></i></span><span class="text">Payment Gateway</span></a></li>
                                <li><a href="<?php echo site_url(CPREFIX . 'payment/paypal'); ?>"><span class="ico"><i class="icon-th-list"></i></span><span class="text">PayPal Account</span></a></li>
                            </ul>
                        </li>-->
<!--                    <li><a href="#"  data-ref-child="<?php echo site_url(CPREFIX . '/email'); ?>" ><span class="ico"><i class=" icon-mobile-phone"></i></span><span class="text">Marketing</span><span class="indicator"></span></a>
                            <ul> 
                                <li><a href="<?php echo site_url(CPREFIX . '/email/email_marketing'); ?>"><span class="ico"><i class="icon-th-list"></i></span><span class="text">Email</span></a></li>
                            </ul>
                        </li>-->
                        <li><a href="<?php echo site_url(CPREFIX . "/affiliate"); ?>"><span class="ico"><i class="icon-mobile-phone"></i></span> Affiliate</a></li>
                        <li><a href="<?php echo site_url(CPREFIX . "/logout"); ?>"><span class="ico"><i class="icon-off"></i></span> Logout</a></li>
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
