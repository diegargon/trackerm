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
        <span class="tor_title"><?= $tdata['title'] ?></span>
        <?php if (!empty($tdata['source'])) { ?>
            <span class="tor_source_link">[<a href="<?= $tdata['guid'] ?>" target=_blank ><?= $tdata['source'] ?></a>]</span>
        <?php } ?>
        <span class="info_size"><?= isset($tdata['hsize']) ? $tdata['hsize'] : null ?></span>
    </div>
</div>
