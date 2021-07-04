<div class="content">
    <div class="container">
        <ul class="breadcrumb">
            <li><b style="font-size: large">Forgot Username or Password!!!</b></li>
        </ul>
        <br>
         <div class="box-content-bg">
        <div class="row-fluid apps-home">
            <div class="container-fluid">
            <div class="row-fluid">                           <div class="span12">
                    <div class="box login well">
                            <div><?php echo (isset($error)) ? $error : ""; ?></div>
                            <div><?php echo (isset($success)) ? $success : ""; ?></div>
                        <div class="box-content">
                            <form class="form-horizontal" method="post" action="" style="margin-top: 0px;">
                                <p style="text-align: center; "><?php // echo _('Please login to continue') ?></p>
                                <div class="control-group">
                                    <label for="user_login" class="control-label">Enter your email</label>
                                    <div class="controls">
                                        <input type="email" name="email" id="email" class="span4" required="1">
                                    </div>
                                </div>
                                <div class="control-group">                                   
                                    <br>
                                     <div class="controls">
                                         <input type="submit" name="btn" value="Submit" class="btn btn-primary"/>
                                    </div>
                                </div>                                                                                        </form>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>