<section>
        <div class="container">    
            <div id="loginbox" style="margin-top:50px;margin-bottom: 60px;" class="mainbox col-md-6 col-md-offset-3 col-sm-4 col-xs-3 col-sm-offset-2">                    
                <div class="panel panel-info" >
                    <div class="panel-heading">
                        <div class="panel-title">Sign In</div>
                        <!--<div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>-->
                    </div>     
                        
                    <div style="padding-top:30px" class="panel-body" >
                        
                        <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>

                         <form method="post" action="<?php echo base_url('login/check_login'); ?>" id="loginform" class="form-horizontal">
                            
                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input id="login-username" type="email" class="form-control" name="email" value="" placeholder="Email">                                        
                            </div>
                                        
                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input id="login-password" type="password" class="form-control" name="password" placeholder="password">
                            </div>         
                                        
                            <div style="margin-top:10px;text-align: center;" class="form-group">
                                <!-- Button -->
                                        
                                <div class="col-sm-12 controls"> 
                                     <button id="btn-signup" type="submit" class="btn btn-info" style="width: 130px;font-size: 15px;"><i class="icon-hand-right"></i> &nbsp Login</button>     
                                </div>
                            </div>
                                    
                                    
                            <div class="form-group">
                                <div class="col-md-12 control">
                                    <div style="border-top: 1px solid#888; padding-top:20px; font-size:15px" >
                                        Don't have an account! 
                                        <a href="#" onClick="$('#loginbox').hide(); $('#signupbox').show()">
                                            <b>Sign Up Here</b>
                                        </a>
                                    </div>
                                </div>
                            </div>    
                        </form>                                   
                    </div>                     
                </div>  
            </div>
            <div id="signupbox" style="display:none; margin-top:50px;margin-bottom: 55px;" class="mainbox col-md-6 col-md-offset-3 col-sm-4 col-xs-3 col-sm-offset-2">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="panel-title">Sign Up</div>
                        <div style="float:right; font-size: 85%; position: relative; top:-15px"><a id="signinlink" style="font-size: 15px;" href="#" onclick="$('#signupbox').hide(); $('#loginbox').show()"><b>Sign In</b></a></div>
                    </div>  
                    <div class="panel-body" >
                        <form method="post" action="<?php echo base_url('signup'); ?>" id="signupform" class="form-horizontal">

                            <div id="signupalert" style="display:none" class="alert alert-danger">
                                <p>Error:</p>
                                <span></span>
                            </div>
                            <div class="form-group">
                                <label for="firstname" class="col-md-3 control-label">First Name</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="firstname" placeholder="First Name">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lastname" class="col-md-3 control-label">Last Name</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="lastname" placeholder="Last Name">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lastname" class="col-md-3 control-label">Gender</label>
                                <div class="col-md-9" style="margin-top: 5px;">
                                    <input type="radio" name="sex" id="inlineCheckbox1" value="0" />Male
                                    <input type="radio" name="sex" id="inlineCheckbox2" value="1" />Female
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lastname" class="col-md-3 control-label">Birthday</label>
                                <div class="col-md-3">
                                    <select id="month" class="form-control" name="month">
                                    <option value="">Month</option>
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" name="day">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++){ ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                                </div>
                                <div class="col-md-3"> 
                                    <select class="form-control" name="year">
                                    <option value="">Year</option>
                                    <?php $a = date("Y"); ?>
                                    <?php for($i=1900;$i<=$a;$i++){ ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email" class="col-md-3 control-label">Email</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="email" placeholder="Email Address">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password" class="col-md-3 control-label">Password</label>
                                <div class="col-md-9">
                                    <input type="password" class="form-control" name="password" placeholder="Password">
                                </div>
                            </div>
                                    
                            <div class="form-group">
                                <!-- Button -->                                        
                                <div class="col-md-offset-3 col-md-9" style="text-align: center;">
                                    <button id="btn-signup" type="submit" class="btn btn-info" style="width: 130px;font-size: 15px;"><i class="icon-hand-right"></i> &nbsp Sign Up</button>
                                    <!--<span style="margin-left:8px;">or</span>-->  
                                </div>
                            </div>
                        </form>
                    </div>
                </div>             
            </div> 
        </div>
    </section>