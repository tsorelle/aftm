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
if ($activepanel == 'mailform') {
    ?>
    <div class="row" style="margin-top: 20px">
        <div class="col-md-12">

            <form method="post" action="<?php echo $view->action('submit_message')?>">
                <!-- p><?php echo $message?></p -->
                <fieldset >
                    <legend>Contact <?php echo $formData->title; ?></legend>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="" class="control-label">Your name<span class="required-field">*</span></label>
                                <?php echo $form->text('from_name',$formData->from_name)?>
                            </div>

                            <div class="form-group">
                                <label for="" class="control-label">Your email address<span class="required-field">*</span></label>
                                <?php echo $form->text('from_address',$formData->from_address)?>
                            </div>

                            <div class="form-group">
                                <label for="" class="control-label">Subject<span class="required-field">*</span></label>
                                <?php echo $form->text('subject',$formData->subject)?>
                            </div>

                            <div class="form-group">
                                <label for="" class="control-label">Your message<span class="required-field">*</span></label>
                                <?php echo $form->textarea('message',$formData->message,['rows'=>'4'])?>
                            </div>

                            <?php echo $form->hidden('to_address',$formData->to_address) ?>

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
                    </fieldset>
                <?php } ?>

                <div class="form-group">
                    <p>
                        <span class="required-field">*</span> Indicates required information.
                    </p>
                    <input type="submit" name="submit" value="Submit your message" class="btn btn-default" />
                </div>
            </form>
        </div>
    </div>

<?php }

if ($activepanel != 'mailform') { ?>
    <h4>Your message has been sent</h4>
    <p><a href='/'>Return to home page</a></p>
<?php } ?>




