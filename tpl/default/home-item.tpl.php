<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata)
?>
<div class="home_item_container <?= isset($tdata['main_class']) ? $tdata['main_class'] : null ?>">
    <div class="home_item_title"><h2><?= $tdata['title'] ?></h2></div>
    <div class="home_item">
        <?= !empty($tdata['status_msg']) ? $tdata['status_msg'] : null ?>
        <?= !empty($tdata['content']) ? $tdata['content'] : null ?>
    </div>
</div>

