<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 *
 */
?>

<div class="ships_page_container">
    <p><?= !empty($tdata['status_msg']) ? $tdata['status_msg'] : null ?></p>
    <div class="col1">
        <p><?= !empty($tdata['sel_ship']) ? $tdata['sel_ship'] : null ?></p>
        <h2><?= $L['L_SHIP'] ?>: <?= $tdata['name'] ?></h2>
        <div><?= $L['L_STATUS'] ?>: <?= $tdata['ship_status'] ?></div>
        <br/>
        <div>
            <div class="vertical_range_container"><label for="energy_range"><?= $L['L_ENERGY_MIN'] ?>:<?= $tdata['energy'] ?></label>
                <input disabled id="energy_range" class="ship_range_info" type="range" orient="vertical" name="energy" min="0" max="<?= $tdata['energy_max'] ?>" value="<?= $tdata['energy'] ?>"/>
            </div>
            <div class="vertical_range_container"><label for="speed_range"><?= $L['L_SPEED_MIN'] ?>:<?= $tdata['speed'] ?></label>
                <input disabled id="speed_range" class="ship_range_info" type="range" orient="vertical" name="speed" step="0.1" min="0" max="1" value="<?= $tdata['speed'] ?>"/>
            </div>
            <div class="pos_wrap"><?= $L['L_POSITION'] ?> X: <span class="pos_cord"><?= $tdata['x'] ?></span>  Y: <span class="pos_cord"><?= $tdata['y'] ?></span> Z:<span class="pos_cord"> <?= $tdata['z'] ?></span></div>

        </div>
        <div>
            <h2><?= $L['L_CONTROLS'] ?></h2>

            <form id="general_controls" method="post">
                <input type="submit" name="ship_set_speed" value="<?= $L['L_SET_SPEED'] ?>" />
                <input type="hidden" name="ship_id" value="<?= $tdata['id'] ?>" />
                <input type="hidden" name="planet_id" value="<?= $tdata['planet_id'] ?>" />
                <input type="number" name="setspeed" size="2" step="0.1" min="0" max="<?= $tdata['max_speed'] ?>" value="<?= $tdata['speed'] ?>"/>
                <br/>
                <?php if ($tdata['in_shipyard']) { ?>
                    <input type="submit" name="ship_shipyard_disconnect" value="<?= $L['L_SHIPYARD_DISCONNECT'] ?>" />
                <?php } else if (!empty($tdata['have_shipyard']) && !empty($tdata['can_connect'])) { ?>
                    <input type="submit" name="ship_shipyard_connect" value="<?= $L['L_SHIPYARD_CONNECT'] ?>" />
                <?php } ?>

            </form>

            <?php if (!empty($tdata['ship_conn_sel'])) { ?>
                <form id="ship_conn_form" method="post">
                    <input type="hidden" name="ship_id" value="<?= $tdata['id'] ?>" />
                    <?= $tdata['ship_conn_sel'] ?>
                </form>
            <?php } ?>

            <form id="destination_controls" method="post">
                <input type="text" name="dest_x" size="5" value="<?= $tdata['dx'] ?>" />
                <input type="text" name="dest_y" size="5" value="<?= $tdata['dy'] ?>" />
                <input type="text" name="dest_z" size="5" value="<?= $tdata['dz'] ?>" />
                <input type="hidden" name="planet_id" value="<?= $tdata['planet_id'] ?>" />
                <input type="hidden" name="ship_id" value="<?= $tdata['id'] ?>" />
                <input type="submit" name="ship_set_destination" value="<?= $L['L_SET_DESTINATION'] ?>" />
            </form>
            <?php if (!empty($tdata['alien_planet_sector']) && $tdata['radar']) { ?>
                <div class="scan_planet_container">
                    <form id="scan_planet_form" method="post">
                        <input type="hidden" name="ship_id" value="<?= $tdata['id'] ?>" />
                        <input type="hidden" name="alien_planet_id" value="<?= $tdata['alien_planet_sector'] ?>" />
                        <input type="submit" name="scan_planet" value="<?= $L['L_SCAN_PLANET'] ?>" />
                    </form>
                    <?= !empty($tdata['scan_planet_report']) ? '<span>' . $tdata['scan_planet_report'] . '</span>' : null; ?>
                </div>
            <?php } ?>
            <?php if ($tdata['can_cargo']) { ?>
                <p><?= $L['L_CARGO_LOAD'] . ':' . $tdata['actual_cargo'] ?></p>
                <form id="cargo_form" method="post">
                    <?= $tdata['cargo_sel'] ?>
                    <input type="text" name="cargo_units" size="5" value="<?= $tdata['cargo_units'] ?>" />
                    <input type="hidden" name="planet_id" value="<?= $tdata['planet_id'] ?>" />
                    <input type="hidden" name="ship_id" value="<?= $tdata['id'] ?>" />
                    <input type="hidden" name="cargo_link" value="<?= $tdata['cargo_link'] ?>" />
                    <input type="submit" name="load_units" value="<?= $L['L_CARGO_LOAD'] ?>" />
                    <input type="submit" name="unload_units" value="<?= $L['L_CARGO_UNLOAD'] ?>" />
                </form>
            <?php } ?>
        </div>

        <div>
            <h2><?= $L['L_VIPS'] ?></h2>
            <form name="vips" method="post">
                <?php if (!empty($tdata['add_vips_sel'])) { ?>
                    <div><span><?= $L['L_VIPS_ADD'] ?></span>
                        <?= $tdata['add_vips_sel'] ?>
                        <input type="hidden" name="ship_id" value="<?= $tdata['id'] ?>" />
                        <input type="hidden" name="planet_id" value="<?= $tdata['planet_id'] ?>" />
                        <input type="submit" name="add_vip" value="<?= $L['L_ADD'] ?>"/>
                    </div>
                <?php } ?>
                <?php if (!empty($tdata['del_vips_sel'])) { ?>
                    <div><span><?= $L['L_VIPS_DEL'] ?></span>
                        <?php if (empty($planet_id)) {
                            ?><p><?= $L['L_WARNING_DEL_VIP'] ?></p><?php
                        }
                        ?>
                        <?= $tdata['del_vips_sel'] ?>
                        <input type="hidden" name="ship_id" value="<?= $tdata['id'] ?>" />
                        <input type="hidden" name="planet_id" value="<?= $tdata['planet_id'] ?>" />
                        <input type="submit" name="del_vip" value="<?= $L['L_DEL'] ?>"/>
                    </div>
                <?php } ?>
            </form>
        </div>

    </div> <!-- col1 -->
    <div class="col2">
        <div id="canvas_container" >
            <div id="canvas">
            </div>
            <div id="canvas_inset"></div>
            <script src="js/TrackBallControls.js"></script>
            <script src="js/ship-radar.js"></script>
        </div>
        <?= (!empty($tdata['ship_specs'])) ? $tdata['ship_specs'] : null; ?>
    </div> <!-- col -->
</div>
