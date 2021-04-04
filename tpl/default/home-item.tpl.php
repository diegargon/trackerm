<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="home_item_container <?= isset($tdata['main_class']) ? $tdata['main_class'] : null ?>">
    <div class="home_item_title"><h2><?= $tdata['title'] ?></h2></div>
    <div class="home_item">
        <?= $tdata['content'] ?>
    </div>
</div>

