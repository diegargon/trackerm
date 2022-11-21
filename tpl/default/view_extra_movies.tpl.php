<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);
?>

<form method="GET">
    <input type="hidden" name="page" value="<?= $tdata['page'] ?>"/>
    <input type="hidden" name="id" value="<?= $tdata['id'] ?>"/>
    <input type="hidden" name="view_type" value="<?= $tdata['view_type'] ?>"/>
    <input onClick="show_loading();" class="submit_btn" type="submit" name="more_movies" value="<?= $LNG['L_SEARCH_MOVIES'] ?>" >
    <input onClick="show_loading();" class="submit_btn" type="submit" name="more_torrents" value="<?= $LNG['L_SHOW_TORRENTS'] ?>" >
    <input type="text" name="search_movies_db" value="<?= $tdata['stitle'] ?>">
</form>
<a href="https://www.themoviedb.org/search?query=<?= $tdata['stitle'] ?>" target="_blank">[TMDB]</a> -
<a href="https://www.imdb.com/find?q=<?= $tdata['stitle'] ?>" target="_blank">[IMDB]</a> -
<a href="https://www.filmaffinity.com/<?= substr($cfg['LANG'], 0, 2) ?>/search.php?stext=<?= $tdata['stitle'] ?>" target="_blank">[FA]</a> -
<a href="https://www.metacritic.com/search/all/<?= $tdata['stitle'] ?>/results" target="_blank">[M]</a> -
<a href="https://www.rottentomatoes.com/search?search=<?= $tdata['stitle'] ?>" target="_blank">[RT]</a> -
<a href="https://www.google.com/search?q=<?= $tdata['stitle'] ?>" target="_blank">[G]</a>
