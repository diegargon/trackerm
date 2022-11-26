<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);
$page_link = '?page=view&id=' . $tdata['id'] . '&view_type=' . $tdata['media_type'] . '_library';
$check_link = $page_link . '&vid=' . $tdata['selected_id'] . '&media_type=' . $tdata['media_type'];
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
                    <div class="mediainfo_container">
                        <?php
                        foreach ($tdata['mediainfo_tags'] as $media_tag) {
                            ?>
                            <div title="<?= $media_tag['mediainfo_tag_title'] ?>" class="mediainfo_tag"><?= $media_tag['mediainfo_tag_value'] ?></div>
                            <?php
                        }
                        ?>
                    </div>
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
                    <span><?= $LNG['L_RELEASED'] ?> :</span>
                    <span class="view_release"><?= strftime("%Y", strtotime($tdata['release'])) ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['f_genres'])) { ?>
                    <span><?= $LNG['L_GENRES'] ?> :</span>
                    <span class="view_genres">
                        <?php foreach ($tdata['f_genres'] as $genre) { ?>
                            <span class="fgenres"><a class="nodecor" href="?page=view_genres&media_type=<?= $tdata['media_type'] ?>&id=<?= $genre['id'] ?>"><?= $genre['name'] ?></a></span>
                        <?php } ?>
                    </span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['f_collection'])) { ?>
                    <span><?= $LNG['L_COLLECTION'] ?> :</span>
                    <?php foreach ($tdata['f_collection'] as $collection) { ?>
                        <span class="view_collection"><a class="nodecor" href="?page=<?= $collection['view_name'] ?>&media_type=<?= $tdata['media_type'] ?>&group_type=<?= $collection['group_type'] ?>&id=<?= $collection['id'] ?>"><?= $collection['name'] ?></a></span>
                    <?php } ?>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['f_director'])) { ?>
                    <span><?= $LNG['L_DIRECTOR'] ?> :</span>
                    <?php foreach ($tdata['f_director'] as $director) { ?>

                        <span class="view_director"><a class="nodecor" href="?page=<?= $director['view_name'] ?>&media_type=<?= $tdata['media_type'] ?>&name=<?= $director['name'] ?>"><?= $director['name'] ?></a></span>
                    <?php } ?>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['f_cast'])) { ?>
                    <span><?= $LNG['L_CAST'] ?> :</span>
                    <?php foreach ($tdata['f_cast'] as $cast) { ?>
                        <span class="view_cast"><a class="nodecor" href="?page=<?= $cast['view_name'] ?>&media_type=<?= $tdata['media_type'] ?>&name=<?= $cast['name'] ?>"><?= $cast['name'] ?></a></span>
                    <?php } ?>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['f_writer'])) { ?>
                    <span><?= $LNG['L_WRITER'] ?> :</span>
                    <?php foreach ($tdata['f_writer'] as $writer) { ?>
                        <span class="view_writer"><a class="nodecor" href="?page=<?= $writer['view_name'] ?>&media_type=<?= $tdata['media_type'] ?>&name=<?= $writer['name'] ?>"><?= $writer['name'] ?></a></span>
                    <?php } ?>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['source'])) { ?>
                    <span><?= $LNG['L_SOURCE'] ?> :</span>
                    <span class="external_link"><a href="<?= $tdata['guid'] ?>" target=_blank><?= $tdata['source'] ?></a></span>
                    <?php if (!empty($tdata['freelech'])) { ?>
                        <span>(<?= $LNG['L_FREELECH'] ?>)</span>
                    <?php } ?>
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
                    <span><?= $LNG['L_TOTAL_SIZE'] ?> :</span>
                    <span class="view_size"><?= $tdata['total_size'] ?></span>
                    <br/>
                <?php } else if (!empty($tdata['size'])) { ?>
                    <span><?= $LNG['L_SIZE'] ?> :</span>
                    <span class="view_size"><?= bytesToGB($tdata['size'], 2) ?>GB</span>
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
                <?php if (!empty($tdata['mediainfo_file_duration'])) { ?>
                    <span><?= $LNG['L_DURATION'] ?> :</span>
                    <span class="mediainfo_file_duration"><?= $tdata['mediainfo_file_duration'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['mediainfo_file_size'])) { ?>
                    <span><?= $LNG['L_SIZE'] ?> :</span>
                    <span class="mediainfo_file_size"><?= $tdata['mediainfo_file_size'] ?></span>
                    <br/>
                <?php } ?>

                <?= (!empty($tdata['add_pre_actions'])) ? $tdata['add_pre_actions'] : null ?>
                <!-- ACTIONS -->
                <div class="view_actions">
                    <?php if (isset($tdata['already_view_file'])) { ?>
                        <a class="action_link <?= $tdata['already_view_file'] ? 'item_view_view' : null ?>" href="<?= $check_link ?>">&#10003;</a>
                    <?php } ?>
                    <?php if (isset($tdata['identify_btn'])) {
                        ?>
                        <a  class="action_link" href="?page=identify&identify=<?= $tdata['selected_id'] ?>&media_type=<?= $tdata['media_type'] ?>"><?= $LNG['L_IDENTIFY'] ?></a>
                        <?php
                    }
                    ?>
                    <?php if (isset($tdata['identify_all_btn'])) {
                        ?>
                        <a class="action_link" href="?page=identify&identify_all=<?= $tdata['selected_id'] ?>&media_type=<?= $tdata['media_type'] ?>"><?= $LNG['L_IDENTIFY_ALL'] ?></a>
                        <?php
                    }
                    ?>
                    <?php if (isset($tdata['show_delete_opts'])) { ?>
                        <form class="inline" method="POST" action="">
                            <select name="delete_opt">
                                <option value="1"><?= $LNG['L_DELETE_REGISTER'] ?></option>
                                <option value="2"><?= $LNG['L_DELETE_FILE'] ?></option>
                                <option value="3"><?= $LNG['L_DELETE_FILES'] ?></option>
                            </select>
                            <input type="hidden" name="file_id" value="<?= $tdata['selected_id'] ?>"/>
                            <input type="hidden" name="file_master" value="<?= $tdata['id'] ?>"/>
                            <input type="hidden" name="media_type" value="<?= $tdata['media_type'] ?>"/>
                            <input type="submit" class="action_link" onClick="return confirm('<?= $LNG['L_AREYOUSURE'] ?>')" value="<?= $LNG['L_DELETE'] ?>">
                        </form>
                        <?php
                    }
                    ?>

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
                        <a class="action_link" href="?page=view&id=<?= $tdata['id'] ?>&view_type=<?= $tdata['view_type'] ?>&season=<?= $tdata['season'] ?>&update=1"><?= $LNG['L_UPDATE_EPISODES'] ?></a>
                    <?php } ?>

                    <?php
                    if (!empty($tdata['follow_show']) && is_array($tdata['follow_show'])) {
                        ?>
                        <form class="inline" method="POST" action="?page=wanted">
                            <input type="hidden" name="id" value="<?= $tdata['follow_show']['oid'] ?>" />
                            <input class="action_link" type="submit" name="track_show" value="<?= $LNG['L_FOLLOW_SHOW'] ?>"/>
                            <select name="track_show">
                                <?php
                                foreach ($tdata['follow_show']['options'] as $option) {
                                    ?>
                                    <option value="<?= $option ?>"><?= $option ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </form>
                        <?php
                    }
                    ?>
                    <?php if (!empty($tdata['custom_poster_btn']) && !empty($user->isAdmin())) { ?>
                        <a class="action_link" href="?page=view&id=<?= $tdata['id'] ?>&view_type=<?= $tdata['view_type'] ?>&show_custom_poster=1"><?= $LNG['L_CUSTOM_POSTER'] ?></a>
                    <?php } ?>

                    <?php if (isset($tdata['show_localplayer'])) {
                        ?>
                        <a  class="action_link" href="?page=localplayer&id=<?= $tdata['selected_id'] ?>&media_type=<?= $tdata['media_type'] ?>">LocalPlay</a>
                        <?php
                    }
                    ?>

                    <?php if (isset($tdata['show_download_button'])) {
                        ?>
                        <a  class="action_link" href="?page=download&id=<?= $tdata['selected_id'] ?>&media_type=<?= $tdata['media_type'] ?>&view_type=<?= $tdata['view_type'] ?>"><?= $LNG['L_DOWNLOAD'] ?></a>
                        <?php
                    }
                    ?>

                    <?php if (!empty($tdata['download'])) { ?>
                        <div class="view_download">
                            <form id="download_url" class="form_inline" method="POST" >
                                <input type="submit" class="action_link" value="<?= $LNG['L_DOWNLOAD'] ?>"/>
                                <input type="hidden" name="download" value="<?= $tdata['download'] ?>"/>
                            </form>

                        </div>
                    <?php } ?>
                    <?php if (!empty($tdata['show_custom_poster'])) { ?>
                        <div class="custom_poster_div">
                            <form id="change_custom_poster" class="form_inline" method="POST" >
                                <input type="text" name="new_custom_poster" size="40" placeholder="https://example.com/poster.jpg" value="<?= $tdata['custom_poster'] ?>"/>
                                <input type="submit" name="change_custom_poster" class="action_link" value="<?= $LNG['L_CHANGE'] ?>"/>
                            </form>
                        </div>
                    <?php } ?>
                </div>
                <!-- //VIEW ACTIONS -->
                <!-- BEFORE_TRAILER -->
                <?php if (!empty($tdata['before_trailer'])) { ?>
                    <?= $tdata['before_trailer'] ?>
                <?php } ?>
                <!-- //END BEFORE_TRAILER -->
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
            </div> <!-- //view_info -->
        </div> <!-- view_description_container -->
    </div> <!-- view_content -->
</div> <!-- view_page -->