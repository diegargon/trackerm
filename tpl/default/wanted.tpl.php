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
            <?= !empty($tdata['quality_tags']) ? $tdata['quality_tags'] : $LNG['L_NONE']; ?>
            <span class="global_title"><?= $LNG['L_GLOBAL_TAGS'] ?></span>
            <?= !empty($tdata['ignore_tags']) ? $tdata['ignore_tags'] : null ?>
            <?= !empty($tdata['require_tags']) ? $tdata['require_tags'] : null ?>
            <?php if (!empty($tdata['require_or_tags'])) { ?>
                <span class="global_title"><?= $LNG['L_ANY_TAG'] ?></span>
                <?= $tdata['require_or_tags'] ?>
            <?php } ?>
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
