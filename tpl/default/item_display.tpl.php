<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="display display_1">
    <div class="poster_preview">
        <a href="?page=view&id=<?= $tdata['id'] ?>&view_type=<?= $tdata['view_type'] ?> ">
            <img class="img_poster_preview"  alt="" src="<?= $tdata['poster'] ?>"/>
        </a>
        <?php
        if (!empty($tdata['guessed_poster'])) {
            ?>
            <div class="guessed_poster"><?= $LNG['L_POSTER_GUESSED'] ?></div>
        <?php } ?>
    </div>
    <div class="item_details">
        <div class="item_title"><?= $tdata['title'] ?></div>
        <hr/>
        <div class="item_desc">
            <?php
            if (!empty($tdata['download'])) {
                ?>
                <span class="item_download"><a class="action_link" href="<?= basename($_SERVER['REQUEST_URI']) . '&download=' . rawurlencode($tdata['download']) ?>"><?= $LNG['L_DOWNLOAD_MIN'] ?></a></span>
                <?php
            }
            if (!empty($tdata['source'])) {
                ?>
                <span class="item_source"><a class="action_link" href="<?= $tdata['guid'] ?>" target=_blank ><?= $tdata['source'] ?></a></span>
                <?php
            }
            if (!empty($tdata['num_episodes'])) {
                ?>
                <span class="item_num_episodes">[<?= $LNG['L_EPISODE_MIN'] . $tdata['num_episodes'] ?>]</span>
                <?php
            }
            if (!empty($tdata['size'])) {
                ?>
                <span class="item_size">[<?= $tdata['size'] ?>]</span>
                <?php
            }
            if (!empty($tdata['rating'])) {
                ?>
                <span class="item_rating">[<?= $LNG['L_RATING_MIN'] . $tdata['rating'] ?>]</span>
                <?php
            }
            if (!empty($tdata['trailer'])) {
                ?>
                <span class="item_link"><a href="<?= $tdata['trailer'] ?>" target="_blank">[T]</a></span>
                <?php
            }
            if (!empty($tdata['movie_in_library']) || !empty($tdata['show_in_library'])) {
                ?>
                <span class="action_link">
                    <?php if ($tdata['view_type'] == 'movies_db') { ?>
                        <a href="?page=view&id=<?= $tdata['movie_in_library'] ?>&view_type=movies_library"><?= $LNG['L_HAVEIT'] ?></a>
                    <?php } else if ($tdata['view_type'] == 'shows_db') { ?>
                        <a href="?page=view&id=<?= $tdata['show_in_library'] ?>&view_type=shows_library"><?= $LNG['L_HAVEIT'] ?></a>
                    <?php } ?>
                </span>
            <?php }
            ?>
        </div>

    </div>
</div>
