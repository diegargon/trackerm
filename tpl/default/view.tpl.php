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
                <?php if (!empty($tdata['wanted'])) { ?>
                    <div><a class="action_link" href="?page=wanted&id=<?= $tdata['themoviedb_id'] ?>&type=<?= $tdata['type'] ?>"><?= $tdata['L_WANTED'] ?></a></div>
                <?php } ?>
                <?php if (!empty($tdata['reidentify'])) { ?>
                    <div><a class="action_link" href="?page=identify&identify=<?= $tdata['id'] ?>&media_type=<?= $tdata['type'] ?>"><?= $tdata['L_IDENTIFY'] ?></a></div>
                <?php } ?>
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