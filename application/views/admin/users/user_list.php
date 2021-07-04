<!--Begin Datatables-->
<div class="row-fluid <?php if(!empty($index) || empty($items)){ echo 'hidden'; } ?>">
    <div class="span12">
        <div class="box">
            <header>
                <div class="icons"><i class="icon-user" style="font-size: 20px; color: #0066cc;"></i></div>
                <h5 style="font-size: 18px; font-weight: bold;">All Users</h5>
            </header>
            <div id="collapse4" class="body">
                <table id="dataTable" class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="width: 70px;" class="center">
                        <div class="btn-group data-list-control" style=" ">
                            <a class="btn"><input class="check_all" type="checkbox" /></a>
                            <button class="btn dropdown-toggle" data-toggle="dropdown"> &nbsp;<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a class="bulk-mail send_bulk_mail" href="javascript:void(0);" data-toggle="modal" data-target="#myModal"><i class="icon-ok"></i> Send Mail</a></li>
                                <!--<li><a class="bulk-inactive" href="javascript:void(0);"><i class="icon-ban-circle"></i> Inactive</a></li>
                                <li><a class="bulk-edit" href="javascript:void(0);"><i class="icon-pencil"></i> Edit</a></li>-->
                                <li><a class="bulk-delete" href="javascript:void(0);"><i class="icon-trash"></i> Delete</a></li>
                            </ul>
                        </div>
                    </th>
                    <th>SI</th>
                    <th>Name</th>
                    <th>Campaigns</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Campaigns</th>
                    <th>Donations</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th width="105">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $ind => $item) { //pr($item->id); ?>
                            <tr>
                                <td class="center"><input class="check list-control-check" type="checkbox" value="<?php echo $item->id ?>" /></td>
                                <td><?php echo $ind+1; //$item->id; ?></td>
                                <td><?php echo $item->fname.' '.$item->lname; ?></td>
                                <td><?php $camp = $this->Campaign->row(array('conditions' => array('user_id' => $item->id))); 
                                    if(!empty($camp->title)){echo $camp->title;}else{echo 'No Campaign!';} ?></td>
                                <td><?php echo $item->email; ?></td>
                                <td><?php echo ''; ?></td>
                                <td><?php echo $item->campaign_count; ?></td>
                                <td><?php echo $item->donation_count; ?></td>
                                <td><?php echo ''; ?></td>
                                <td><?php if($item->is_active == 1){echo 'Active';}else{echo 'Inactive';} ?></td>
                                
                                <td><?php $d = strtotime($item->created); echo date("d",$d).' '.date("M",$d).' '.date("Y",$d); ?></td>
                                <td class="action">
                                    <a href="<?php echo site_url(CPREFIX . "/users/user_list/" . $item->id) ?>"><i class="icon-pencil"></i>&nbsp;Edit</a>
                                    <a class="bulk-delete single-delete"><i class="icon-trash"></i>&nbsp;Delete</a><br>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <style>
        .box{border: 0px solid #D6D6D6;
        }
        .span6{border: 1px solid #D6D6D6;
            box-shadow: 0px 1px 3px rgba(100, 100, 100, 0.1);}
    </style>
    <div class="span12" style="margin-left:0;">
        <div class="box">
            <header class="span6">
                <div class="icons"><i class="icon-ok" style="font-size: 20px; color: #009900;"></i></div>
                <h5 style="font-size: 15px; font-weight: bold; display: inline-block; float: left;">Total Active Users</h5>
                <h5 style="font-size: 15px; font-weight: bold; display: inline-block; float: right; margin-right: 20px;"><?php if(isset($totalActiveUsers)){ echo count($totalActiveUsers);}else{echo 0;} ?></h5>
            </header>
            <header class="span6">
                <div class="icons"><i class="icon-info" style="font-size: 20px; color: #999999;"></i></div>
                <h5 style="font-size: 15px; font-weight: bold; display: inline-block;">Total Inactive Users</h5>
                <h5 style="font-size: 15px; font-weight: bold; display: inline-block; float: right; margin-right: 20px;"><?php if(isset($totalInctiveUsers)){ echo count($totalInctiveUsers);}else{echo 0;} ?></h5>
            </header>
        </div>
    </div>
    <div class="span12" style="margin-left:0; margin-top: 10px; margin-bottom: 20px;">
        <div class="box">
            <header class="span6">
                <div class="icons"><i class="icon-facebook" style="font-size: 20px; color: #46629E;"></i></div>
                <h5 style="font-size: 15px; font-weight: bold; display: inline-block;">Total Faceboook Users</h5>
                <h5 style="font-size: 15px; font-weight: bold; display: inline-block; float: right; margin-right: 20px;"><?php if(isset($totalFacebookUsers)){ echo count($totalFacebookUsers);}else{echo 0;} ?></h5>
            </header>
            <header class="span6">
                <div class="icons"><i class="icon-envelope" style="font-size: 20px; color: #1e87ef;"></i></div>
                <h5 style="font-size: 15px; font-weight: bold; display: inline-block;">Total Email Users</h5>
                <h5 style="font-size: 15px; font-weight: bold; display: inline-block; float: right; margin-right: 20px;"><?php if(isset($totalEmailUsers)){ echo count($totalEmailUsers) - count($totalFacebookUsers);}else{echo 0;} ?></h5>
            </header>
            <!--<div class="span2"> <a href="<?php //echo site_url(CPREFIX . '/dashboard'); ?>" class="quick-action"> <span class="icon-facebook"></span> Dashboard </a> </div>-->
        </div>
    </div>
</div>


<!--End Datatables-->
<script>
    $(document).ready(function() {
        $('.send_mail').on('click',function() {
            $('#user-id').val($(this).attr('data-user-id'));
            var id = $('#user-id').val();
              $.ajax({
                type: "POST",
                url: '<?php echo site_url("ccadmin/user/get_name"); ?>',
                dataType: 'json',
                data: jQuery.param({id: id}),
                    success: function(res) {
                        //alert('res');
                        $('#username').val(res.firstname+' '+res.lastname);
                        $('#user_email').val(res.email);
                        console.log(res);			
                    }
                });
        });
    });
</script>

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
        /*----------- BEGIN datatable CODE -------------------------*/
        dTable = $('#dataTable').dataTable({
            //"sDom": "<'pull-right'l>t<'row-fluid datatable-bottombar-add-top-marrgin'<'span6'f><'span8 text-center'p<'data-table-info-right'i>>>",
            "sDom": "<'pull-right'f>t<'row-fluid datatable-bottombar-add-top-marrgin'<'span4'l><'span4 data-table-info-right'i><'span4'p>>",
            "sPaginationType": "bootstrap",
            "aaSorting": [[ 1, "asc" ]],
            // Disable sorting on the first column
            "aoColumnDefs": [{
                    'bSortable': true,
                    'aTargets': [3, ($('#dataTable thead tr').children().length - 1)]
                }],
            
            "oLanguage": {
                "sLengthMenu": "Show _MENU_ entries"
            }
            
        });
        
        /*----------- END datatable CODE -------------------------*/

        // Start Bulk Mail Send
        $('#dataTable .bulk-mail').each(function() {
            $(this).click(function() {
                var len = $(this).parents('table').find('tbody .check:checked').length;
                if (!len) {
                    alert('Please select at lest 1 item');
                    return;
                }
                if (len && !confirm('Are you sure want to active?')) {
                    return;
                }

                var ids = new Array();
                $(this).parents('table').find('tbody .check:checked').each(function() {
                    ids.push($(this).val());
                    
                });
                
                var multiple_id = '';
                $.each(ids, function(index, value) {
                    multiple_id = multiple_id + value + ',';                  
                });
                $('#user-id').val(multiple_id);
            });
        });
        
       
        $('.send_bulk_mail').on('click',function() {
            var id = $('#user-id').val();
            alert(id);
              $.ajax({
                type: "POST",
                url: '<?php echo site_url("ccadmin/user/get_bulk_name"); ?>',
                dataType: 'json',
                data: jQuery.param({id: id}),
                    success: function(res) {
                        //alert('res');
                        $('#username').val(res.name);
                        $('#user_email').val(res.email);
                        console.log(res);			
                    }
                });
        });
        
        // End Bulk Mail Send
        
        $('#dataTable .bulk-inactive').each(function() {
            $(this).click(function() {
                var len = $(this).parents('table').find('tbody .check:checked').length;
                if (!len) {
                    alert('Please select at lest 1 item');
                    return;
                }
                if (len && !confirm('Are you sure want to inactive?')) {
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
                    url: '<?php echo site_url(CPREFIX . "/users/change_status/inactive") ?>',
                    dataType: 'json',
                    context: $(this).parents('table'),
                    data: jQuery.param({id: ids})
                }).done(function(rt) {

                    var $this = $(this);
                    $.each(ids, function(index, value) {
                        rowIndex = $this.find('input[value="' + value + '"]').parents('tr:first').index();
                        dTable.fnUpdate('Inactive', rowIndex, 3); // Single cell
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
//
//                    setTimeout(function() {
//                        window.location = window.location;
//                    }, 800);
                });
            });
        });


        $('#dataTable .bulk-edit').each(function() {
            $(this).click(function() {
                var len = $(this).parents('table').find('tbody .check:checked').length;
                if (!len) {
                    alert('Please select an item');
                    return;
                }
                if (len && len > 1) {
                    alert("You have't right edit multiple item at a time.");
                    return;
                }
                var id = $(this).parents('table').find('tbody .check:checked').eq(0).val();
                window.location = '<?php echo site_url(CPREFIX . "/users/add") ?>' + "/" + id;

            });
        });



        $('body').on('click', '#dataTable .bulk-delete', function(e) {
                e.preventDefault();
                if ($(this).hasClass('single-delete')) {
                    $(this).parents('table').find('tbody .check:checked').prop('checked', false);
                    $(this).closest('tr').find('.check').prop('checked', true);
                }


                var len = $(this).parents('table').find('tbody .check:checked').length;
                if (!len) {
                    alert('Please select at lest 1 item');
                    return;
                }
                if (len && !confirm('Are you sure want to delete?')) {
                    return;
                }

                var ids = new Array();
                $(this).parents('table').find('tbody .check:checked').each(function() {
                    ids.push($(this).val());                  
                });

                $.ajax({
                    // Uncomment the following to send cross-domain cookies:
                    //xhrFields: {withCredentials: true},
                    type: "POST",
                    url: '<?php echo site_url(CPREFIX . "/users/delete") ?>',
                    dataType: 'json',
                    context: $(this).parents('table'),
                    data: jQuery.param({id: ids})
                    
                }).done(function(rt) {
                    var $this = $(this);
                    $.each(ids, function(index, value) {
                        dTable.fnDeleteRow($this.find('input[value="' + value + '"]').parents('tr:first').index());
                        $this.find('input[value="' + value + '"]').parents('tr:first').remove();
                    });
                    //alert('OK');
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
                        window.location = window.location ;
                    },800);

                });
        });


    });

</script>


<div class="row-fluid <?php if(!empty($items) && empty($index)){ echo 'hidden'; } ?>">
    <div class="span12">
        <div class="box for-form-horizontal">
            <header>
                <div class="icons"><i class="icon-plus-sign" style="font-size: 20px; color: #0066cc;"></i></div>
                <h5 style="font-size: 18px; font-weight: bold;">Edit User</h5>
                <div class="toolbar">
                    <ul class="nav">
                        <li>
                            <div class="btn-group">
                                <a class="accordion-toggle btn btn-mini minimize-box" data-toggle="collapse"
                                   href="#collapse3">
                                    <i class="icon-chevron-up"></i>
                                </a>
                                <!--<button class="btn btn-mini btn-danger close-box"><i class="icon-remove"></i></button>-->
                            </div>
                        </li>
                    </ul>
                </div>
            </header>           
            <div id="collapse3" class="accordion-body collapse in body">
                <div><?php echo (isset($error)) ? $error : ""; ?></div>
                <div><?php echo (isset($success)) ? $success : ""; ?></div>               
                <?php
                $template = '<div class="control-group">
                        {label}
                        <div class="controls">{input}</div>
                        {error}
                        </div>';
                echo $this->ahrform->set_template($template);
                echo $this->ahrform->set_input_default(array('label' => array('class' => 'control-label'), 'input' => array('class' => 'input-xlarge')));
                ?>
                <form action="" method="post" class="form-horizontal" id="inline-validate">
                    <?php echo $this->ahrform->input(array('name' => 'id', 'type' => 'hidden', 'label' => false, 'template' => false)); ?>
                    <?php
//                    echo $this->ahrform->input(array(
//                        'name' => 'group_id',
//                        'type' => 'select',
//                        'empty' => 'Select Group Name',
//                        'label' => 'User Groups',
//                        'options' => empty($Groups) ? array() : $Groups
//                    ));
                    ?>
                    <?php echo $this->ahrform->input(array('name' => 'fname', 'type' => 'text', 'label' => 'First Name')); ?>
                    <?php echo $this->ahrform->input(array('name' => 'lname', 'type' => 'text', 'label' => 'Last Name')); ?>
                    <?php //echo $this->ahrform->input(array('name' => 'username', 'type' => 'text', 'label' => 'Username')); ?>
                    <?php echo $this->ahrform->input(array('name' => 'email', 'type' => 'text', 'label' => 'Email')); ?>
                    <?php
                    if (!$this->ahrform->get('id')) {
                        echo $this->ahrform->input(array('name' => 'password', 'type' => 'password', 'label' => 'Password'));
                    }
                    ?>
<!--                    <div class="control-group">
                        <?php echo $this->ahrform->label(array('text' => 'Activate?', 'for' => 'activate')); ?>
                        <div class="controls">
                            <input type="hidden" value="<?php echo $this->ahrform->get('status'); ?>" id="isactive"  class="input-xlarge" name="status">
                            <div data-toggle="buttons-radio" class="btn-group">
                                <?php foreach (array('1' => 'Yes', '0' => 'No') as $groupID => $groupName) { ?>
                                    <button  data-equalto_default="<?php echo $this->ahrform->get('status'); ?>" data-equalto="#isactive" type="button" value="<?php echo $groupID ?>" class="btn btn-info "><?php echo $groupName ?></button>
                                <?php } ?>
                            </div>
                            <span class="help-inline" for="isactive"></span>
                        </div>
                    </div>-->
                    
                    <?php
                            //echo $this->ahrsession->get('Admin').'  12';
                    echo $this->ahrform->input(array(
                        'name' => 'is_active',
                        'type' => 'select',
                        'id' => 'is_active',
                        //'empty' => '',
                        'label' => 'Activate?',
                        'options' => array('1'=>'Yes', '0'=>'No'),
                        'style' => 'width: 60px;'
                    ));
                    ?>

                    <?php if ($id = $this->ahrform->get('id')) { ?>
                        <div class="control-group">
                            <?php echo $this->ahrform->label('Change Password'); ?>
                            <div class="controls">
                                <a href="<?php echo site_url(CPREFIX . "/users/change_password/$id") ?>"><i class="icon-lock"></i> Change Password</a>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-actions">
                        <button value="" class="btn btn-primary" name="data[form_acion]" type="submit" id="btn"><i class="icon-save"></i> Save</button>
                        or <a href="<?php echo site_url('/' . CPREFIX . '/users/user_list'); ?>"><i class="icon-backward"></i> Back to list</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        /*----------- BEGIN validate CODE -------------------------*/
        var sdform = $('#inline-validate');
        sdform.validate({
            ignore: [], //hiden field validate
            rules: {
                username: {
                    required: true,
                    <?php if ($id != $this->ahrform->get('id')) { ?>
                    remote: {
                        url: '<?php echo site_url(CPREFIX . "/user/check_username") ?>',
                        type: "post",
                        data: {
                            requestby: 'jquery_validator',
                            id: function() {
                                return sdform.find('[name="id"]').val();
                            },
                            username: function() {
                                return sdform.find('[name="username"]').val();
                            }
                        }
                    }
                    <?php } ?>
                },
                email: {
                    required: true,
                    email: true	
                },
                password: {
                    required: true, 
                    minlength: 6
                }	
            },
            messages: {
                username: {
                    remote: 'Username address already exist.'
                }
            },
            errorClass: 'help-inline',
            errorElement: 'span',
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('success').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error').addClass('success');
            }
        });
    });
</script>



