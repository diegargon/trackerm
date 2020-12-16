<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<div class="display display_1">
    <div class="poster_preview">
        <a href="?page=view&id=<?= $tdata['id'] ?>&type=<?= $tdata['ilink'] ?> ">
            <img class="img_poster_preview"  alt="" src="<?= $tdata['poster'] ?>"/>
        </a>
    </div>
    <div class="item_details">
        <div class="item_title"><?= $tdata['title'] ?>
            <?= isset($tdata['release']) ? ' (' . strftime("%Y", strtotime($tdata['release'])) . ')' : null ?>
        </div>
        <hr/>
        <div class="item_desc">
            <?php
            if (!empty($tdata['download'])) {
                ?>
                <span class="item_download"><a class="action_link" href="<?= basename($_SERVER['REQUEST_URI']) . '&download=' . rawurlencode($tdata['download']) ?>"><?= $tdata['L_DOWNLOAD_MIN'] ?></a></span>
                <?php
            }
            if (!empty($tdata['episode_count'])) {
                ?>
                <span class="item_episode_count">[<?= $tdata['L_EPISODE_MIN'] . $tdata['episode_count'] ?>]</span>
                <?php
            }
            if (!empty($tdata['size'])) {
                ?>
                <span class="item_size">[<?= $tdata['size'] ?>]</span>
                <?php
            }
            if (!empty($tdata['rating'])) {
                ?>
                <span class="item_rating">[<?= $tdata['L_RATING_MIN'] . $tdata['rating'] ?>]</span>
                <?php
            }
            if (!empty($tdata['trailer'])) {
                ?>
                <span class="item_link"><a href="<?= $tdata['trailer'] ?>" target="_blank">[T]</a></span>
                <?php
            }
            if (!empty($tdata['in_library'])) {
                ?>
                <span class="action_link">
                    <?php if ($tdata['ilink'] == 'movies_db') { ?>
                        <a href="?page=view&id=<?= $tdata['in_library'] ?>&type=movies_library"><?= $tdata['L_HAVEIT'] ?></a>
                    <?php } else if ($tdata['ilink'] == 'shows_db') { ?>
                        <a href="?page=view&id=<?= $tdata['in_library'] ?>&type=shows_library"><?= $tdata['L_HAVEIT'] ?></a>
                    <?php } ?>
                </span>
                <?php
            }
            if (!empty($tdata['source'])) {
                ?>
                <span class="item_source"><a class="action_link" href="<?= $tdata['guid'] ?>" target=_blank ><?= $tdata['source'] ?></a></span>
                    <?php
                }
                ?>
        </div>

    </div>
</div>
