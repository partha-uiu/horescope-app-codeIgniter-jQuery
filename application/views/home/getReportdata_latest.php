<div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12 sol-xs-12">
                <div class="table_header">Input</div>
                <div class="table-container">
                    <div class="table-row">
                        <div class="table-col">First Name</div>
                        <div class="table-col1"><?php echo $firstname; ?></div>
                    </div>
<!--                    <div class="table-row">
                        <div class="table-col">Last Name</div>
                        <div class="table-col1"><?php //echo $lastname; ?></div>
                    </div>-->
                    <div class="table-row">
                        <div class="table-col">Male/Female</div>
                        <div class="table-col1"><?php 
                            if($sex==0){
                                echo 'Male';
                            }
                            else {
                                echo 'Female';
                            }
                          
                          ?></div>
                    </div>
                    <div class="table-row">
                        <div class="table-col">Birthday(mm/dd/yyyy)</div>
                        <div class="table-col1"><?php echo $birthday; ?></div>
                    </div>
<!--                    <div class="table-row">
                        <div class="table-col">Current Country</div>
                        <div class="table-col1"><?php //echo $country; ?></div>
                    </div>
                    <div class="table-row">
                        <div class="table-col">Current City</div>
                        <div class="table-col1"><?php //echo $city; ?></div>
                    </div>-->
                  </div>
                
                
                     <div class="table_header">Highlights</div>
                     <div class="table-container">
                         <?php foreach($final_result as $r): ?>
                         <?php
                         $text = str_split($r->header_value);
                         if($text[0]== 'a' || $text[0] =='A'):
                             ?>
                         <div class="table-row">
                            <div class="table-col"><?php echo $r->header_value; ?></div>
                            <div class="table-col1"><?php echo $r->return_details; ?></div>
                        </div>
                         <?php endif; ?>
                         <?php endforeach; ?>
                     </div>
                    
                <button class="btn btn-info view_details">Going Deeper</button>
                
                     <div style="display: none;margin-bottom: 30px;" class="details_table">
                         <div class="table_header">Going Deeper</div>
                         <div class="table-container">
                             <?php foreach($final_result as $r): ?>
                             <div class="table-row">
                                <div class="table-col"><?php echo $r->header_value; ?></div>
                                <div class="table-col1"><?php echo $r->return_details; ?></div>
                            </div>
                             <?php endforeach; ?>
                         </div>
                     </div>
            </div>
        </div>
        <a href="<?php echo site_url('home/getpdf/'.$user_id); ?>">
        <div style="float: right; width: 138px; height: 100%; padding: 8px 10px 1px; border: 1px #d9d9d9 solid; border-radius: 5px; margin: 10px 50px 10px 0;background-color: aqua;
    font-size: 15px;">
            <p>Download Report</p>
        </div>
        </a>
    </div>
<style>
            .table-container{
                display:table;
                border-collapse: table-collapse;
                width:90%;
                margin-left: 5%;
                margin-bottom: 15px;
                border: 1px solid skyblue;
            }
            .table-row{  
                display:table-row;
                /*text-align: center;*/
            }
            .table-head-col {
                border: 1px solid red;
                display:table-cell;
                padding: 8px 5px;
            }

            .table-col{
                display:table-cell;
                padding: 8px 5px;
                border-bottom: 1px solid skyblue;
                /*border: 1px solid #00ab54;*/
                width: 30%;
                text-align: center;
                font-weight: bold;
            }
            .table-col1{
                display:table-cell;
                padding: 8px 5px;
                border-bottom: 1px solid skyblue;
                /*border: 1px solid #00ab54;*/
                width: 70%;
                text-align: left;
            }
            .table_header {
                width: 90%;
                text-align: center;
                background-color: skyblue;
                /*background-color: burlywood;*/
                font-size: 22px;
                line-height: 40px;
                margin-left: 5%;
            }
           .view_details {
                margin: 25px 0 25px 47%;
            }
        </style>
<script>
    $(document).ready(function(){
        $(".view_details").click(function(e){
                e.preventDefault();
                $(".view_details").hide(); 
                $(".details_table").show(); 
            });
    });
</script>
