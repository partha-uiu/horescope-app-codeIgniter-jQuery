<!--BEGIN INPUT TEXT FIELDS-->
<?php echo $this->theme->js('assets/js/jquery.imagepreview.js'); ?>
<script>
    jQuery(document).ready(function() {
        $('#content_icon').imagePreview({selector: '#image',
        });
    });
</script>
<script src="<?php echo site_url('assets/ckeditor/ckeditor.js', TRUE) ?>" type="text/javascript"></script>
<script src="<?php echo site_url('assets/ckfinder/ckfinder.js', TRUE) ?>" type="text/javascript"></script>
<?php
$this->ahrsession->set('ck_auth', true);
$this->ahrsession->set('ck_base_url', $this->sitesettings->asset_file_url . '/cms/');
?>
<script>
    jQuery(function() {
        var root_assets_url = '<?php echo site_url('assets'); ?>';
        var editor = CKEDITOR.replace('description',
                {
                    filebrowserBrowseUrl: root_assets_url + '/ckfinder/ckfinder.html?baseUrl=' + root_assets_url,
                    filebrowserImageBrowseUrl: root_assets_url + '/ckfinder/ckfinder.html?type=Images',
                    filebrowserFlashBrowseUrl: root_assets_url + '/ckfinder/ckfinder.html?type=Flash',
                    filebrowserUploadUrl:
                            root_assets_url + '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&currentFolder=/archive/',
                    filebrowserImageUploadUrl:
                            root_assets_url + '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&currentFolder=/cars/',
                    filebrowserFlashUploadUrl: root_assets_url + '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
                });
        CKFinder.setupCKEditor(editor, '/ckfinder/');
    });
</script>

<!--BEGIN INPUT TEXT FIELDS-->
<div class="row-fluid">
    <div class="span12">
        <div class="box for-form-horizontal">
            <header>
                <div class="icons"><i class="icon-plus-sign"></i></div>
                <h5>Add New Content</h5>
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
                <form action="" method="post" class="form-horizontal" id="inline-validate"  enctype="multipart/form-data">
                    <?php echo $this->ahrform->input(array('name' => 'id', 'type' => 'hidden', 'label' => false, 'template' => false)); ?>
                    <?php echo $this->ahrform->input(array('name' => 'title', 'type' => 'text', 'label' => 'Content Title', 'placeholder' => 'Title')); ?>
                    <?php echo $this->ahrform->input(array('name' => 'description', 'type' => 'textarea', 'label' => 'Description')); ?>

                    <div class="form-actions">
                        <button value="" class="btn btn-primary" name="data[form_acion]" type="submit"><i class="icon-save"></i> Save</button>
                        or <a href="<?php echo site_url('/' . CPREFIX . '/about/about_list'); ?>"><i class="icon-backward"></i> Back to list</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--END TEXT INPUT FIELD-->
<script>
    $('#endtime').click(function() {
        if (this.checked) {
            $("#enddiv").show("fade");
        }
        else
        {
            $("#enddiv").hide();
        }
    })
</script>
<script>
    jQuery(document).ready(function() {
//		var checkIfFieldVisible = function() {
//        return $("#endtime").is(':checked');
//		};
        /*----------- BEGIN validate CODE -------------------------*/
        var sdform = $('#inline-validate');
        sdform.validate({
            ignore: [], //hiden field validate
//            rules: {
//                title: {required: true, },
//                user_id: {required: true, },
//                // required: { depends: checkIfFieldVisible }
//                // }
//            },
            messages: {
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
