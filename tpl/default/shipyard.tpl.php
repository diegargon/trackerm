<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="column_container">
    <div class="column">
        <h2></h2>
        <form id="shipyard_build" method="POST">
            <div><?= $L['L_SHIP_NAME'] ?>: <input type="text" name="ship_name" value="<?= $tdata['ship_name'] ?>"/> </div>
            <div><?= $L['L_BRIDGE'] ?>:<?= $tdata['bridge_sel'] ?> </div>
            <div><?= $L['L_CREW'] ?>:<?= $tdata['crew_sel'] ?></div>
            <div><?= $L['L_CARGO'] ?>:<?= $tdata['cargo_sel'] ?></div>
            <div><?= $L['L_MISSILE_STORAGE'] ?>:<?= $tdata['missile_storage_sel'] ?></div>
            <div><?= $L['L_FRONT_GUNS'] ?>:<?= $tdata['front_guns_sel'] ?></div>
            <div><?= $L['L_TURRETS'] ?>:<?= $tdata['turrets_sel'] ?></div>
            <div><?= $L['L_PROPELLER'] ?>:<?= $tdata['propeller_sel'] ?></div>
            <div><?= $L['L_GENERATOR'] ?>:<?= $tdata['generator_sel'] ?></div>
            <div><?= $L['L_ACCUMULATOR'] ?>:<?= $tdata['accumulator_sel'] ?></div>
            <div><?= $L['L_SHIELDS'] ?>:<?= $tdata['shields_sel'] ?></div>
            <div><?= $L['L_RADAR'] ?>:<?= $tdata['radar_sel'] ?></div>
            <input type="hidden" name="planet_id" value="<?= $tdata['planet_id'] ?>"/>
            <input type="submit" name="calculate_ship" value="<?= $L['L_PREVIEW'] ?>"/>
            <input type="submit" name="ship_builder_submit" value="<?= $L['L_BUILD'] ?>"/>
        </form>
    </div>
    <div class="column">
        <h2><?= $L['L_DESCRIPTION'] ?></h2>
        <p><?= $tdata['descriptions'] ?></p>
    </div>
</div>