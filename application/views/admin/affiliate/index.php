<style>
    .controls input[type="checkbox"] {
        margin-top: -2px !important;
    }
</style>
<?php //echo empty($index).' '; echo empty($item); //if((isset($item) && !empty($item)) && (isset($id) && empty($id))) { ?>
<div class="row-fluid" <?php if(!empty($index) || empty($item)){ echo 'hidden'; } ?>>
    <div class="span12">
        <div class="box">
            <header>
                <div class="icons"><i class="icon-cog" style="font-size: 20px; color: #0066cc;"></i></div>
                <h5 style="display: inline-block; font-size: 18px; font-weight: bold;">Settings</h5>
            </header>
            <div id="collapse4" class="body">
                <table id="dataTable" class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                        <tr>
                            <!-- <th>Referral Cookie Expire</th> -->
                            <th>Maximum Commission</th>
                            <th>Enable pay commission on every pledge</th>
                            <th>Enable pay commission on every project listing</th>
                            <th>Minimum Withdrawal Amount</th>
                            <th>Application Fee (%)</th>
                            <th width="105">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($item)) { ?>
                            <tr>
                                <!-- <td><?php echo $item->referral_cookie_expire;?></td> -->
                                <td><?php echo $item->maximum_commission;?></td>
                                <td><?php if($item->enable_pay_commission_on_every_pledge == 0){echo 'NO';}else{ 
                                    echo 'YES';}?>
                                </td>
                                <td><?php if($item->enable_pay_commission_on_every_project_listing == 0){echo 'NO';}else{ 
                                    echo 'YES';}?>
                                </td>
                                <td><?php echo $item->minimum_withdrawal_amount; ?></td>
                                <td><?php echo $item->transaction_fee; ?></td>
                                <td class="action">
                                    <a href="<?php echo site_url(CPREFIX . "/affiliate/index/" . $item->id) ?>"><i class="icon-pencil"></i> Edit</a>&nbsp;&nbsp;
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row-fluid <?php if(!empty($item) && empty($index)){ echo 'hidden'; } ?>">
    <div class="span12">
        <div class="box for-form-horizontal">
            <header>
                <div class="icons" style="font-size: 20px; color: #0066cc;"><i class="icon-edit-sign"></i></div>
                <h5 style="font-size: 18px; font-weight: bold;"><?php echo ($this->ahrform->get('id') == "" ) ? "Add New" : "Edit"; ?> Settings </h5>
                <div class="toolbar">
                    <ul class="nav">
                        <li>
                            <div class="btn-group">
                                <a class="accordion-toggle btn btn-mini minimize-box" data-toggle="collapse"
                                   href="#collapse3">
                                    <i class="icon-chevron-up"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </header>

            <div id="collapse3" class="accordion-body collapse in body">
                <?php
                $template = '<div class="control-group">
                        {label}
                        <div class="controls span9">{input}</div>
                        {error}
                        </div>';
                echo $this->ahrform->set_template($template);
                echo $this->ahrform->set_input_default(array('label' => array('class' => 'control-label span3'), 'input' => array('class' => 'input-xlarge')));
                ?>
                <form action="" method="post" class="form-horizontal" id="inline-validate" enctype="multipart/form-data">
                    <?php echo $this->ahrform->input(array('name' => 'id', 'type' => 'hidden', 'label' => false, 'template' => false)); ?>

                    <?php echo $this->ahrform->input(array('name' => 'referral_cookie_expire', 'type' => 'text', 'label' => 'Referral Cookie Expire', 'placeholder' => 'Enter Referral Cookie Expire')); ?>
                    <?php echo $this->ahrform->input(array('name' => 'maximum_commission', 'type' => 'text', 'label' => 'Maximum Commission', 'placeholder' => 'Enter maximum commission')); ?>
                    
                    <div class="control-group">
                        <label class="control-label span3" for=""></label>
                        <div class="controls span9">
                            <input id="" class="input-xxlarge" type="checkbox" name="enable_pay_commission_on_every_pledge" value="1" <?php if($this->ahrform->get('enable_pay_commission_on_every_pledge') == 1){ echo 'checked';} ?> />
                            <span>Enable pay commission on every pledge</span>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label span3" for=""></label>
                        <div class="controls span9">
                            <input id="" class="input-xxlarge" type="checkbox" name="enable_pay_commission_on_every_project_listing" value="1" <?php if($this->ahrform->get('enable_pay_commission_on_every_project_listing') == 1){ echo 'checked';} ?> />
                            <span>Enable pay commission on every project listing</span>
                        </div>
                    </div>
                    
                    <?php echo $this->ahrform->input(array('name' => 'minimum_withdrawal_amount', 'type' => 'text', 'label' => 'Minimum withdrawal amount', 'placeholder' => 'Enter Minimum withdrawal amount')); ?>
                    <?php echo $this->ahrform->input(array('name' => 'transaction_fee', 'type' => 'text', 'label' => 'Application fee (%)', 'placeholder' => 'Enter Transaction fee')); ?>                                    
                    
                    <?php
//                    echo $this->ahrform->input(array(
//                        'name' => 'transaction_fee_type',
//                        'type' => 'select',
//                        'id' => 'publisher',
//                        //'empty' => '',
//                        'label' => 'Transaction fee type',
//                        'options' => array('1'=>'Integer', '0'=>'Float'),
//                        'style' => 'width: 100px;'
//                    ));
                    ?>
                    
                    <div class="control-group">
                        <label class="control-label span3" for=""></label>
                        <div class="controls span9">
                            <button value="" class="btn btn-primary" name="data[form_acion]" type="submit"><i class="icon-save"></i> Save</button>
                            or <a href="<?php echo site_url('/' . CPREFIX . '/affiliate'); ?>"><i class="icon-backward"></i> Back to list</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>