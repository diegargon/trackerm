<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="divTableRow">
    <div class="divTableCellEpisodes"><?= $tdata['episode'] ?></div>
    <?php if (!empty($tdata['have_show'])) { ?>
        <div class="divTableCellEpisodes" style="color:yellow;"><?= $tdata['title'] ?></div>
    <?php } else { ?>
        <div class="divTableCellEpisodes"><?= $tdata['title'] ?></div>
    <?php } ?>
    <div class="divTableCellEpisodes">
        <?php if (!empty($tdata['have_show'])) { ?>
            <?php if (!empty($tdata['download_button'])) { ?>
                <a class="episode_link" href="?page=download&type=shows_library&id=<?= $tdata['have_show']['id'] ?>"><?= $tdata['L_DOWNLOAD'] ?></a>
            <?php } ?>
            <?php if (!empty($tdata['localplayer'])) { ?>
                <a class="episode_link inline"  target=_blank href="?page=localplayer&id=<?= $tdata['have_show']['id'] ?>&media_type=shows"><?= $tdata['L_LOCALPLAYER'] ?></a>
            <?php } ?>
        <?php } else { ?>
            <a class="episode_link" href="<?= $tdata['iurl'] ?>&wanted=1&season=<?= $tdata['season'] ?>&episode=<?= $tdata['episode'] ?>"><?= $tdata['L_WANTED'] ?></a>
        <?php } ?>
    </div>
</div>
