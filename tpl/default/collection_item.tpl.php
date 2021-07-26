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
        <a onClick="show_loading();" href="?page=view_group&group_type=<?= $tdata['group_type'] ?>&id=<?= $tdata['id'] ?>">
            <img class="img_poster_preview"  alt="" src="<?= $tdata['poster'] ?>"/>
        </a>
        <div class="overlay_nfiles">
            <div class="nfiles">
                <?= $tdata['total_items']; ?>
            </div>
        </div>
    </div>
    <div class="item_details">
        <div class="item_title"><?= $tdata['title'] ?></div>
    </div>
</div>
