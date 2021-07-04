<div class="content">
    <div class="container">
        <ul class="breadcrumb">
            <li><b style="font-size: x-large">YappGuru User Login</b></li>
        </ul>
        <br>
         <div class="box-content-bg">
        <div class="row-fluid apps-home">
           
            <div class="container-fluid">
            <div class="row-fluid">                
                <div class="span12"> 
                    <div class="box login well">
                    
                        <!--<div class="box-title"> <span class="ico"><i class="icon-lock"></i></span>&nbsp; User Login</div>-->
                        <div class="box-content">
                            <?php if ($this->ahrsession->get('warning')) { ?>
                                <div class="alert alert-error" style="margin-bottom: 5px; text-align: center; font-weight: bold; z-index: 123;">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <?php
                                    $warning = $this->ahrsession->get('warning');
                                    if (is_array($warning) || is_object($warning)) {
                                        echo implode('<br/>', $warning);
                                    } else if (is_string($warning)) {
                                        echo $warning;
                                    }
                                    $this->ahrsession->delete('warning');
                                    ?></div>
                            <?php } ?>

                            <form class="form-horizontal" method="post" action="" style="margin-top: 0px;">
                                <p style="text-align: center; "><?php // echo _('Please login to continue') ?></p>
                                <div class="control-group">
                                    <label for="user_login" class="control-label">Username</label>
                                    <div class="controls">
                                        <input type="text" name="username" id="username"  value="<?php echo set_value('username') ?>" required="1" />
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="user_pass" class="control-label">Password</label>
                                    <div class="controls">
                                        <input type="password" name="password" id="password"  value="<?php echo set_value('password') ?>" required="1" />
                                    </div>
                                    <br>
                                     <div class="controls">
                                         <input type="submit" value="Login" class="btn btn-primary"/>&nbsp;
                                        <a href="<?php echo site_url(CPREFIX . '/users/forget'); ?>">Forgot Username or Password?</a>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="user_login" class="control-label">&nbsp;</label>
                                    <div class="controls">
                                        Don't have an account? Please <a href="<?php echo site_url(CPREFIX . '/home/registration'); ?>">click here</a> to create an account.
                                    </div>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>