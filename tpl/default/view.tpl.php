<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="view_page">
    <div class="view_content">
        <div class="view_poster_container">
            <img class="view_poster" src="<?= $tdata['poster'] ?>" alt=""/>
            <?php
            if (!empty($tdata['guessed_poster'])) {
                ?>
                <div class="guessed_poster"><?= $LNG['L_POSTER_GUESSED'] ?></div>
            <?php } ?>
        </div>

        <div class="view_description_container">
            <h2><?= $tdata['title'] ?></h2>
            <?php if (!empty($tdata['plot'])) { ?>
                <div class="view_plot">
                    <p><?= $tdata['plot'] ?></p>
                </div>
            <?php } ?>
            <div class="view_info">
                <?php if (!empty($tdata['mediainfo_tags'])) {
                    ?>
                    <div class="mediainfo_container"><?= $tdata['mediainfo_tags'] ?></div>
                    <br/>
                <?php } ?>

                <?php if (!empty($tdata['themoviedb_id']) && $tdata['media_type'] == 'movies') { ?>
                    <span class="external_link"><a href="<?= $cfg['odb_movies_link'] . $tdata['themoviedb_id'] ?>" target=_blank>TheMovieDB</a></span><br/>
                <?php } else if (!empty($tdata['themoviedb_id']) && $tdata['media_type'] == 'shows') { ?>
                    <span class="external_link"><a href="<?= $cfg['odb_shows_link'] . $tdata['themoviedb_id'] ?>" target=_blank>TheMovieDB</a></span><br/>
                <?php } ?>
                <?php if (!empty($tdata['created'])) {
                    ?>
                    <span><?= $LNG['L_ADDED'] ?> :</span>
                    <span class="view_added"><?= strftime("%x", strtotime($tdata['created'])) ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['release'])) { ?>
                    <span><?= $LNG['L_RELEASE'] ?> :</span>
                    <span class="view_release"><?= strftime("%Y", strtotime($tdata['release'])) ?></span>
                    <br/>
                <?php } ?>
                <?php if ($tdata['media_type'] == 'shows' && isset($tdata['ended'])) {
                    ?>
                    <span><?= $LNG['L_STATE'] ?> :</span>
                    <?php if ($tdata['ended'] === 1) { ?>
                        <span class="view_ended"><?= $LNG['L_ENDED'] ?></span>
                    <?php } else if ($tdata['ended'] === 0) { ?>
                        <span class="view_ended"><?= $LNG['L_CONTINUE'] ?></span>
                        <?php
                    }
                    ?><br/><?php
                }
                ?>
                <?php if (!empty($tdata['total_size'])) { ?>
                    <span><?= $LNG['L_SIZE'] ?> :</span>
                    <span class="view_size"><?= $tdata['total_size'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['rating'])) { ?>
                    <span><?= $LNG['L_RATING'] ?> :</span>
                    <span class="view_rating"><?= $tdata['rating'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['total_items'])) { ?>
                    <span><?= $LNG['L_N_FILES'] ?> :</span>
                    <span class="view_have_episodes"><?= $tdata['total_items'] ?></span>
                    <br/>
                <?php } ?>
                <div class="view_actions">
                    <?php
                    if (!empty($tdata['in_library'])) {
                        ?>
                        <span class="action_link">
                            <?php if (!empty($tdata['in_library'])) { ?>
                                <a href="?page=view&id=<?= $tdata['in_library'] ?>&view_type=<?= $tdata['media_type'] ?>_library"><?= $LNG['L_HAVEIT'] ?></a>
                            <?php } ?>
                        </span>
                    <?php } ?>
                    <?php if (!empty($tdata['wanted']) && empty($tdata['movie_in_library']) && empty($tdata['show_in_library'])) { ?>
                        <a class="action_link" href="?page=wanted&id=<?= $tdata['themoviedb_id'] ?>&media_type=<?= $tdata['media_type'] ?>"><?= $LNG['L_WANTED'] ?></a>
                    <?php } ?>
                    <?= !empty($tdata['media_files']) ? $tdata['media_files'] : null; ?>

                    <?php if ($tdata['view_type'] == 'shows_library' || $tdata['view_type'] == 'shows_db') { ?>
                        <a class="action_link" href="?page=view&id=<?= $tdata['id'] ?>&view_type=<?= $tdata['view_type'] ?>&update=1"><?= $LNG['L_UPDATE_EPISODES'] ?></a>
                    <?php } ?>

                    <?php !empty($tdata['follow_show']) ? print $tdata['follow_show'] : null; ?>
                </div>
                <?php if (!empty($tdata['download'])) { ?>
                    <div class="view_download">
                        <form id="download_url" class="form_inline" method="POST" action="">
                            <input type="submit" class="action_link" value="<?= $LNG['L_DOWNLOAD'] ?>"/>
                            <input type="hidden" name="download" value="<?= $tdata['download'] ?>"/>
                        </form>

                    </div>
                <?php } ?>
                <?php if (!empty($tdata['seasons_data'])) { ?>
                    <?= $tdata['seasons_data'] ?>
                <?php } ?>
                <?php
                if (!empty($tdata['trailer'] || (!empty($tdata['guessed_trailer'])) && $tdata['guessed_trailer'] != -1)) {
                    !empty($tdata['trailer']) ? $trailer = $tdata['trailer'] : $trailer = $tdata['guessed_trailer'];
                    if (substr($trailer, 0, 5) != 'https') {
                        $trailer = str_replace('http', 'https', $trailer);
                    }
                    ?>
                    <div class="video_container">
                        <iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0"  type="text/html"
                                src="<?= $trailer ?>?autoplay=0&fs=1&iv_load_policy=0&rel=0&cc_load_policy=0"
                                frameborder="0"/></iframe>
                            <?php } ?>
                </div>
                <div class="view_extra">
                    <?= $tdata['extra'] ?>
                </div>
            </div>
        </div>
    </div>
</div>