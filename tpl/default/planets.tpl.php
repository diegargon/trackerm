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
<!-- </div> -->