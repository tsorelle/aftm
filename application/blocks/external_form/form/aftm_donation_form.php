<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/11/2017
 * Time: 5:59 AM
 */
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
if ($activepanel == 'donationform') {
    ?>
    <div class="row" style="margin-top: 20px">
        <div class="col-md-12">

            <form method="post" action="<?php echo $view->action('submit_donation')?>">
                <!-- p><?php echo $message?></p -->
                <fieldset >
                    <legend>Submit Your Donation</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="" class="control-label">Amount of donation<span class="required-field">*</span></label>
                                <?php echo $form->text('donation_amount',$formData->donation_amount)?>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="" class="control-label">First name<span class="required-field">*</span></label>
                                <?php echo $form->text('donor_first_name',$formData->donor_first_name)?>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="donor_last_name" class="control-label">Last name<span class="required-field">*</span></label>
                                <?php echo $form->text('donor_last_name',$formData->donor_last_name)?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="donor_address1" class="control-label">Address line 1<span class="required-field">*</span></label>
                                <?php echo $form->text('donor_address1',$formData->donor_address1)?>
                            </div>
                            <div class="form-group">
                                <label for="donor_address2" class="control-label">Address line 2</label>
                                <?php echo $form->text('donor_address2',$formData->donor_address2)?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="donor_city" class="control-label">City<span class="required-field">*</span></label>
                                <?php echo $form->text('donor_city',$formData->donor_city)?>
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="donor_state" class="control-label">State/Province<span class="required-field">*</span></label>
                                <?php echo $form->text('donor_state',$formData->donor_state)?>
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Postal Code<span class="required-field">*</span></label>
                                <?php echo $form->text('donor_zipcode',$formData->donor_zipcode)?>
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="donor_email" class="control-label">Email address<span class="required-field">*</span></label>
                                <?php echo $form->text('donor_email',$formData->donor_email)?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="donor_email" class="control-label">Phone number<span class="required-field">*</span></label>
                                <?php echo $form->text('donor_email',$formData->donor_phone)?>
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

if ($activepanel == 'thanks') {
    ?>
    <p>Thank you for your donation.</p>
<?php }
if ($activepanel == 'paypal') {
    echo $paypalform;
}
?>




