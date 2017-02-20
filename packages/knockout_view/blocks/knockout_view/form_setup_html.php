<?php defined('C5_EXECUTE') or die("Access Denied."); ?>


<div id="ccm-block-knockout-value-viewmodel">
    <label for="ccm-block-knockout-value-viewmodel-input">View Model
    <input id="ccm-block-knockout-value-viewmodel-input" class="form-control" value="<?php  echo $viewmodel ?>" name="viewmodel" />
    </label>
</div>

<div class="checkbox" id="ccm-block-knockout-value-addwrapper">
    <label for="ccm-block-knockout-value-addwrapper-input">
        <input type="checkbox" id="ccm-block-knockout-value-addwrapper-input" value="1" name="addwrapper"
               <?php echo $addwrapper ? 'checked' : '' ?> />
        Add Wrapper Markup
    </label>
</div>

<div style="margin-top: 10px"><b>Content</b></div>
    <div id="ccm-block-html-value"><?php echo htmlspecialchars($content,ENT_QUOTES,APP_CHARSET) ?></div>
    <textarea style="display: none" id="ccm-block-html-value-textarea" name="content"></textarea>

<style type="text/css">

    #ccm-block-html-value {
        width: 100%;
        border: 1px solid #eee;
        height: 490px;
    }
</style>

<script type="text/javascript">
    $(function() {
        var editor = ace.edit("ccm-block-html-value");
        editor.setTheme("ace/theme/eclipse");
        editor.getSession().setMode("ace/mode/html");
        refreshTextarea(editor.getValue());
        editor.getSession().on('change', function() {
            refreshTextarea(editor.getValue());
        });
    });

    function refreshTextarea(contents) {
        $('#ccm-block-html-value-textarea').val(contents);
    }
</script>
