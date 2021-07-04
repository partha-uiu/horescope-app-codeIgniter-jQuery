
	<div class="top-bar">
		
	</div>
    <div style="border:1px dashed #333333; width:300px; margin:0 auto; padding:10px;text-align: center;">
    <h3>Upload Birthday Data CSV</h3>
    <form name="import" method="post" enctype="multipart/form-data" action="<?php echo site_url(CPREFIX . '/birthday/upload_birthday_csv'); ?>">
            <input type="file" name="file" /><br />
            <input type="submit" name="submit" value="Submit" />
        </form>
    
    </div>
    <hr style="margin-top:300px;" />	
    

