<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<h2><a href="https://themoviedb.org" target=_blank>The Movie Database</a></h2>
<div class="moviedb_container">
    <div class="search_container">
        <form method="GET" >
            <?php if ($cfg['want_movies']) { ?>
                <div class="search_tag"><?= $LNG['L_MOVIE'] ?>:</div><div class="search_box"><input type="text" name="search_movies" value="<?= $tdata['search_movies_word'] ?>"></div>
            <?php } ?>
            <?php if ($cfg['want_shows']) { ?>
                <div class="search_tag"><?= $LNG['L_SHOW'] ?>:</div><div class="search_box"><input type="text" name="search_shows" value="<?= $tdata['search_shows_word'] ?>" ></div>
            <?php } ?>
            <input type="hidden" name="page" value="<?= $_GET['page'] ?>">
            <div class="search_btn"><input onClick="show_loading()"  class="submit_btn" type="submit"  value="<?= $LNG['L_SEARCH'] ?>"></div>
        </form>
    </div>
    <div class="">
        <form class="form_inline" method="POST" >
            <label for="show_trending"><?= $LNG['L_SHOW_TRENDING'] ?> </label>
            <input  type="hidden" name="show_trending" value="0"/>
            <input id="show_trending" <?= $tdata['TRENDING_CHECKED'] ?> onChange="this.form.submit()" type="checkbox" name="show_trending" value="1"/>
        </form>
        <form class="form_inline" method="POST" >
            <label for="show_popular"><?= $LNG['L_SHOW_POPULAR'] ?> </label>
            <input  type="hidden" name="show_popular" value="0"/>
            <input id="show_popular" <?= $tdata['POPULAR_CHECKED'] ?> onChange="this.form.submit()" type="checkbox" name="show_popular" value="1"/>
        </form>
        <form class="form_inline" method="POST" >
            <label for="show_today_shows"><?= $LNG['L_TODAY_SHOWS'] ?> </label>
            <input  type="hidden" name="show_today_shows" value="0"/>
            <input id="show_today_shows" <?= $tdata['TODAYSHOWS_CHECKED'] ?> onChange="this.form.submit()" type="checkbox" name="show_today_shows" value="1"/>
        </form>
    </div>
</div>