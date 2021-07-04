 <div class="content2">
        <p><b>Log In to Continue...</b></p>
        <form id="myform">
            <div style="width:48%; float: left;">
                <label for="">Email</label><span style="color: red"> *</span><br/>
                <input style="width: 100%;" type="email" name="l_email" required>
            </div>

            <div style="width:48%; float: left; margin-left: 4%;">
                <label for="">Password</label><span style="color: red">*</span><br/>
                <input style="width: 100%;"type="password" name="pass_word" required>
            </div>
            <div class="invalid-login" style="color: red;display: none;">
                <span>**Invalid Email and Password</span>
            </div>
            <input style="margin-top: 15px;" type="submit" class="button" id="login" name="login" value="Login">
        </form>
</div>

    
    <div id="content1" class="content_area">
        <div style=" margin:1% 10% 1% 0;">
            <h3 >Sign Up</h3><br/>
        </div>
        <form id="order_form" action="<?php echo site_url(CPREFIX. '/checkout/insert_user')?>">
            <div>
                <div style="width:48%; float: left;">
                    <label for="">First Name</label><span style="color: red"> *</span><br/>
                    <input style="width: 100%;" type="text" name="fname" required  placeholder="First Name"  >
                </div>
                    
                <div style="width:48%; float: left; margin-left: 4%;">
                    <label for="">Last Name</label><span style="color: red">*</span><br/>
                    <input style="width: 100%;"type="text" class="lname" name="lname" required placeholder="Last Name" >
                </div>
            </div>
            <div style="display:inline-block;  margin-top:2%;"> <label >Company Name</label><br/>
                <input style="width:100%;" type="text"  name="company_name" placeholder="Company Name" size="170" >
            </div>
            <div style=" margin-top:2%;"> <label >Address</label><span style="color: red"> *</span><br/>
                <input style=" width: 100%;" type="text" name="address" required  placeholder="Street Address" >
                <!--<input style="margin-top:10px; width: 100%"type="email"   placeholder="Apartment,suit,unit etc.(optional)">-->
            </div>
            <div>
                    
                <div style="float:left ; width:48%;  margin-top: 2%; margin-right:5%;">
                    <label for="">Post Code</label><span style="color: red"> *</span>
                    <input style="width: 100%;" type="text" required name="postcode" placeholder="Postcode/Zip" >
                </div>
                <div style="float:left; width:47%;; margin-top: 2%;">
                    <label for="">Phone</label><span style="color: red"> *</span><br/>
                    <input style="width: 100%;" type="tel" required name="phone"  placeholder="Your Phone" >
                </div>
                <div style="float:left ;width:48%; margin-top: 2%; ">
                    <label for="">Email Address</label><span style="color: red"> *</span><br/>
                    <input style="width: 100%;" type="email" required name="email"  placeholder="Your E-mail"  >
                </div>
                <div style="float:left ;width:47%; margin-top: 2%;margin-left: 5%;">
                    <label for="">Password</label><span style="color: red"> *</span><br/>
                    <input style="width: 100%;" type="password" required name="password"  placeholder="Your Password"  >
                </div>
                    
            </div>
        </form>
            
    </div>
    <!------------------------------->
       
    <div class="place-order">
            <i class="fa fa-location-arrow"></i>
            <button id="signup">Submit</button>
            
   </div>
    
    <script>
        $('#signup').on('click', function(){
            
            var fname= $("[name=fname]").val();      
	    var lname = $(".lname").val();
	    var company_name = $("[name=company_name]").val();
	    var address = $("[name=address]").val();
	    var postcode = $("[name=postcode]").val();
	    var email = $("[name=email]").val();
	    var phone = $("[name=phone]").val();
	    var password = $("[name=password]").val();

            $.ajax({
                type: "POST",
                dataType: 'json',
                url: '<?php echo site_url('signup/sign_up_user'); ?>',
                // context: $(this).parents('form'),
                data: {fname: fname,
                       lname: lname,
                       company_name: company_name,
                       address: address,
                       postcode: postcode,
                       email: email,
                       password: password,
                       phone: phone },
                success: function(response) {
                    console.log(response.status);
                    $("#content1").hide();
                    $(".place-order").hide("slow");
                    $(".content2").show("slow");
                                     
                },
                error: function() {
                    //  alert("Something went wrong");
                }
            });
        });
        $('#myform').validate({
            rules:{
                l_email: {required: true},
                pass_word: {required: true}
            }
        
        });
        $('#login').on('click', function(e){
        e.preventDefault();
           if($('#myform').valid()){ 
            var e_mail = $("[name=l_email]").val();
	    var pass_word  = $("[name=pass_word]").val();
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: '<?php echo site_url('login/check_login'); ?>',
                // context: $(this).parents('form'),
                data: {e_mail: e_mail,
                       pass_word: pass_word},
                success: function(response) {
                    console.log(response.status);
                    if(response.status == true){
                       window.location= '<?php echo site_url('checkout'); ?>'; 
                    }
                    else{
                        $(".invalid-login").show();
                    }                                 
                },
                error: function() {
                    //  alert("Something went wrong");
                }
            });
            }
        });

    </script>
