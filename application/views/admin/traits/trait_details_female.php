<script>
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
                    url: '<?php echo site_url(CPREFIX . "/traits/delete_female") ?>',
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
                url: '<?php echo site_url(CPREFIX . "/traits/delete_female") ?>',
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
                <h5 style="display: inline-block; font-size: 18px; font-weight: bold;">Female Trait Details</h5>
            </header>
            <div id="collapse4" class="body">
                <table id="dataTable" class="table table-bordered table-condensed table-hover table-striped">
<!--                    <thead>
                    <tr>
                        <th style="width: 70px;" class="center">
                            <div class="btn-group data-list-control" style=" ">
                                <a class="btn"><input class="check_all" type="checkbox" /></a>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"> &nbsp;<span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li><a class="bulk-delete" href="javascript:void(0);"><i class="icon-trash"></i> Delete</a></li>
                                </ul>
                            </div>
                        </th>
                        <th>Header</th>
                        <th>Trait id </th>
                        <th> Birthday Value</th>
                        <th> Details</th>


                        <th>Parent</th>

                        <th width="105">Action</th>
                    </tr>
                    </thead>-->
                    <tbody>
                        <tr>
                                <td>1</td>
                                <?php foreach ($items as $item) { ?>                           
                                <td>
                                    <?php echo $item['trait_id']?><br>
                                    <a href="<?php echo site_url('admin/traits/trait_edit').'/'.$item['id'];?>">Edit</a>
                                </td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td></td>
                                <?php foreach ($items as $item) { ?>  
                                <td><?php echo $item['header_value']?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>1</td>                              
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val1[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>2</td>
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val2[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>3</td>
                               <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val3[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>4</td>
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val4[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>5</td>
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val5[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>6</td>
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val6[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>7</td>
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val7[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>8</td>
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val8[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>9</td>
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val9[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>10</td>
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val10[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>11</td>
                               <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php                               
                                    echo $val11[$i][0]['return_details'];?></td>
                                <?php } ?>
                        </tr>
                        <tr>
                                <td>0</td>
                                <?php for($i=0;$i<=25;$i++) { ?>  
                                <td><?php   if($val12[$i]==0)                            
                                    {echo 'No Data';}
                                   else {echo $val12[$i][0]['return_details'];}?></td>
                                <?php } ?>
                        </tr>
                    </tbody>
                </table>
                <!--                <button class="btn btn-primary click_show" style="margin: 10px 20px;"><i class="icon-plus"></i> <b>Add</b></button>-->
            </div>
        </div>
    </div>
</div>