<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<div class="view_page">
    <div class="view_content">
        <div class="view_poster_container">
            <img class="view_poster" src="<?= isset($tdata['poster']) ? $tdata['poster'] : null ?>" alt=""/>
        </div>

        <div class="view_description_container">
            <h2><?= $tdata['title'] ?></h2>
            <?php if (!empty($tdata['plot'])) { ?>
                <div class="view_plot">
                    <p><?= $tdata['plot'] ?></p>
                </div>
            <?php } ?>
            <div class="">
                <span>IDs :</span><span>
                    <?= $tdata['id'] ?>
                    <?php
                    if (isset($tdata['themoviedb_id'])) {
                        echo ' / ' . $tdata['themoviedb_id'];
                    }
                    ?>
                </span><br/>
                <?php if (!empty($tdata['added'])) { ?>
                    <span><?= $tdata['L_ADDED'] ?> :</span>
                    <span class="view_added"><?= date("d-m-y", $tdata['added']) ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['release'])) { ?>
                    <span><?= $tdata['L_RELEASE'] ?> :</span>
                    <span class="view_release"><?= $tdata['release'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['size'])) { ?>
                    <span><?= $tdata['L_SIZE'] ?> :</span>
                    <span class="view_size"><?= human_filesize($tdata['size']) ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['rating'])) { ?>
                    <span><?= $tdata['L_RATING'] ?> :</span>
                    <span class="view_rating"><?= $tdata['rating'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['popularity'])) { ?>
                    <span><?= $tdata['L_POPULARITY'] ?> :</span>
                    <span class="view_popularity"><?= $tdata['popularity'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['seasons_data'])) { ?>
                    <?= $tdata['seasons_data'] ?>
                <?php } ?>
                <div class="view_actions">
                    <?php
                    if (!empty($tdata['in_library'])) {
                        ?>
                        <span class="action_link">
                            <?php if ($tdata['ilink'] == 'movies_db') { ?>
                                <a href="?page=view&id=<?= $tdata['in_library'] ?>&type=movies_library"><?= $tdata['L_HAVEIT'] ?></a>
                            <?php } else if ($tdata['ilink'] == 'shows_db') { ?>
                                <a href="?page=view&id=<?= $tdata['in_library'] ?>&type=shows_library"><?= $tdata['L_HAVEIT'] ?></a>
                            <?php } ?>
                        </span>
                    <?php } ?>
                    <?php if (!empty($tdata['wanted']) && empty($tdata['in_library'])) { ?>
                        <a class="action_link" href="?page=wanted&id=<?= $tdata['themoviedb_id'] ?>&type=<?= $tdata['type'] ?>"><?= $tdata['L_WANTED'] ?></a>
                    <?php } ?>
                    <?php if (!empty($tdata['reidentify'])) { ?>
                        <a class="action_link" href="?page=identify&identify=<?= $tdata['id'] ?>&media_type=<?= $tdata['type'] ?>"><?= $tdata['L_IDENTIFY'] ?></a>
                    <?php } ?>
                    <?php if (!empty($tdata['deletereg'])) { ?>
                        <a class="action_link" href="?page=view&id=<?= $tdata['id'] ?>&type=<?= $tdata['page_type'] ?>&deletereg=1" onclick="return confirm('Are you sure?')" ><?= $tdata['L_DELETE_REGISTER'] ?></a>
                    <?php } ?>
                    <?php if ($tdata['ilink'] == 'shows_library') { ?>
                        <a class="action_link" href="?page=view&id=<?= $tdata['id'] ?>&type=shows_library&update=1"><?= $tdata['L_UPDATE_EPISODES'] ?></a>
                    <?php } ?>
                    <?php if ($tdata['ilink'] == 'movies_library') { ?>
                        <a class="action_link" href="?page=download&id=<?= $tdata['id'] ?>&type=movies_library" target=_blank><?= $tdata['L_DOWNLOAD'] ?></a>
                    <?php } ?>
                </div>
                <hr/>
                <?php if (!empty($tdata['download'])) { ?>
                    <div class="view_download">
                        <a class="submit_link" href="<?= basename($_SERVER['REQUEST_URI']) . '&download=' . rawurlencode($tdata['download']) ?>"><?= $tdata['L_DOWNLOAD'] ?></a>
                    </div>
                <?php } ?>
                <div class="view_extra">
                    <?= $tdata['extra'] ?>
                </div>
            </div>
        </div>
    </div>
</div>