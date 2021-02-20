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
            <img class="view_poster" src="<?= isset($tdata['poster']) ? $tdata['poster'] : null ?>" alt=""/>
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
                    <span class="external_link"><a href="<?= $cfg['odb_movies_link'] . $tdata['themoviedb_id'] ?>" target=_blank>TheMovieDB</a></span><br/>
                <?php } ?>
                <?php if (!empty($tdata['created'])) {
                    ?>
                    <span><?= $LNG['L_ADDED'] ?> :</span>
                    <span class="view_added"><?= strftime("%d %h", strtotime($tdata['created'])) ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['release'])) { ?>
                    <span><?= $LNG['L_RELEASE'] ?> :</span>
                    <span class="view_release"><?= $tdata['release'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['size'])) { ?>
                    <span><?= $LNG['L_SIZE'] ?> :</span>
                    <span class="view_size"><?= $tdata['size'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['rating'])) { ?>
                    <span><?= $LNG['L_RATING'] ?> :</span>
                    <span class="view_rating"><?= $tdata['rating'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['have_episodes'])) { ?>
                    <span><?= $LNG['L_EPISODES_HAVE'] ?> :</span>
                    <span class="view_have_episodes"><?= $tdata['have_episodes'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['mediainfo']['General']['Duration'])) { ?>
                    <span><?= $LNG['L_DURATION'] ?> :</span>
                    <span><?= $tdata['mediainfo']['General']['Duration'] ?></span>
                    <hr/>
                <?php } ?>
                <div class="view_actions">
                    <?php
                    if (!empty($tdata['in_library'])) {
                        ?>
                        <span class="action_link">
                            <?php if ($tdata['ilink'] == 'movies_db') { ?>
                                <a href="?page=view&id=<?= $tdata['in_library'] ?>&view_type=movies_library"><?= $LNG['L_HAVEIT'] ?></a>
                            <?php } else if ($tdata['ilink'] == 'shows_db') { ?>
                                <a href="?page=view&id=<?= $tdata['in_library'] ?>&view_type=shows_library"><?= $LNG['L_HAVEIT'] ?></a>
                            <?php } ?>
                        </span>
                    <?php } ?>
                    <?php if (!empty($tdata['wanted']) && empty($tdata['in_library'])) { ?>
                        <a class="action_link" href="?page=wanted&id=<?= $tdata['themoviedb_id'] ?>&media_type=<?= $tdata['media_type'] ?>"><?= $LNG['L_WANTED'] ?></a>
                    <?php } ?>
                    <?php if (!empty($tdata['reidentify'])) { ?>
                        <a class="action_link" href="?page=identify&identify=<?= $tdata['id'] ?>&media_type=<?= $tdata['media_type'] ?>"><?= $LNG['L_IDENTIFY'] ?></a>
                    <?php } ?>
                    <?php if (!empty($tdata['deletereg'])) { ?>
                        <a class="action_link" href="?page=view&id=<?= $tdata['id'] ?>&view_type=<?= $tdata['view_type'] ?>&deletereg=1" onclick="return confirm('Are you sure?')" ><?= $LNG['L_DELETE_REGISTER'] ?></a>
                    <?php } ?>
                    <?php if ($tdata['ilink'] == 'shows_library') { ?>
                        <a class="action_link" href="?page=view&id=<?= $tdata['id'] ?>&view_type=shows_library&update=1"><?= $LNG['L_UPDATE_EPISODES'] ?></a>
                    <?php } ?>
                    <?php if ($tdata['ilink'] == 'movies_library' && $cfg['localplayer']) { ?>
                        <a class="action_link"  target=_blank href="?page=localplayer&id=<?= $tdata['id'] ?>&media_type=<?= $tdata['media_type'] ?>">LocalPlayer</a>
                    <?php } ?>
                    <?php if ($tdata['ilink'] == 'movies_library' && ($cfg['download_button'])) { ?>
                        <a class="action_link" href="?page=download&id=<?= $tdata['id'] ?>&view_type=movies_library" target=_blank><?= $LNG['L_DOWNLOAD'] ?></a>
                    <?php } ?>
                    <?php !empty($tdata['follow_show']) ? print $tdata['follow_show'] : null; ?>
                </div>
                <?php if (!empty($tdata['download'])) { ?>
                    <div class="view_download">
                        <a onClick="show_loading()" class="submit_link" href="<?= basename($_SERVER['REQUEST_URI']) . '&download=' . rawurlencode($tdata['download']) ?>"><?= $LNG['L_DOWNLOAD'] ?></a>
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