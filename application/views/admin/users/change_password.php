<!--BEGIN INPUT TEXT FIELDS-->
<div class="row-fluid">
    <div class="span12">
        <div class="box for-form-horizontal">
            <header>
                <div class="icons"><i class="icon-lock" style="font-size: 20px; color: #0066cc;"></i></div>
                <h5 style="font-size: 18px; font-weight: bold;">Change Password</h5>
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
                    $input = array_filter(array(
                        array('name' => 'password', 'type' => 'password'),
                        array('name' => 'confirm_password', 'type' => 'password'),
                    ));

                    echo $this->ahrform->input($input);
                    
                    $id = $this->ahrform->get('id');
                    $gid = $this->User->row($id)->group_id;
                    $uref = '';
                    if($gid == 1){
                        $uref = 'administrators';
                    }  else {
                        $uref = 'user_list';
                    }
                    ?>
                    <div class="form-actions">
                        <button value="" class="btn btn-primary" name="data[form_acion]" type="submit"><i class="icon-save"></i> Save</button>
                        or <a href="<?php echo site_url('/' . CPREFIX . '/users/'.$uref.'/'.$id); ?>"><i class="icon-backward"></i> Back</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<!--END TEXT INPUT FIELD-->


<script>
    jQuery(document).ready(function() {
        /*----------- BEGIN validate CODE -------------------------*/
       var sdform = $('#inline-validate');
        sdform.validate({
//            ignore: [], //hiden field validate
            rules: {
               password: {
                    required: true, minlength: 6
                },
                confirm_password: {
                    required: true, minlength: 6, equalTo: sdform.find('[name="password"]')
                },
            },
            messages: {
                email: {
                    remote: 'Email address already exist.'
                },
                username: {
                    remote: 'Username address already exist.'
                },
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
