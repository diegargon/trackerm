<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<!-- BEGIN WANTED TABLEROW> -->
<?php
if ($tdata['want_separator']) {
    ?>
    <div class="divTableRow">
        <div class="divTableCellWanted">&nbsp;</div>
    </div>
    <?php
} else {
    ?>
    <div class="divTableRow">
        <div class="divTableCellWanted">
            <a href="<?= $tdata['iurl'] . '&delete=' . $tdata['id'] ?>" class="action_link"><?= $LNG['L_DELETE'] ?></a>
        </div>
        <div class="divTableCellWanted">
            <div class="tag_state"><?= $tdata['status_name'] ?></div>
        </div>
        <div class="divTableCellWanted">
            <div class="tag_day"><?= $tdata['day_check'] ?></div>
        </div>
        <div class="divTableCellWanted">
            <div class="tag_added"><?= $tdata['created'] ?></div>
        </div>
        <div class="divTableCellWanted">
            <div class="tag_day"><?= $tdata['last_check'] ?></div>
        </div>
        <div class="divTableCellWanted">
            <div class="tag_type"><?= $tdata['lang_media_type'] ?></div>
        </div>
        <div class="divTableCellWanted">
            <div class="tag_type">
                <form class="form_inline" method="POST" action="">
                    <input type="hidden" name="only_proper[<?= $tdata['id'] ?>]" value="0">
                    <input  <?= !empty($tdata['only_proper']) ? "checked" : null ?> onchange="this.form.submit()" type="checkbox" name="only_proper[<?= $tdata['id'] ?>]]" value="1">
                </form>
            </div>
        </div>
        <div class="divTableCellWanted">
            <div class="inline" data-tip="<?= $LNG['L_TIP_COMMA'] ?>">
                <div class="tag_type"><form class="form_inline" method="post" action=""><input class="wanted_input_red" name="ignore_tags[<?= $tdata['id'] ?>]" type="text" onchange="this.form.submit()" value="<?= !empty($tdata['custom_words_ignore']) ? $tdata['custom_words_ignore'] : null ?>"/></form></div>
            </div>
        </div>
        <div class="divTableCellWanted">
            <div class="inline" data-tip="<?= $LNG['L_TIP_COMMA'] ?>">
                <div class="tag_type"><form class="form_inline" method="post" action=""><input class="wanted_input" name="require_tags[<?= $tdata['id'] ?>]"  type="text" onchange="this.form.submit()" value="<?= !empty($tdata['custom_words_require']) ? $tdata['custom_words_require'] : null ?>"/></form></div>
            </div>
        </div>
        <div class="divTableCellWanted">
            <div class="tag_id">
                <?php if (!empty($tdata['elink'])) { ?>
                    <a href="<?= $tdata['elink'] ?>" target="_blank">TMDB</a>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="divTableCellWanted">
            <div class="tag_title"><a class="wanted_link" href="<?= $tdata['link'] ?>"><?= $tdata['link_name'] ?></a></div>
        </div>
    </div>
    <!-- END WANTED TABLEROW> -->
<?php } ?>