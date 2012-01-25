<?php
// Main functionality lib
include_once 'index.func.php';

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Minecraft Resource Mirror</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <link rel="stylesheet" type="text/css" href="common/css">
        <link rel="stylesheet" type="text/css" href="index.css">

        <script src="common/js"></script>
        <script src="style/index.js"></script>
    </head>

    <body onload="_init();">
    <div class="full-screen hidden" id="window_drophelper">
        <h1>Drop anywhere to upload files</h1>
    </div>

    <header>
        <div class="fill">Minecraft Resource Mirror/Storage Box</div>
    </header>

    <article>

    <?php foreach ($_MESSAGES as $msg) { ?>
    <div class="canvas">
        <?php echo $msg; ?>
    </div>

    <hr />
    <?php } ?>

    <form class="canvas" id="form_upload" method="POST" action="./" enctype="multipart/form-data">
        <h1 class="button" id="button_upload">
            Click here OR drag files here to upload<br />
            <small>MAX 32MB, ZIP / RAR / 7Z ONLY</small>
        </h1>

        <div id="rules" class="hidden">
            <h3 class="error hidden center" id="rules_cookieerror">Warning: Cookies are disabled. These terms will therefore always appear for every upload you perform. Other features may also be non-functional.</h3>
            <h3 class="center">This complimentary storage service is provided by The Major under the following advice and conditions:</h3>

            <ul style="text-align: left; width: 600px; margin: 0 auto;">
                <li>You may <b>ONLY</b> store archive files of type <b>ZIP / RAR / 7Z</b> of up to <b>32MB</b> per archive.</li>
                <li>Files must be self-contained Minecraft mods, texture packs, resource packs or tools for use with the Minecraft client or server.</li>
                <li>Files can <b>NOT</b> be <b>linked</b> through an advertising gateway such as <b>Adf.ly or Adcraft.co</b>. Any uploads found violating this condition will be made to auto-redirect to <a href="http://adfly.simplaza.net">Deadfly</a>.</li>
                <li>Files reported to contain stolen content will only be <b>suspended</b> after a <b>thorough investigation</b>.</li>
                <li>Whilst SimPlaza.net has a stable uptime, you should use this service as a secondary mirror or better alternative to unfavorable services like MediaFire and MegaUpload. There is no guarantee that uploaded files will be accessible 24/7/365.</li>
            </ul>

            <h1 class="button" id="button_acceptrules">I agree to this</h1>
        </div>

        <input type="file" name="filepicker[]" id="filepicker" />
    </form>
    </article>
    <footer class="center">
        Website and code all licensed under <a href="http://simplaza.net/hax/SIMPL/" target="_blank">SIMPL</a> 2011 by Major Rasputin<br />
        Follow <a href="http://twitter.com/Gnu32" target="_blank">@Gnu32</a> for updates to this service
    </footer>

    </body>
</html>
