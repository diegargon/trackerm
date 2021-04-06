<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="port_page">
    <p><?= !empty($tdata['status_msg']) ? $tdata['status_msg'] : null ?></p>
    <?php if (!empty($tdata['planet_shipyard'])) { ?>
        <?= $tdata['planet_shipyard'] ?>
    <?php } ?>
</div>
