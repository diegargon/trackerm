<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<div class="wanted_list_row">
    <a href="<?= $tdata['iurl'] . '&delete=' . $tdata['id'] ?>" class="action_link"><?= $tdata['L_DELETE'] ?></a>
    <a href="<?= $tdata['iurl'] . '&ignore=' . $tdata['id'] ?>" class="action_link"><?= $tdata['ignore_link'] ?></a>
    <span class="tag_id"><?= $tdata['id'] ?></span>
    <span class="tag_state"><?= $tdata['wanted_state'] ?></span>
    <span class="tag_title"><?= $tdata['title'] ?></span>
    <span class="tag_type"><?= $tdata['media_type'] ?></span>
    <span class="tag_day"><?= $tdata['day_check'] ?></span>
    <?php
    foreach ($cfg['TORRENT_QUALITYS_PREFS'] as $quality) {
        ?>
        <span class="tag_quality"><?= $quality ?></span>
        <?php
    }
    foreach ($cfg['TORRENT_IGNORES_PREFS'] as $ignores) {
        ?>
        <span class="tag_ignore"><?= $ignores ?></span>
    <?php } ?>
    <span class="tag_added"><?= $tdata['L_ADDED'] . ' :' . $tdata['added'] ?></span>
    <span class="tag_day"><?= $tdata['L_CHECKED'] . ': ' . $tdata['last_check'] ?></span>
    <span class="tag_id">TMDB:
        <?php if (!empty($tdata['elink'])) { ?>
            <a href="<?= $tdata['elink'] ?>" target="_blank"><?= $tdata['themoviedb_id'] ?></a>
            <?php
        } else {
            print $tdata['themoviedb_id'];
        }
        ?>
    </span>
</div>