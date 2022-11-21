<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);

if (isset($tdata['tpl_var_ary_first'])) {
    ?>
    <hr/><div class="divTable">
        <?php
    }
    ?>

    <!-- REPEAT -->
    <div class="divTableRow">
        <div class="divTableCellEpisodes"><?= $tdata['episode'] ?></div>
        <?php if (!empty($tdata['have_show'])) { ?>
            <div class="divTableCellEpisodes" style="color:yellow;"><?= $tdata['title'] ?></div>
        <?php } else { ?>
            <div class="divTableCellEpisodes"><?= $tdata['title'] ?></div>
        <?php } ?>
        <div class="divTableCellEpisodes">
            <?php if (!empty($tdata['have_show'])) { ?>
                <a class="<?= !empty($tdata['viewed']) ? 'item_view_view' : 'item_view_noview' ?>" href="?page=view&id=<?= $tdata['have_show']['master'] ?>&vid=<?= $tdata['have_show']['id'] ?>&view_type=shows_library&media_type=shows&season=<?= $tdata['season'] ?>">&#10003;</a>
                <?php if (!empty($cfg['download_button'])) { ?>
                    <a class="episode_link" href="?page=download&view_type=shows_library&id=<?= $tdata['have_show']['id'] ?>&media_type=shows"><?= $LNG['L_DOWNLOAD'] ?></a>
                <?php } ?>
                <?php if (!empty($cfg['localplayer'])) { ?>
                    <a class="episode_link inline"  target=_blank href="?page=localplayer&id=<?= $tdata['have_show']['id'] ?>&media_type=shows"><?= $LNG['L_LOCALPLAYER'] ?></a>
                <?php } ?>
            <?php } else { ?>
                <a class="episode_link" href="?page=view&id=<?= $tdata['master_id'] ?>&wanted=1&season=<?= $tdata['season'] ?>&episode=<?= $tdata['episode'] ?>"><?= $LNG['L_WANTED'] ?></a>
            <?php } ?>
        </div>
    </div>

    <!-- END REPEAT -->

    <?php
    if (isset($tdata['tpl_var_ary_last'])) {
        ?>
    </div> <!-- endDivTable -->

    <?php
    if (!empty($tdata['missing_episodes'])) {
        ?>
        <div class="episode_options">
            <a class="episode_link" href="?page=view&wanted=1&season=<?= $tdata['season'] ?>&episode=<?= $tdata['missing_episodes'] ?> "><?= $LNG['L_WANT_ALL'] ?></a>
        </div>
        <?php
    }
}
?>

