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
        <h2><?= $LNG['L_WANTED'] ?></h2>
        <div class="wanted_opt_global">
            <span class="global_title"><?= $LNG['L_GLOBAL_QUALITY_TAGS'] ?></span>
            <?php
            if (!empty($cfg['torrent_quality_prefs'])) {
                foreach ($cfg['torrent_quality_prefs'] as $quality) {
                    ?>
                    <span class="tag_quality"><?= $quality ?></span>
                    <?php
                }
            }
            ?>
            <span class="global_title"><?= $LNG['L_GLOBAL_TAGS'] ?></span>
            <?php
            if (!empty($cfg['torrent_ignore_prefs'])) {
                foreach ($cfg['torrent_ignore_prefs'] as $ignores) {
                    ?>
                    <span class="tag_ignore"><?= $ignores ?></span>
                    <?php
                }
            }
            if (!empty($cfg['torrent_require_prefs'])) {
                foreach ($cfg['torrent_require_prefs'] as $require) {
                    ?>
                    <span class="tag_require"><?= $require ?></span>
                    <?php
                }
            }
            if (!empty($cfg['torrent_require_or_prefs'])) {
                ?>
                <span class="global_title"><?= $LNG['L_ANY_TAG'] ?></span>
                <?php
                foreach ($cfg['torrent_require_or_prefs'] as $or_require) {
                    ?>
                    <span class="tag_require"><?= $or_require ?></span>
                    <?php
                }
            }
            ?>
        </div>
        <div class="wanted_list_container">
            <?= isset($tdata['working_list']) ? $tdata['working_list'] : null ?>
        </div>
        <div class="wanted_list_container">
            <?= isset($tdata['track_show_list']) ? $tdata['track_show_list'] : null ?>
        </div>
    </div>
</div>
<div class="wanted_help">
    <div><?= $LNG['L_HELP_PROPER'] ?></div>
    <div><?= $LNG['L_HELP_NOCOUNT'] ?></div>
    <div><?= $LNG['L_HELP_IGNORE_TAGS'] ?></div>
    <div><?= $LNG['L_HELP_REQUIRE_TAGS'] ?></div>
</div>
<div style="clear:both;"></div>
