<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<!DOCTYPE html>
<html lang="<?= substr($cfg['LANG'], 0, 2) ?>">
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=<?= $tdata['charset'] ?>" />
        <link rel="stylesheet" href="css/trackerm-<?= $cfg['css'] ?>.css?nocache=<?= time() ?>">
        <link href="data:image/x-icon;base64,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" rel="icon" type="image/x-icon" />
        <meta name="referrer" content="never">
        <title>trackerm</title>
        <script>
            window.onload = function () {
                document.getElementById("loading_wrap").style.display = "none";
            };
            function show_loading() {
                document.getElementById("loading_wrap").style.display = "block";
            }
        </script>
        <!--
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous">
        </script>
        -->
    </head>
    <body>
        <div id="loading_wrap" class="loading"></div>
        <div class="page">
            <?php if (!empty($tdata['general_warn_msg'])) { ?>
                <div class="red_warning"><?= $tdata['general_warn_msg'] ?></div>
            <?php } ?>
            <?= $tdata['menu'] ?>
            <?= $tdata['body'] ?>
            <footer>
                <?= $tdata['footer'] ?>
            </footer>
        </div>
    </body>
</html>