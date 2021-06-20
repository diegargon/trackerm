<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="footer">
    <p class="center">Querys(<?= $tdata['num_querys'] ?>)</p>
    <p class="copyright">
        <a href="https://github.com/diegargon/trackerm" target="_blank">TrackerM</a> - Copyright @ 2020 - 2021
    </p>
    <?php
    if (!empty($tdata['querys'])) {
        foreach ($tdata['querys'] as $query) {
            ?>
            <p class="center"><?= $query['query'] ?></p>
            <?php if (isset($query['bind'])) { ?>
                <p class="center"><?= $query['bind'] ?></p>
                <?php
            }
        }
    }
    ?>
</div>