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
        <a  href="&id=<?= $tdata['id'] ?>">
            <img class="img_poster_preview"  alt="" src="<?= !empty($tdata['poster']) ? $tdata['poster'] : null ?>"/>
        </a>
        <div class="overlay_nfiles">
            <div class="nfiles">
                <?= !empty($tdata['total_items']) ? $tdata['total_items'] : null ?>
            </div>
        </div>
    </div>
    <div class="item_details">
        <div class="item_title"><?= $tdata['title'] ?></div>
    </div>
</div>


