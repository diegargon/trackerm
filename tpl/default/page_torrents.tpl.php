<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<h2><?= $LNG['L_SEARCHTORRENTS'] ?></h2>
<p><?= $LNG['L_SEARCHTORRENTS_DESC'] ?></p>
<form method="GET" >
    <div class="moviedb_container">
        <?php if ($cfg['want_movies']) { ?>
            <div class="search_tag"><?= $LNG['L_MOVIE'] ?>:</div><div class="search_box"><input type="text" name="search_movies_torrents" value="<?= $tdata['search_movies_word'] ?>"></div>
        <?php } ?>
        <?php if ($cfg['want_shows']) { ?>
            <div class="search_tag"><?= $LNG['L_SHOW'] ?>:</div><div class="search_box"><input type="text" name="search_shows_torrents" value="<?= $tdata['search_shows_word'] ?>"></div>
        <?php } ?>
        <input type="hidden" name="page" value="<?= $_GET['page'] ?>">
        <div class="search_btn"><input onClick="show_loading()" class="submit_btn" type="submit" value="<?= $LNG['L_SEARCH'] ?>"></div>
    </div>
</form>

