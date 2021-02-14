<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="divTableRow"><div class = "divTableCellID"><?= $tdata['predictible_title'] ?></div>
    <div class="divTableCellID">
        <?php if ($tdata['media_type'] == 'movies') {
            ?>
            <select class="ident_select" name="mult_movies_select[<?= $tdata['id'] ?>]"><?= $tdata['results_opt'] ?>
                <option value=""><?= $tdata['L_NOID'] ?></option>
            </select>
        <?php } else if ($tdata['media_type'] == 'shows') { ?>
            <select class="ident_select" name="mult_shows_select[<?= $tdata['id'] ?>]"><?= $tdata['results_opt'] ?>
                <option value=""><?= $tdata['L_NOID'] ?></option>
            </select>
        <?php } ?>
    </div>
    <div class="divTableCellID">
        <span><a class="action_link" href="<?= $tdata['del_iurl'] ?>"><?= $tdata['L_DELETE_REGISTER'] ?></a></span>
        <span><a class="action_link" href="<?= $tdata['more_iurl'] ?>"><?= $tdata['L_MORE_SIGN'] ?></a></span>
    </div>
</div>