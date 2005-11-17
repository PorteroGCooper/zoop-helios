<?php
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Purpose:  Include all necessary javascript files to enable
htmlarea.
 * -------------------------------------------------------------
 */
function smarty_function_enable_htmlarea($params, &$smarty)
{
        if (!web_root) die ('global define "html_root" is not defined');

        echo '
<script>_editor_url = "resources/htmlarea/";</script>
<script type="text/javascript" src="resources/htmlarea/htmlarea.js"></script>
<script type="text/javascript" src="resources/htmlarea/lang/en.js"></script>
<script type="text/javascript" src="resources/htmlarea/dialog.js"></script>
<script type="text/javascript" src="resources/htmlarea/popupwin.js"></script>
<script type="text/javascript" src="resources/htmlarea/plugins/fwSpellChecker/spell-checker.js"></script>
<script type="text/javascript" src="resources/htmlarea/plugins/fwSpellChecker/lang/en.js"></script>
<style type="text/css">
@import url(resources/htmlarea/htmlarea.css);
</style>
';

}
?>
