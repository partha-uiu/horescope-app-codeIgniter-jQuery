<section>
        <div class="row">
                <div class="col-md-2 col-sm-2">
                    
                </div>
                <div class="col-md-8 col-sm-8 about_us">
                    <h3 style="text-align: center;">GET YOUR REPORT</h3>
                    <form class="form-horizontal form_info" method="post" action="<?php if($userid){echo base_url('home/getReportdata').'/'.$userid;} else{echo base_url('home/getReportdata');} ?>">
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <input class="form-control" name="firstname" placeholder="First Name" type="text"
                                      value="<?php if($user_details->fname) {echo $user_details->fname;} ?>" <?php if($user_details->fname){echo 'disabled';} ?> required autofocus />
                            </div>

                        </div>
                        <label for="month">Birth Date</label>
                        <div class="row">
                            <div class="col-xs-4 col-md-4">
                                <select id="month" class="form-control" name="month" <?php if($userid){echo 'disabled';} ?>>
                                    <option value="">Select Month</option>
                                    <option value="1" <?php if($for_birthday[0] == 1){echo 'selected';}?>>January</option>
                                    <option value="2" <?php if($for_birthday[0] == 2){echo 'selected';}?>>February</option>
                                    <option value="3" <?php if($for_birthday[0] == 3){echo 'selected';}?>>March</option>
                                    <option value="4" <?php if($for_birthday[0] == 4){echo 'selected';}?>>April</option>
                                    <option value="5" <?php if($for_birthday[0] == 5){echo 'selected';}?>>May</option>
                                    <option value="6" <?php if($for_birthday[0] == 6){echo 'selected';}?>>June</option>
                                    <option value="7" <?php if($for_birthday[0] == 7){echo 'selected';}?>>July</option>
                                    <option value="8" <?php if($for_birthday[0] == 8){echo 'selected';}?>>August</option>
                                    <option value="9" <?php if($for_birthday[0] == 9){echo 'selected';}?>>September</option>
                                    <option value="10" <?php if($for_birthday[0] == 10){echo 'selected';}?>>October</option>
                                    <option value="11" <?php if($for_birthday[0] == 11){echo 'selected';}?>>November</option>
                                    <option value="12" <?php if($for_birthday[0] == 12){echo 'selected';}?>>December</option>
                                </select>
                            </div>
                            <div class="col-xs-4 col-md-4">
                                <select class="form-control" name="day" <?php if($userid){echo 'disabled';} ?>>
                                    <option value="">Select Day</option>
                                    <?php for($i=1;$i<=31;$i++){ ?>
                                    <option value="<?php echo $i; ?>" <?php if($for_birthday[1] == $i){echo 'selected';} ?>><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-xs-4 col-md-4">
                                <select class="form-control" name="year" <?php if($userid){echo 'disabled';} ?>>
                                    <option value="">Select Year</option>
                                    <?php $a = date("Y"); ?>
                                    <?php for($i=1900;$i<=$a;$i++){ ?>
                                    <option value="<?php echo $i; ?>" <?php if($for_birthday[2] == $i){echo 'selected';} ?>><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <label class="radio-inline" style="font-size: 16px;">
                            <input type="radio" name="sex" id="inlineCheckbox1" value="0" <?php if($user_details->gender == 0){echo 'checked';}?> <?php if($userid){echo 'disabled';} ?>/>
                            Male
                        </label>
                        <label class="radio-inline" style="font-size: 16px;">
                            <input type="radio" name="sex" id="inlineCheckbox2" value="1" <?php if($user_details->gender == 1){echo 'selected';}?> <?php if($userid){echo 'disabled';} ?>/>
                            Female
                        </label>
                        <br />
                        <br />
                        <button class="btn btn-lg btn-primary btn-block" type="submit" style="border-radius: 0;">Submit</button>
                    </form>
                </div>
                <div class="col-md-2 col-sm-2">
                    
                </div>
            </div> 
    </section>
<script language="javascript">
        populateCountries("country", "state");
</script>