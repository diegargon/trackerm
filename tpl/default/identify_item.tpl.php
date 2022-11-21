<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
//$title_tdata['del_iurl'] = $iurl . '&media_type=' . $media_type . '&ident_delete=' . $item['id'];
//$title_tdata['more_iurl'] = '?page=identify&media_type=' . $media_type . '&identify=' . $item['id'];
?>

<div class="divTableRow"><div class = "divTableCellID"><?= $tdata['predictible_title'] ?></div>
    <div class="divTableCellID">
        <?php if ($tdata['media_type'] == 'movies') {
            ?>
            <select class="ident_select" name="mult_movies_select[<?= $tdata['id'] ?>]"><?= $tdata['results_opt'] ?>
            <?php } else if ($tdata['media_type'] == 'shows') { ?>
                <select class="ident_select" name="mult_shows_select[<?= $tdata['id'] ?>]"><?= $tdata['results_opt'] ?>
                <?php } ?>
                <option value=""><?= $LNG['L_NOID'] ?></option>
                <?php
                foreach ($tdata['odb_results'] as $results) {
                    ?>
                    <option value="<?= $results['themoviedb_id'] ?>"><?= $results['title'] ?>(<?= $results['year'] ?>)</option>
                    <?php
                }
                ?>
            </select>
    </div>
    <div class="divTableCellID">
        <span><a class="action_link" href="?page=<?= $tdata['page'] ?>&media_type<?= $tdata['media_type'] ?>&ident_delete=<?= $tdata['id'] ?>"><?= $LNG['L_DELETE_REGISTER'] ?></a></span>
        <span><a class="action_link" href="?page=identify&media_type<?= $tdata['media_type'] ?>&identify=<?= $tdata['id'] ?>"><?= $LNG['L_MORE_SIGN'] ?></a></span>
    </div>
</div>