
<style>


    .thclass  {



        border: 1px solid #000000;
    }
    td {
        border: 1px solid blue;
text-align: center;
    }
</style>

<script>

    $('.too-long').popover()


    $(document).ready(function() {
        // Check all checkboxes in table
        $('.check_all').click(function() {
            var pt = $(this).parents('table');
            var ch = pt.find('tbody .check');
            if ($(this).is(':checked')) {
                ch.each(function() {
                    $(this).attr('checked', true);
                });
            } else {
                ch.each(function() {
                    $(this).attr('checked', false);
                });
            }
        });
    });
</script>

<script>
    var dTable = null;
    $(document).ready(function() {
        /*---------- BEGIN datatable CODE -------------------------*/
        dTable = $('#dataTable').dataTable({
            "sDom": "<'pull-right'f>t<'row-fluid datatable-bottombar-add-top-marrgin'<'span4'l><'span4 data-table-info-right'i><'span4'p>>",
            "sPaginationType": "bootstrap",
            "aaSorting": [[ 4, "asc" ]],
            // Disable sorting on the first column
            "aoColumnDefs": [{
                'bSortable': false,
                'aTargets': [0, ($('#dataTable thead tr').children().length - 1)]
            }],
            "oLanguage": {
                "sLengthMenu": "Show _MENU_ entries"
            }
        });
        /*----------- END datatable CODE -------------------------*/

        /*----------- bulk Delete -------------------------*/
        $('#dataTable .bulk-delete').each(function() {
            $(this).click(function(e) {
                e.preventDefault();
                var len = $(this).parents('table').find('tbody .check:checked').length;
                if (!len) {
                    alert('Please select at least 1 item');
                    return;
                }
                if (len && !confirm('Are you sure want to delete?')) {
                    return;
                }

                var ids = new Array();
                $(this).parents('table').find('tbody .check:checked').each(function() {
                    ids.push($(this).val());
                    ;
                });

                $.ajax({
                    // Uncomment the following to send cross-domain cookies:
                    //xhrFields: {withCredentials: true},
                    type: "POST",
                    url: '<?php echo site_url(CPREFIX . "/traits/delete_male") ?>',
                    dataType: 'json',
                    context: $(this).parents('table'),
                    data: jQuery.param({id: ids})
                }).done(function(rt) {
                    var $this = $(this);
                    $.each(ids, function(index, value) {
                        dTable.fnDeleteRow($this.find('input[value="' + value + '"]').parents('tr:first').index());
                        $this.find('input[value="' + value + '"]').parents('tr:first').remove();
                    });

                    $.pnotify.defaults.styling = "bootstrap";
                    var stack_bottomright = {"dir1": "down", "dir2": "left", "push": "bottom", "firstpos1": 25, "firstpos2": 25};
                    var opts = {
                        //title: "Over Here",
                        text: rt.msg,
                        addclass: "stack-topright",
                        stack: stack_bottomright,
                        type: rt.status === true ? "success" : 'error'
                    };
                    $.pnotify(opts);
                    setTimeout(function(){
                        window.location = window.location;
                    },800);

                });
            });
        });

        /*----------- single Delete -------------------------*/
        $('body').on('click','#dataTable .single-delete',function(e) {
            e.preventDefault();
            $(this).parents('table').find('tbody .check:checked').prop('checked', false);
            $(this).closest('tr').find('.check').prop('checked', true);

            var len = $(this).parents('table').find('tbody .check:checked').length;
            if (!len) {
                alert('Please select at least 1 item');
                return;
            }
            if (len && !confirm('Are you sure want to delete?')) {
                return;
            }

            var ids = new Array();
            $(this).parents('table').find('tbody .check:checked').each(function() {
                ids.push($(this).val());
                ;
            });

            $.ajax({
                // Uncomment the following to send cross-domain cookies:
                //xhrFields: {withCredentials: true},
                type: "POST",
                url: '<?php echo site_url(CPREFIX . "/traits/delete_male") ?>',
                dataType: 'json',
                context: $(this).parents('table'),
                data: jQuery.param({id: ids})
            }).done(function(rt) {
                var $this = $(this);
                $.each(ids, function(index, value) {
                    dTable.fnDeleteRow($this.find('input[value="' + value + '"]').parents('tr:first').index());
                    $this.find('input[value="' + value + '"]').parents('tr:first').remove();
                });

                $.pnotify.defaults.styling = "bootstrap";
                var stack_bottomright = {"dir1": "down", "dir2": "left", "push": "bottom", "firstpos1": 25, "firstpos2": 25};
                var opts = {
                    //title: "Over Here",
                    text: rt.msg,
                    addclass: "stack-topright",
                    stack: stack_bottomright,
                    type: rt.status === true ? "success" : 'error'
                };
                $.pnotify(opts);
                setTimeout(function(){
                    window.location = window.location;
                },800);

            });
        });
    });
</script>

<!--Add Category-->
<?php echo $this->theme->js('assets/js/jquery.imagepreview.js'); ?>
<script>
    jQuery(document).ready(function() {
        $('#image').imagePreview({ selector : '#bannerpic'
        });
    });

    jQuery(document).ready(function() {
        $("#name").on('keyup keypress change', function(e) {
            if ($("#slug").attr('data-changed') != 'changed') {
                var value = $(this).val().toString();
                $("#slug").val(value.toSlug());
            }
        });
    });
</script>










<div class="row-fluid list_show <?php if(!empty($index) || empty($items)){ echo 'hidden'; } ?>">
    <div class="span12">
        <div class="box">
            <header>
                <div class="icons"><i class="icon-tasks" style="font-size: 20px; color: #0066cc;"></i></div>
                <h5 style="display: inline-block; font-size: 18px; font-weight: bold;">Search Birthday</h5>
            </header><br/><br/>
            <div id="collapse4" class="body">


                <h3>Select your birthday value :</h3><br/>

                <form method="post" action="">


                    Month:<select name="month">
                        <option selected="selected">
                            Select Month
                        </option>

                        <?php
                        for ($i=1;$i<=12;$i++) {
                            echo "<option value=$i>$i</option>";
                        }
                        ?>
                    </select>










               Day: <select name="day"> <option selected="selected">
                        Select Day
                    </option>
                    <?php
for ($i=1;$i<=31;$i++) {
    echo "<option value=$i>$i</option>";
    }
?>

                </select>




                Year:<select name="year">
                    <option selected="selected">
                        Select Year
                    </option>
                    <?php
                    for ($i=1900;$i<=2015;$i++) {
                        echo "<option value=$i>$i</option>";
                    }
                    ?>
                </select>














                                <input type="submit" value="Search" class="btn btn-primary click_show" style="margin: 10px 20px;">
      </form>
            </div>
        </div>
    </div>










<br/>
    <?php
if (isset($_POST['month'])&& isset($_POST['day'])&& isset($_POST['year'])) {
    $month = $_POST["month"];
    $day = $_POST["day"];
    $year = $_POST["year"];


    $birthday = $month . "/" . $day . "/" . $year;



    $get_birthday_value = $this->db->query("SELECT * from hr_birthday where val_1='$birthday'")->row();
}

    ?>
   <table  style="border: 1px solid black">
       <?php
       if(isset($get_birthday_value)) {
           ?>

       <tr >
           <th class="thclass">val_1</th>    <th class="thclass">val_2</th>   <th class="thclass">val_3</th>   <th class="thclass">val_4</th><th class="thclass">val_5</th> <th class="thclass">val_6</th><th class="thclass">val_7</th><th class="thclass">val_8</th><th class="thclass">val_9</th><th class="thclass">val_10</th><th class="thclass">val_11</th><th class="thclass">val_12</th><th class="thclass">val_13</th>
           <th class="thclass">

               val_14</th><th class="thclass">val_15</th><th class="thclass">val_16</th><th class="thclass">val_17</th><th class="thclass">val_18</th><th class="thclass">val_19</th><th class="thclass">val_20</th><th class="thclass">val_21</th>

       </tr>


<tr>
    <td><?php echo $get_birthday_value->val_1; ?> </td>

    <td><?php echo $get_birthday_value->val_2; ?></td>
    <td><?php echo $get_birthday_value->val_3; ?></td>
    <td><?php echo $get_birthday_value->val_4; ?></td>
    <td><?php echo $get_birthday_value->val_5; ?></td>
    <td><?php echo $get_birthday_value->val_6; ?></td>
    <td><?php echo $get_birthday_value->val_7; ?></td>
    <td><?php echo $get_birthday_value->val_8; ?></td>
    <td><?php echo $get_birthday_value->val_9; ?></td>
    <td><?php echo $get_birthday_value->val_10; ?></td>
    <td><?php echo $get_birthday_value->val_11; ?></td>
    <td><?php echo $get_birthday_value->val_12; ?></td>
    <td><?php echo $get_birthday_value->val_13; ?></td>
    <td><?php echo $get_birthday_value->val_14; ?></td>
    <td><?php echo $get_birthday_value->val_15; ?></td>
    <td><?php echo $get_birthday_value->val_16; ?></td>
    <td><?php echo $get_birthday_value->val_17; ?></td>
    <td><?php echo $get_birthday_value->val_18; ?></td>
    <td><?php echo $get_birthday_value->val_19; ?></td>
    <td><?php echo $get_birthday_value->val_20; ?></td>
    <td><?php echo $get_birthday_value->val_21; ?></td>



    </tr>
       <tr ><th class="thclass">val_22</th>
           <th class="thclass">val_23</th>
           <th class="thclass">val_24</th>
           <th class="thclass">val_25</th>
           <th class="thclass">val_26</th>
           <th class="thclass">val_27</th>
           <th class="thclass">val_28</th>
           <th class="thclass">val_29</th>
           <th class="thclass">val_30</th>
           <th class="thclass">val_31</th>
           <th class="thclass">val_32</th>
           <th class="thclass">val_33</th></tr>

       <tr>
           <td><?php echo $get_birthday_value->val_22; ?></td>
           <td><?php echo $get_birthday_value->val_23; ?></td>
           <td><?php echo $get_birthday_value->val_24; ?></td>
           <td><?php echo $get_birthday_value->val_25; ?></td>
            <td><?php echo $get_birthday_value->val_26; ?></td>
            <td><?php echo $get_birthday_value->val_27; ?></td>
             <td><?php echo $get_birthday_value->val_28; ?></td>
            <td><?php echo $get_birthday_value->val_29; ?></td>
             <td><?php echo $get_birthday_value->val_30; ?></td>
             <td><?php echo $get_birthday_value->val_31; ?></td>
          <td><?php echo $get_birthday_value->val_32; ?></td>
             <td><?php echo $get_birthday_value->val_33; ?></td>
</tr>


<?php } ?>



   </table>













</div>