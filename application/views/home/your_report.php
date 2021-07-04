<div class="row">
    <div class="col-md-12">
        <h2 class="success">Your file is ready to download</h2>
    </div>
    <div style="margin-top: 50px;margin-bottom: 12%;text-align: center;">
        <span>Your File : &nbsp</span>
        <a href="<?php echo site_url('home/download').'/'.$user_id;?>">Report_TechofBliss_<?php echo $user_id; ?>.pdf</a>
    </div>
    
</div>
<style>
    .success {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 35px;
            /*margin: 100px 0 125px 0;*/
            font-size: 28px;
            color: green;
    }
</style>