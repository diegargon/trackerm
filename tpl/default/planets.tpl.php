<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<!-- <div class="vips_ships"> -->

<div class="planet_page">
    <div class="planet_col1">
        <?= $tdata['planet_head_data'] ?>
        <?php if (!empty($tdata['status_msg'])) { ?>
            <fieldset>
                <legend><?= $L['L_STATUS_MSG'] ?></legend>
                <?= $tdata['status_msg'] ?>
            </fieldset>
        <?php } ?>
        <fieldset>
            <legend><?= $L['L_SUMMARY'] ?></legend>
            <?= $tdata['planet_brief_data'] ?>
            <?= $tdata['planet_engineers_data'] ?>
            <?= !empty($tdata['build_shipyard']) ? $tdata['build_shipyard'] : null; ?>
        </fieldset>
        <fieldset>
            <legend><?= $L['L_MINING'] ?></legend>
            <?= $tdata['mining_data'] ?>
        </fieldset>
        <div class="planet_list_container">
            <fieldset>
                <legend><?= $L['L_SUMMARY'] ?></legend>
                <?php if (!empty($tdata['vips_data'])) { ?>
                    <div class="vips_cont">
                        <h3><?= $L['L_VIPS'] ?></h3>
                        <form id="planet_characters_form" method="POST">
                            <?= $tdata['vips_data'] ?>
                        </form>
                    </div>
                <?php } ?>

                <?php if (!empty($tdata['ships_data'])) { ?>
                    <div class="ships_cont">
                        <h3><?= $L['L_SHIPS'] ?></h3>
                        <form id="planet_ships_form" method="POST">
                            <?= $tdata['ships_data'] ?>
                        </form>
                    </div>
                <?php } ?>
            </fieldset>
        </div>
    </div>
    <div class="planet_col2">
        <?= !empty($tdata['planet_shipyard_data']) ? $tdata['planet_shipyard_data'] : null ?>
    </div>
</div>