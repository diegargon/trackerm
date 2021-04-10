<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<h3><?= $L['L_MINING'] ?></h3>
<form id="mining_form" method="post" action="">
    <input type="hidden" name="planet_id" value="<?= $tdata['id'] ?>"/>
    <?php if (!empty($tdata['titanium'])) { ?>
        <label for="titanium_assign"><?= $L['L_TITANIUM'] ?></label>
        <input id="titanium_assign" type="text" name="titanium_assign" size="4" value="<?= $tdata['titanium_workers'] ?>"/>
        <?php
    }
    if (!empty($tdata['lithium'])) {
        ?>
        <label for="lithium_assign"><?= $L['L_LITHIUM'] ?></label>
        <input id="lithium_assign" type="text" size="4"  name="lithium_assign" value="<?= $tdata['lithium_workers'] ?>"/>
        <?php
    }
    if (!empty($tdata['armatita'])) {
        ?>
        <label for="armatita_assign"><?= $L['L_ARMATITA'] ?></label>
        <input id="armatita_assign" type="text" size="4" name="armatita_assign" value="<?= $tdata['armatita_workers'] ?>"/>
        <?php
    }
    ?>
    <input type="submit" name="mining_submit" value="<?= $L['L_ASSIGN'] ?>" />
</form>