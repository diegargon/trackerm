<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="vips_ships">
    <div class="vips_cont">
        <h3><?= $L['L_VIPS'] ?></h3>
        <form id="planet_characters_form" method="POST">
            <?= $tdata['vip_data'] ?>
        </form>
    </div>
    <div class="ships_cont">
        <h3><?= $L['L_SHIPS'] ?></h3>
        <form id="planet_ships_form" method="POST">
            <?= $tdata['ships_data'] ?>
        </form>
    </div>
</div>