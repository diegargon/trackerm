<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
?>

<!-- <div class="wanted_list_row"> -->
<div class="divTableRow">
    <div class="divTableCellWanted">
        <a href="<?= $tdata['iurl'] . '&delete=' . $tdata['id'] ?>" class="action_link"><?= $tdata['L_DELETE'] ?></a>
    </div>
    <!--
        <div class="divTableCellWanted">
            <div class="tag_id"><?= $tdata['id'] ?></div>
        </div>
    -->
    <div class="divTableCellWanted">
        <div class="tag_state"><?= $tdata['status_name'] ?></div>
    </div>
    <div class="divTableCellWanted">
        <div class="tag_day"><?= $tdata['day_check'] ?></div>
    </div>
    <div class="divTableCellWanted">
        <div class="tag_added"><?= $tdata['added'] ?></div>
    </div>
    <div class="divTableCellWanted">
        <div class="tag_day"><?= $tdata['last_check'] ?></div>
    </div>
    <div class="divTableCellWanted">
        <div class="tag_type"><?= $tdata['lang_media_type'] ?></div>
    </div>
    <div class="divTableCellWanted">
        <div class="tag_type"><form class="form_inline" method="post" action=""><input class="wanted_input" name="ignore_tags[<?= $tdata['id'] ?>]" type="text" onchange="this.form.submit()" value="<?= !empty($tdata['custom_words_ignore']) ? $tdata['custom_words_ignore'] : null ?>"/></form></div>
    </div>
    <div class="divTableCellWanted">
        <div class="tag_type"><form class="form_inline" method="post" action=""><input class="wanted_input" name="require_tags[<?= $tdata['id'] ?>]"  type="text" onchange="this.form.submit()" value="<?= !empty($tdata['custom_words_require']) ? $tdata['custom_words_require'] : null ?>"/></form></div>
    </div>
    <div class="divTableCellWanted">
        <div class="tag_id">
            <?php if (!empty($tdata['elink'])) { ?>
                <a href="<?= $tdata['elink'] ?>" target="_blank"><?= $tdata['themoviedb_id'] ?></a>
                <?php
            } else {
                print $tdata['themoviedb_id'];
            }
            ?>
        </div>
    </div>
    <div class="divTableCellWanted">
        <div class="tag_title"><?= !empty($tdata['shown_title']) ? $tdata['shown_title'] : $tdata['title'] ?></div>
    </div>
</div>