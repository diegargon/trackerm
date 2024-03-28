<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="footer">
    <p class="center">Querys[D<?= $tdata['num_querys'] ?>][J<?= $cfg['remote_querys_jackett'] ?>][T<?= $cfg['remote_querys_tmdb'] ?>]</p>
    <p class="copyright">
        <a href="https://github.com/diegargon/trackerm" target="_blank">TrackerM <?= $cfg['version'] . ' DB v' . $cfg['db_version'] ?></a> - Copyright @ 2020 - 2024
    </p>
    <?php
    if (!empty($tdata['querys']) && !empty($user->isAdmin())) {
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
