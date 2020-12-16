<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<div class="wanted_page">
    <div class="wanted_list">
        <h2><?= $tdata['L_WANTED'] ?></h2>
        <div class="wanted_opt_global">
            <div class="global_title"><?= $tdata['L_GLOBAL_TAGS'] ?></div>
            <?php
            foreach ($cfg['TORRENT_QUALITYS_PREFS'] as $quality) {
                ?>
                <span class="tag_quality"><?= $quality ?></span>
                <?php
            }
            foreach ($cfg['TORRENT_IGNORES_PREFS'] as $ignores) {
                ?>
                <span class="tag_ignore"><?= $ignores ?></span>
            <?php } ?>
        </div>
        <div class="wanted_list_container">
            <div class="divTableWanted">
                <div class="divTableHeadingWanted">
                    <div class="divTableCellWanted"></div>
                    <div class="divTableCellWanted">ID</div>
                    <div class="divTableCellWanted"><?= $tdata['L_STATUS'] ?></div>
                    <div class="divTableCellWanted"></div>
                    <div class="divTableCellWanted"><?= $tdata['L_ADDED'] ?></div>
                    <div class="divTableCellWanted"><?= $tdata['L_CHECKED'] ?></div>
                    <div class="divTableCellWanted"><?= $tdata['L_TYPE'] ?></div>
                    <div class="divTableCellWanted">TMDB</div>
                    <div class="divTableCellWanted"><?= $tdata['L_TITLE'] ?></div>
                </div>
                <?= isset($tdata['wanted_list']) ? $tdata['wanted_list'] : null ?>
            </div>
        </div>
    </div>
</div>