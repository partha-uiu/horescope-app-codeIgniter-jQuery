

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
                    url: '<?php echo site_url(CPREFIX . "/campaign/delete_campaign_categories") ?>',
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
                url: '<?php echo site_url(CPREFIX . "/products/delete_product") ?>',
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

<div class="row-fluid add_show <?php if(!empty($items) && empty($index)){ echo 'hidden'; } ?>">
    <div class="span12">
        <div class="box for-form-horizontal">
            <header>
                <div class="icons"><i class="icon-plus-sign" style="font-size: 20px; color: #0066cc;"></i></div>
                <h5 style="display: inline-block; font-size: 18px; font-weight: bold;">Update Female Trait</h5>
                <div class="toolbar">
                    <ul class="nav">
                        <li>
                            <div class="btn-group">
                                <a class="accordion-toggle btn minimize-box" data-toggle="collapse" href="#collapse3"><i class="icon-chevron-up"></i></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </header>

            <div id="collapse3" class="accordion-body collapse in body">
                <?php
                $template = '<div class="control-group">
                        {label}
                        <div class="controls">{input}</div>                        
                        </div>';
                echo $this->ahrform->set_template($template);
                echo $this->ahrform->set_input_default(array('label' => array('class' => 'control-label'), 'input' => array('class' => 'input-xlarge')));
                ?>
                <form action="" method="post" class="form-horizontal" id="inline-validate" enctype="multipart/form-data">
                    <?php echo $this->ahrform->input(array('name' => 'id', 'type' => 'hidden', 'label' => false, 'template' => false)); ?>
                    <?php echo $this->ahrform->input(array('name' => 'header_value', 'type' => 'text', 'label' => 'Trait Header')); ?>
                    <?php // echo $this->ahrform->input(array('name' => 'p_summary', 'type' => 'text', 'label' => 'Product Summary')); ?>
                    <?php echo $this->ahrform->input(array('name' => 'trait_id', 'type' => 'textarea', 'label' => 'Trait id')); ?>

                    <!--                    <div class="control-group">
                        <label class="control-label" for="maingraphic">Green waste</label>
                        <div class="controls">
                            
                                
                            <input class="input-xlarge" name="green_waste" type="radio" value="1" id="greenwaste"  <?php // if(!$this->ahrform->get('id'))
                    //                                    echo 'checked';
                    //                            else if(isset($_POST['greenwaste']) == '1')  echo ' checked="checked"';?>>Yes
                            <input class="input-xlarge" name="green_waste" type="radio" value="0" id="greenwaste" //<?php // if(isset($_POST['green_waste']) == '0')  echo ' checked="checked"';?>>No
                        
                            //<?php //  $this->ahrform->get('green_waste'); ?>  
                                
                               
                            
                        </div>
                    </div>-->

                    <!--                   <div class="control-group">
                        <label class="control-label" for="maingraphic">General Waste</label>
                        <div class="controls">
                            
                                
                            <input class="input-xlarge" name="generalwaste" type="radio" value="1"  id="generalwaste"<?php
                    //                            if(!$this->ahrform->get('id'))
                    //                                    echo 'checked';
                    //                            else if(isset($_POST['generalwaste']) == '1')
                    //                                echo ' checked="checked"';?>>Yes
                            <input class="input-xlarge" name="generalwaste" type="radio" value="0" id="generalwaste" <?php // if(isset($_POST['generalwaste']) == '0')  echo ' checked="checked"';?>>No
                        
                            <?php //  $this->ahrform->get('generalwaste') ?>  
                                
                               
                            
                        </div>
                    </div>  -->











                    <!--                       <div class="control-group">
                        <label class="control-label" for="maingraphic">Clean Concrete and Bricks</label>
                        <div class="controls">
                            
                                
                            <input class="input-xlarge" name="concrete" type="radio" value="1"   <?php // if(!$this->ahrform->get('id'))
                    //                                    echo 'checked';
                    //                            else  if(isset($_POST['concrete']) == '1')  echo ' checked="checked"';?>>Yes
                            <input class="input-xlarge" name="concrete" type="radio" value="0" <?php // if(isset($_POST['concrete']) == '0')  echo ' checked="checked"';?>>No
                        
                            <?php //  $this->ahrform->get('concrete') ?>  
                                
                               
                            
                        </div>
                    </div>  -->

                    <!--                         <div class="control-group">
                        <label class="control-label" for="maingraphic">Mixed</label>
                        <div class="controls">
                            
                                
                            <input class="input-xlarge" name="mixed" type="radio" value="1" checked  <?php // if(isset($_POST['mixed']) == 'Yes')  echo 'checked="checked"';?>>Yes
                            <input class="input-xlarge" name="mixed" type="radio" value="0" <?php // if(isset($_POST['mixed']) == 'No')  echo 'checked="checked"';?>>No
                        
                            <?php //  $this->ahrform->get('mixed') ?>  
                                
                               
                            
                        </div>
                    </div>-->






                    <?php
                    //                    echo $this->ahrform->input(array(
                    //                        'name' => 'is_active',
                    //                        'type' => 'select',
                    //                        'id' => 'is_active',
                    //                        //'empty' => '',
                    //                        'label' => 'Published?',
                    //                        'options' => array('1'=>'Yes', '0'=>'No'),
                    //                        'style' => 'width: 60px;'
                    //                    ));
                    //                    ?>
                    <?php // echo $this->ahrform->input(array('name' => 'sort_order', 'type' => 'text', 'label' => 'Sort Order', 'style'=>'max-width: 80px')); ?>
                    <div class="form-actions">
                        <button value="" class="btn btn-primary" name="data[form_acion]" type="submit"><i class="icon-save"></i> Save</button>
                        or <a href="<?php echo site_url('/' . CPREFIX . '/traits/show_female') ?>"><i class="icon-backward"></i> Back to list</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!--END TEXT INPUT FIELD-->
<script>
    $(document).on('click', '.click_show', function(e) {
        e.preventDefault();
        $('.list_show').hide();
        $('.add_show').removeClass('hidden');
        $('.add_show').show();
    });

    jQuery(document).ready(function() {
        /*----------- BEGIN validate CODE -------------------------*/
        var sdform = $('#inline-validate');
        sdform.validate({
            ignore: "", //validate hidden field
            rules: {
                product_title: {
                    required: true
                },
                tyre_removal: {
                    required: true
                },
                mattress_removal: {
                    required: true
                },
                lpg_gas_bottle: {
                    required: true
                },
                tv_monitors: {
                    required: true
                },
                extra_day_hire: {
                    required: true
                },
                product_quantity: {
                    required: true
                },
                product_price: {
                    required: true
                },

                <?php if($this->ahrform->get('id')==null) { ?>
                image: {
                    required: true
                },
                <?php } ?>
//                sort_order: {
//                    required: true
//                }
                sort_order: {
                    required: true,
                    <?php if($this->ahrform->get('id')==null) { ?>
                    remote: {
                        url: '<?php echo site_url(CPREFIX . "/products/products_list"); ?>',
                        type: "post",
                        data: {
                            requestby: 'jquery_validator',
                            id: function() {
                                return sdform.find('[name="id"]').val();
                            },
                            sort_order: function() {
                                return sdform.find('[name="product_list"]').val();
                            }
                        }
                    }
                    <?php } ?>
                }
            },
            messages: {
                sort_order: {
                    remote: 'This product is  already exist!!'
                }
            },
            errorClass: 'help-inline',
            errorElement: 'span',
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('success').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');//.addClass('success');
            }
        });
    });
</script>