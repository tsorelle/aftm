<?php
$form = Loader::helper('form');
defined('C5_EXECUTE') or die("Access Denied.");


if (!empty($response)) {
    ?>
	<div class="alert alert-info"><?php echo $response?></div>
<?php 
}
if (!empty($errormessage)) {
    ?>
    <div class="alert alert-danger"><?php echo $errormessage?></div>
<?php
}
?>

<?php
    if ($activepanel == 'memberform') {
?>
        <div class="row" style="margin-top: 20px">
            <div class="col-md-12">

        <form method="post" action="<?php echo $view->action('submit_member')?>">
    <!-- p><?php echo $message?></p -->
<fieldset >
    <legend>Join or renew your membership</legend>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="membership_type" class="control-label">Membership type</label>
                <?php echo $form->select('membership_type',$membertypes, $formData->membership_type)?>
            </div>

            <div class="form-group">
                <span style="padding-right: 20px"><?php echo $form->radio('new_or_renewal','new',$formData->new_or_renewal)?>New Membership</span>
                <span><?php echo $form->radio('new_or_renewal','renewal',$formData->new_or_renewal)?>Renewal</span>
            </div>

        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="payment_method" class="control-label">Payment method</label>
                <?php echo $form->select('payment_method',$payoptions, $formData->payment_method)?>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="" class="control-label">First name<span class="required-field">*</span></label>
                <?php echo $form->text('member_first_name',$formData->member_first_name)?>
            </div>

        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="member_last_name" class="control-label">Last name<span class="required-field">*</span></label>
                <?php echo $form->text('member_last_name',$formData->member_last_name)?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="member_address1" class="control-label">Address line 1<span class="required-field">*</span></label>
                <?php echo $form->text('member_address1',$formData->member_address1)?>
            </div>
            <div class="form-group">
                <label for="member_address2" class="control-label">Address line 2</label>
                <?php echo $form->text('member_address2',$formData->member_address2)?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="member_city" class="control-label">City<span class="required-field">*</span></label>
                <?php echo $form->text('member_city',$formData->member_city)?>
            </div>

        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="member_state" class="control-label">State/Province<span class="required-field">*</span></label>
                <?php echo $form->text('member_state',$formData->member_state)?>
            </div>

        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="" class="control-label">Postal Code<span class="required-field">*</span></label>
                <?php echo $form->text('member_zipcode',$formData->member_zipcode)?>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="member_email" class="control-label">Email address<span class="required-field">*</span></label>
                <?php echo $form->text('member_email',$formData->member_email)?>
            </div>
        </div>
    </div>

</fieldset>
<fieldset>
    <legend>For bands or dance groups</legend>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="member_band_name" class="control-label">Group name</label>
                <?php echo $form->text('member_band_name',$formData->member_band_name)?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="member_band_website" class="control-label">Web site</label>
                <?php echo $form->text('member_band_website',$formData->member_band_website)?>
            </div>

        </div>
    </div>

    </fieldset>
    <fieldset>

    <legend>Additional information</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="member_volunteer_interest" class="control-label">Volunteer Interests</label>
                <p><i>Would you be interested in helping with any of the following?</i></p>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <?php
                    echo $form->checkbox('vol_concerts'  ,1,$volunteer->concerts  ).'&nbsp;House Concerts<br>';
                    echo $form->checkbox('vol_newsletter',1,$volunteer->newsletter).'&nbsp;Newsletter<br>';
                    ?>
                </div>
                <div class="col-md-3">
                    <?php
                    echo $form->checkbox('vol_publicity' ,1,$volunteer->publicity ).'&nbsp;Publicity<br>';
                    echo $form->checkbox('vol_festivals' ,1,$volunteer->festivals ).'&nbsp;Festivals<br>';
                    ?>
                </div>
                <div class="col-md-3">
                    <?php
                    echo $form->checkbox('vol_membership',1,$volunteer->membership).'&nbsp;Membership<br>';
                    echo $form->checkbox('vol_mailings'  ,1,$volunteer->mailings  ).'&nbsp;Mailings<br>';
                    ?>
                </div>
                <div class="col-md-3">
                    <?php
                    echo $form->checkbox('vol_webpage'   ,1,$volunteer->webpage   ).'&nbsp;Webpage<br>';
                    ?>
                </div>
            </div>
        </div>


    </div>
        <div class="row" style="margin-top: 10px">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="" class="control-label">Ideas</label>
                    <p><i>Please share any ideas you might have about how to make AFTM a better organization.</i></p>
                    <?php echo $form->textarea('member_ideas',$formData->member_ideas)?>
                </div>
            </div>
        </div>


    </fieldset>
<?php if($showCaptcha) { ?>
            <fieldset>
                <legend>Help us fight spam</legend>
                <?php
                $captcha = Core::make('captcha');
                ?>
                <div class="form-group">
                    <label class="control-label"><?=$captcha->label()?></label>
                    <div><?php $captcha->display(); ?></div>
                    <div><?php $captcha->showInput(); ?></div>
                </div>

<?php } ?>
            </fieldset>

    <div class="form-group">
        <p>
            <span class="required-field">*</span> Indicates required information.
        </p>
        <input type="submit" name="submit" value="Submit your membership form" class="btn btn-default" />
    </div>
</form>
            </div>
        </div>

<?php }

if ($activepanel == 'checks') {
?>
        <p>Please mail your check or money order for <?php echo $totalCost ?> to:<br><br>Austin Friends of Traditional Music<br>P.O. Box 49608<br>Austin, TX 78765.<p>
        <p>Write 'membership fee: <?php echo $membershipType ?>' on the check memo and be sure that the name and email address you entered is written on the check or in an accompanying note. </p>
<?php }
if ($activepanel == 'paypal') {
    echo $paypalform;
}
?>




