<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="wanted_page">
    <div class="wanted_list">
        <h2><?= $tdata['L_WANTED'] ?></h2>
        <div class="wanted_opt_global">
            <span class="global_title"><?= $tdata['L_GLOBAL_QUALITY_TAGS'] ?></span>
            <?php
            if (!empty($tdata['torrent_quality_prefs'])) {
                foreach ($tdata['torrent_quality_prefs'] as $quality) {
                    ?>
                    <span class="tag_quality"><?= $quality ?></span>
                    <?php
                }
            }
            ?>

            <span class="global_title"><?= $tdata['L_GLOBAL_TAGS'] ?></span>
            <?php
            if (!empty($tdata['torrent_ignore_prefs'])) {
                foreach ($tdata['torrent_ignore_prefs'] as $ignores) {
                    ?>
                    <span class="tag_ignore"><?= $ignores ?></span>
                    <?php
                }
            }
            if (!empty($tdata['torrent_require_prefs'])) {
                foreach ($tdata['torrent_require_prefs'] as $require) {
                    ?>
                    <span class="tag_require"><?= $require ?></span>
                    <?php
                }
            }

            if (!empty($tdata['torrent_require_or_prefs'])) {
                ?>
                <span class="global_title"><?= $tdata['L_ANY_TAG'] ?></span>
                <?php
                foreach ($tdata['torrent_require_or_prefs'] as $or_require) {
                    ?>
                    <span class="tag_require"><?= $or_require ?></span>
                    <?php
                }
            }
            ?>
        </div>
        <div class="wanted_list_container">
            <div class="divTableWanted">
                <div class="divTableHeadingWanted">
                    <div class="divTableCellWanted"></div>
                    <!-- <div class="divTableCellWanted">ID</div> -->
                    <div class="divTableCellWanted"><?= $tdata['L_STATUS'] ?></div>
                    <div class="divTableCellWanted"><?= $tdata['L_CHECKDAY'] ?></div>
                    <div class="divTableCellWanted"><?= $tdata['L_ADDED'] ?></div>
                    <div class="divTableCellWanted"><?= $tdata['L_CHECKED'] ?></div>
                    <div class="divTableCellWanted"><?= $tdata['L_TYPE'] ?></div>
                    <div class="divTableCellWanted"><?= $tdata['L_ONLY_PROPER'] ?></div>
                    <div class="divTableCellWanted"><?= $tdata['L_IGNORE'] ?></div>
                    <div class="divTableCellWanted"><?= $tdata['L_REQUIRE'] ?></div>
                    <div class="divTableCellWanted">TMDB</div>
                    <div class="divTableCellWanted"><?= $tdata['L_TITLE'] ?></div>
                </div>
                <?= isset($tdata['wanted_list']) ? $tdata['wanted_list'] : null ?>
            </div>
        </div>
    </div>
</div>
<div class="wanted_help">
    <div><?= $tdata['L_HELP_PROPER'] ?></div>
    <div><?= $tdata['L_HELP_IGNORE_TAGS'] ?></div>
    <div><?= $tdata['L_HELP_REQUIRE_TAGS'] ?></div>
</div>