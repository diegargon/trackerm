<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="css/trackerm-<?= $cfg['theme'] ?>.css?nocache=<?= time() ?>">
        <meta name="referrer" content="never">
        <title>trackerm</title>
    </head>

    <body>
        <div class="page">
            <?= $tdata['body'] ?>
            <footer>
                <?= $tdata['footer'] ?>
            </footer>
        </div>
    </body>

</html>