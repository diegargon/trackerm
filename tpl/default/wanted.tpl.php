<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<div class="wanted_page">
    <div class="wanted_list">
        <h2><?= $tdata['L_WANTED'] ?></h2>
        <div class="wanted_list_container">
            <?= isset($tdata['wanted_list']) ? $tdata['wanted_list'] : null ?>
        </div>
    </div>
</div>