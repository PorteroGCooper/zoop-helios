###############################################
1. Upload the captcha folder to your main Cutenews directory

###############################################
2. Open inc/shows.inc.php and find this:

    $time = time()+($config_date_adjust*60);

    //----------------------------------
    // Add The Comment ... Go Go GO!
    //----------------------------------

add above:

if (!PhpCaptcha::Validate($_POST['code'])) {
echo("<div style=\"text-align: center;\">Please enter the numbers from the image.<br /><a href=\"javascript:history.go(-1)\">go back</a></div>");
$CN_HALT = TRUE;
     break 1;
}

################################################
3. And find this:

        </noscript>".insertSmilies('short', FALSE);

add below:

$template_form = str_replace("{captcha}","<br />Enter this code<br /><img src=\"".$config_http_script_dir."/captcha/captcha.php?width=144\" width=\"144\" alt=\"Security Image\"/><br /><input type=\"text\" size=\"10\" name=\"code\" maxlength=\"6\" /><br />",$template_form); 

################################################
4. Open the file which shows your news and add the following code at the beginning of the file above everything else:

<?php
require('./captcha/php-captcha.inc.php'); //Change the path to your needs
?>

################################################
5. Go to Options -> Edit Templates -> Add comment form and put {captcha} where you want to show the Security Image.