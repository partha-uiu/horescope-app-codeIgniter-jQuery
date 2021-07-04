<div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12 sol-xs-12">
                <div class="table_header">Input</div>
                <div class="new_area">
                <span class="head1">Name</span>
                <span class="head2"><?php echo $firstname; ?></span>
                <hr>
                <span class="head1">Male/Female</span>
                <span class="head2"><?php 
                            if($sex==0){
                                echo 'Male';
                            }
                            else {
                                echo 'Female';
                            }
                          
                          ?></span>
                <hr>
                <span class="head1">Birthday(mm/dd/yyyy)</span>
                <span class="head2"><?php echo $birthday; ?></span>
            </div>               
               
                     <div class="table_header">Highlights</div>

                     <div class="new_area go1">
                        <span class="head1"><?php echo $head1; ?></span>
                        <span class="head2"><?php echo $d1; ?></span>
                        <span class="deep1 "><i class="fa fa-plus-circle"></i></span>
                    </div>
                     <div style="margin-bottom: 30px;display:none;" class="details_table1">
                         <div class="table-container">
                             <?php foreach($deep1 as $d1): ?>
                             <div class="table-row">
                                <div class="table-col"><?php echo $d1->header_value; ?></div>
                                <div class="table-col1"><?php echo $d1->return_details; ?></div>
                            </div>
                             <?php endforeach; ?>
                         </div>
                    </div>
<!--                     <div class="new_area">
                        <span class="head1"><?php// echo $head2; ?></span>
                        <span class="head2"><?php// echo $d2; ?></span>
                    </div> -->
                     <div class="new_area go2">
                        <span class="head1"><?php echo $head3; ?></span>
                        <span class="head2"><?php echo $d3; ?></span>
                        <span class="deep1 "><i class="fa fa-plus-circle"></i></span>
                    </div> 
                     <div style="margin-bottom: 30px;display:none;" class="details_table2">
                         <div class="table-container">
                             <?php foreach($deep2 as $d2): ?>
                             <div class="table-row">
                                <div class="table-col"><?php echo $d2->header_value; ?></div>
                                <div class="table-col1"><?php echo $d2->return_details; ?></div>
                            </div>
                             <?php endforeach; ?>
                         </div>
                    </div>
                     <div class="new_area go3">
                        <span class="head1"><?php echo $head4; ?></span>
                        <span class="head2"><?php echo $d4; ?></span>
                        <span class="deep1 "><i class="fa fa-plus-circle"></i></span>
                    </div> 
                     <div style="margin-bottom: 30px;display:none;" class="details_table3">
                         <div class="table-container">
                             <?php foreach($deep3 as $d3): ?>
                             <div class="table-row">
                                <div class="table-col"><?php echo $d3->header_value; ?></div>
                                <div class="table-col1"><?php echo $d3->return_details; ?></div>
                            </div>
                             <?php endforeach; ?>
                         </div>
                    </div>
                     <div class="new_area go4">
                        <span class="head1"><?php echo $head5; ?></span>
                        <span class="head2"><?php echo $d5; ?></span>
                        <span class="deep1 "><i class="fa fa-plus-circle"></i></span>
                    </div> 
                     
                     <div style="margin-bottom: 30px;display:none;" class="details_table4">
                         <div class="table-container">
                             <?php foreach($deep4 as $d4): ?>
                             <div class="table-row">
                                <div class="table-col"><?php echo $d4->header_value; ?></div>
                                <div class="table-col1"><?php echo $d4->return_details; ?></div>
                            </div>
                             <?php endforeach; ?>
                         </div>
                    </div>
                     
                     
                     <!------------------------------------------------------>

                    

            </div>
        </div>
        <a href="<?php echo site_url('home/getpdf/'.$user_id); ?>">
        <div style="float: right; width: 138px; height: 100%;padding: 10px 10px 4px; border: 1px #d9d9d9 solid; border-radius: 5px;margin: 15px 53px 15px 0;background-color: aqua;
    font-size: 15px;">
            <p>Download Report</p>
        </div>
        </a>
    </div>
<style>
    
            .new_area {
                background: #e7e7e7;
                padding: 15px;
                line-height: 12px;
                width: 90%;
                margin-left: 5%;
                border-radius: 5px;
                font-size: 16px;
                margin-bottom: 10px;
            }
            .head1 {
                width: 30%;display: block;float: left;
            }
            .head2 {
                display: block;
            }
            .deep1 {
                display: block;
                float: right;
                position: relative;
                top: -16px;
            }
            .deep1 i {
                color: #999;
                font-size: 22px;
            }
            .table-container{
                display:table;
                border-collapse: table-collapse;
                width:90%;
                margin-left: 5%;
                margin-bottom: 15px;
                border: 1px solid lightgray;
                border-radius: 5px;
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
                border-bottom: 1px solid lightgray;
                /*border: 1px solid #00ab54;*/
                width: 30%;
                text-align: center;
                font-weight: bold;
            }
            .table-col1{
                display:table-cell;
                padding: 8px 5px;
                border-bottom: 1px solid lightgray;
                /*border: 1px solid #00ab54;*/
                width: 70%;
                text-align: left;
            }
            .table_header {
                width: 90%;
                text-align: center;
                background-color: lightgray;
                /*background-color: burlywood;*/
                font-size: 22px;
                line-height: 40px;
                margin-left: 5%;
                margin-bottom: 10px;
                border-radius: 5px;
            }
           .view_details {
                margin: 25px 0 25px 47%;
            }
            .go1 , .go2 , .go3 , .go4 {
                cursor: pointer;
            }
        </style>
<script>
    $(document).ready(function(){
        $(".go1").click(function(e){
                e.preventDefault();
                $(".details_table1").toggle('slow');  
            });
        $(".go2").click(function(e){
                e.preventDefault();
                $(".details_table2").toggle('slow');  
            });
        $(".go3").click(function(e){
                e.preventDefault();
                $(".details_table3").toggle('slow');  
            });
        $(".go4").click(function(e){
                e.preventDefault();
                $(".details_table4").toggle('slow');  
            });
    });
</script>
