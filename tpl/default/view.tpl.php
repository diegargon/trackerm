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
            <img class="view_poster" src="<?= $tdata['poster'] ?>" alt=""/>
        </div>

        <div class="view_description_container">
            <h2><?= $tdata['title'] ?></h2>
            <div class="view_plot">
                <p><?= $tdata['plot'] ?></p>
            </div>
            <div class="">
                <?php if (!empty($tdata['added'])) { ?>
                    <span>Añadido:</span>
                    <span class="view_added"><?= date("d-m-y", $tdata['added']) ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['release'])) { ?>
                    <span>Release:</span>
                    <span class="view_release"><?= $tdata['release'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['size'])) { ?>
                    <span>Size:</span>
                    <span class="view_size"><?= human_filesize($tdata['size']) ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['rating'])) { ?>
                    <span>Rating:</span>
                    <span class="view_rating"><?= $tdata['rating'] ?></span>
                    <br/>
                <?php } ?>
                <?php if (!empty($tdata['popularity'])) { ?>
                    <span>Popularity:</span>
                    <span class="view_popularity"><?= $tdata['popularity'] ?></span>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="view_extra">
        <?= $tdata['extra'] ?>
    </div>
</div>