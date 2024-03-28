<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
?>
<h2><a href="https://themoviedb.org" target=_blank>The Movie Database</a></h2>
<div class="moviedb_container">
    <div class="search_container">
        <form method="GET" action="">
            <input type="hidden" name="page" value="<?= $tdata['page'] ?>">
            <?php if ($cfg['want_movies']) { ?>
                <div class="search_tag"><?= $LNG['L_MOVIE'] ?>:</div><div class="search_box"><input type="text" name="search_movies" value="<?= $tdata['search_movies'] ?>"></div>
            <?php } ?>
            <?php if ($cfg['want_shows']) { ?>
                <div class="search_tag"><?= $LNG['L_SHOW'] ?>:</div><div class="search_box"><input type="text" name="search_shows" value="<?= $tdata['search_shows'] ?>" ></div>
            <?php } ?>

            <div class="search_btn inline"><input onClick="show_loading()"  class="submit_btn" type="submit"  value="<?= $LNG['L_SEARCH'] ?>"></div>
        </form>
    </div>
</div>