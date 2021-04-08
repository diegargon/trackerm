<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<fieldset>
    <legend><?= $L['L_REPORT'] ?></legend>
    <div class="report_container">
        <?php
        if (!empty($tdata['name'])) {
            echo '<div>' . $L['L_NAME'] . ': ' . $tdata['name'] . '</div>';
        }
        if (!empty($tdata['titanium'])) {
            echo '<div>' . $L['L_TITANIUM'] . ': ' . $tdata['titanium'] . '</div>';
        }
        if (!empty($tdata['lithium'])) {
            echo '<div>' . $L['L_LITHIUM'] . ': ' . $tdata['lithium'] . '</div>';
        }
        if (!empty($tdata['armatita'])) {
            echo '<div>' . $L['L_ARMATITA'] . ': ' . $tdata['armatita'] . '(' . $tdata['armatita_purity'] . ')</div>';
        }
        if (!empty($tdata['have_port'])) {
            echo '<div>' . $L['L_PLANET_HAVE_PORT'] . '</div>';
        }
        ?>
    </div>
</fieldset>
