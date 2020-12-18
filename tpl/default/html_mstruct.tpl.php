<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="css/trackerm-<?= $cfg['theme'] ?>.css?nocache=<?= time() ?>">
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
            <?= $tdata['body'] ?>
            <footer>
                <?= $tdata['footer'] ?>
            </footer>
        </div>
    </body>

</html>