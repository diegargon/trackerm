<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<!-- <div class="wanted_list_row"> -->
<div class="divTableRow">
    <div class="divTableCellWanted">
        <a href="<?= $tdata['iurl'] . '&delete=' . $tdata['id'] ?>" class="action_link"><?= $tdata['L_DELETE'] ?></a>
    </div>
    <div class="divTableCellWanted">
        <a href="<?= $tdata['iurl'] . '&ignore=' . $tdata['id'] ?>" class="action_link"><?= $tdata['ignore_link'] ?></a>
    </div>
    <div class="divTableCellWanted">
        <span class="tag_id"><?= $tdata['id'] ?></span>
    </div>
    <div class="divTableCellWanted">
        <span class="tag_state"><?= $tdata['status_name'] ?></span>
    </div>
    <div class="divTableCellWanted">
        <span class="tag_day"><?= $tdata['day_check'] ?></span>
    </div>
    <div class="divTableCellWanted">
        <span class="tag_added"><?= $tdata['added'] ?></span>
    </div>
    <div class="divTableCellWanted">
        <span class="tag_day"><?= $tdata['last_check'] ?></span>
    </div>
    <div class="divTableCellWanted">
        <span class="tag_type"><?= $tdata['lang_media_type'] ?></span>
    </div>
    <div class="divTableCellWanted">
        <span class="tag_id">
            <?php if (!empty($tdata['elink'])) { ?>
                <a href="<?= $tdata['elink'] ?>" target="_blank"><?= $tdata['themoviedb_id'] ?></a>
                <?php
            } else {
                print $tdata['themoviedb_id'];
            }
            ?>
        </span>
    </div>
    <div class="divTableCellWanted">
        <span class="tag_title"><?= $tdata['title'] ?></span>
    </div>
</div>