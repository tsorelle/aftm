<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php if ($addwrapper) { ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
<?php }
    $c = Page::getCurrentPage();
    if ($c->isEditMode()) {
        echo '<div style="border: 1px double black; padding: 50px"><b>Knockout View disabled in edit mode.</b></div>';
    }
    else {
        echo "<div id='service-messages-container'><service-messages></service-messages></div>";
        echo "<div id='$viewcontainerid'>\n$content\n</div>";
    }

    if ($addwrapper) {
                ?>
        </div>
    </div>
</div>
<?php } ?>


