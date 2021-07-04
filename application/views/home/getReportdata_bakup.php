    <div class="container">
        <div class="row">
            <div class="col-md-12">
<!--                <table>
                    <tr><th>Input</th></tr>
                </table>-->
                 <table>
                     <tr><th><center>Input</center></th></tr>
                    <tr>
                        <td class="t1">First Name</td>
                        <td class="t2"><?php echo $firstname; ?></td>
                    </tr>
                    <tr>
                        <td class="t1">Last Name</td>
                        <td class="t2"><?php echo $lastname; ?></td>
                    </tr>
                    <tr>
                        <td class="t1">Male/Female</td>
                        <td class="t2">
                          <?php 
                            if($sex==0){
                                echo 'Male';
                            }
                            else {
                                echo 'Female';
                            }
                          
                          ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="t1">Birthday(mm/dd/yyyy)</td>
                        <td class="t2"><?php echo $birthday; ?></td>
                    </tr>
                    <tr>
                        <td class="t1">Current Country</td>
                        <td class="t2"><?php echo $country; ?></td>
                    </tr>
                    <tr>
                        <td class="t1">Current City</td>
                        <td class="t2"><?php echo $city; ?></td>
                    </tr>
                </table>
                 <table>
                     <tr><th><center>Summary</center></th></tr>
                     <?php foreach($final_result as $r): ?>
                     <?php
                     $text = str_split($r->header_value);
                     if($text[0]== 'a' || $text[0] =='A'):
                         ?>
                     <tr>
                         <td class="t1"><b><?php echo $r->header_value; ?></b></td>
                         <td class="t2"><?php echo $r->return_details; ?></td>
                     </tr>
                     <?php endif; ?>
                     <?php endforeach; ?>                    
                </table>
                <button class="btn btn-info view_details">View Details</button>
                
                 <table class="details_table">
                     <tr><th><center>Details</center></th></tr>
                     <?php foreach($final_result as $r): ?>
                         <tr>
                             <td class="t1"><?php echo $r->header_value; ?></td>
                             <td class="t2"><?php echo $r->return_details; ?></td>
                         </tr>
                     <?php endforeach; ?>                    
                </table>
            </div>
        </div>
    </div>
<style>
    table {
        width: 100%;
    }
    .view_details {
        margin: 25px 0 25px 47%;
    }
    .details_table {
        margin-bottom: 25px;
    }
    .t1 {
        width: 30%;
    }
    .t2 {
        width: 70%;
        color: cornflowerblue;
    }
    .details_table {
        display: none;
    }
    table  , tr , th {
        border: 1px solid green;
/*        border-collapse: separate !important;*/
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