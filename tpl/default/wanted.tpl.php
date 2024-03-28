<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="wanted_page">
    <div class="wanted_list">
        <h2><?= $LNG['L_WANTED'] ?></h2>
        <div class="wanted_opt_global">
            <span class="global_title"><?= $LNG['L_GLOBAL_QUALITY_TAGS'] ?></span>

            <?php
            foreach ($tdata['quality_tags'] as $tag) {
                ?>
                <span class="tag_quality"><?= $tag ?></span>
            <?php }
            ?>

            <span class="global_title"><?= $LNG['L_GLOBAL_TAGS'] ?></span>

            <?php
            foreach ($tdata['ignore_tags'] as $tag) {
                ?>
                <span class="tag_ignore"><?= $tag ?></span>
            <?php }
            ?>
            <?php
            foreach ($tdata['require_tags'] as $tag) {
                ?>
                <span class="tag_require"><?= $tag ?></span>
            <?php }
            ?>
            <span class="global_title"><?= $LNG['L_ANY_TAG'] ?></span>
            <?php
            foreach ($tdata['require_or_tags'] as $tag) {
                ?>
                <span class="tag_require"><?= $tag ?></span>
            <?php }
            ?>

        </div>
        <div class="wanted_list_container">
            <?= isset($tdata['wanted_list']) ? $tdata['wanted_list'] : null ?>
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
