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
    <?php
    if (!empty($tdata['source'])) {
        ?>
        <div class="banner_head">
            <div class="item_source">
                <a class="tor_source_link<?= !empty($tdata['freelech']) ? '_freelech" title="freelech' : '" title="no freelech' ?>" href="<?= $tdata['guid'] ?>" target=_blank ><?= $tdata['source'] ?></a>
            </div>
        </div>
    <?php } ?>
    <div class="poster_preview">
        <?php
        if (($tdata['view_type'] == 'movies_library' || $tdata['view_type'] == 'shows_library') && !empty($cfg['localplayer']) && !empty($prefs->getPrefsItem('view_mode'))) {
            ?>
            <div class="overlay_play">
                <div class="main_play">
                    <a href="?page=localplayer&mid=<?= $tdata['id'] ?>&media_type=<?= $tdata['media_type'] ?>">&#9656;</a>
                </div>
            </div>
        <?php } ?>
        <a onClick="show_loading();" href="?page=view&id=<?= $tdata['id'] ?>&view_type=<?= $tdata['view_type'] ?> ">
            <img class="img_poster_preview"  alt="" src="<?= $tdata['poster'] ?>"/>
        </a>
        <?php
        if (!empty($tdata['guessed_poster'])) {
            ?>
            <div class="guessed_poster"><?= $LNG['L_POSTER_GUESSED'] ?></div>
            <?php
        }
        if (($tdata['view_type'] == 'movies_library' || $tdata['view_type'] == 'shows_library') &&
                !empty($tdata['total_items']) && ($tdata['total_items'] > 1) && empty($prefs->getPrefsItem('view_mode'))) {
            ?>
            <div class="overlay_nfiles">
                <div class="nfiles"><?= $tdata['total_items'] ?></div>
            </div>
            <?php
        }
        if (($tdata['view_type'] == 'movies_library' || $tdata['view_type'] == 'shows_library') && !empty($prefs->getPrefsItem('view_mode'))) {
            ?>
            <div class="overlay_nfiles">
                <div class="nfiles">
                    <?php
                    if (!empty($prefs->getPrefsItem('view_mode')) && isset($tdata['total_unseen_items'])) {
                        print $tdata['total_unseen_items'];
                    } else {
                        print $tdata['total_items'];
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="stack_overlay">
            <?php if (!empty($tdata['have_it'])) { ?>
                <div class="have_it"><a onClick="show_loading();" href="?page=view&id=<?= $tdata['have_it'] ?>&view_type=<?= $tdata['media_type'] ?>_library">&#9668;</a></div>
            <?php } ?>
        </div>
    </div>
    <div class="item_details">
        <div class="item_title"><?= $tdata['title'] ?></div>
        <hr/>
        <div class="item_desc">
            <?php
            if (!empty($tdata['download'])) {
                ?>
                <form id="download_url" class="form_inline" method="POST" >
                    <input type="submit" class="action_link" value="<?= $LNG['L_DOWNLOAD_MIN'] ?>"/>
                    <input type="hidden" name="download" value="<?= $tdata['download'] ?>"/>
                </form>
                <?php
            }
            if (( $tdata['view_type'] == 'movies_library' || $tdata['view_type'] == 'shows_library')) {
                if (isset($tdata['total_unseen_items']) && $tdata['total_unseen_items'] <= 0) {
                    ?>
                    <form class="inline" method="POST">
                        <div class="inline" data-tip="<?= $LNG['L_SEEN'] ?>">
                            <input type="hidden" name="vid" value="<?= $tdata['id'] ?>" />
                            <input type="hidden" name="media_type" value="<?= $tdata['media_type'] ?>" />
                            <input class="action_link_small text_green" type="submit" name="vid_btn" value="&#10003;"/>
                        </div>
                    </form>
                <?php } else { ?>
                    <form class="inline" method="POST">
                        <div class="inline" data-tip="<?= $LNG['L_UNSEEN'] ?>">
                            <input type="hidden" name="vid" value="<?= $tdata['id'] ?>" />
                            <input type="hidden" name="media_type" value="<?= $tdata['media_type'] ?>" />
                            <input class="action_link_small text_gray" type="submit" name="vid_btn" value="&#10003;"/>
                        </div>
                    </form>
                    <?php
                }
            }
            if (!empty($tdata['total_size'])) {
                ?>
                <span class="item_size">[<?= $tdata['total_size'] ?>]</span>
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
                        <a onClick="show_loading();" href="?page=view&id=<?= $tdata['movie_in_library'] ?>&view_type=movies_library"><?= $LNG['L_HAVEIT'] ?></a>
                    <?php } else if ($tdata['view_type'] == 'shows_db') { ?>
                        <a onClick="show_loading();" href="?page=view&id=<?= $tdata['show_in_library'] ?>&view_type=shows_library"><?= $LNG['L_HAVEIT'] ?></a>
                    <?php } ?>
                </span>
            <?php }
            ?>
        </div>

    </div>
</div>
