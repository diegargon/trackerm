<?php
/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>
<div class="library_options">
    <form method="post">
        <input class="submit_btn" type="submit" name="rebuild_movies" value="<?= $tdata['L_REBUILD_MOVIES'] ?>"/>
        <input class="submit_btn" type="submit" name="rebuild_shows" value="<?= $tdata['L_REBUILD_SHOWS'] ?>"/>
        <input type="text" name="search_text" value="TODO" />
        <input class="submit_btn"  type="submit" name="search_biblio" value="<?= $tdata['L_SEARCH'] ?> "/>
        <?= $tdata['L_ID_DISPLAY'] ?>:
        <select name="num_id_show" onchange="this.form.submit()">
            <option <?= $tdata['max_id_sel_10'] ?> id="10">10</option>
            <option <?= $tdata['max_id_sel_20'] ?> id="20">20</option>
            <option <?= $tdata['max_id_sel_50'] ?> id="50">50</option>
        </select>
        <?= $tdata['L_ROWS'] ?>:
        <select name="num_results" onchange="this.form.submit()">
            <option <?= $tdata['max_results_sel_none'] ?> id="default"><?= $tdata['L_DEFAULT'] ?></option>
            <option <?= $tdata['max_results_sel_2'] ?> id="2">2</option>
            <option <?= $tdata['max_results_sel_4'] ?> id="4">4</option>
            <option <?= $tdata['max_results_sel_8'] ?> id="8">8</option>
            <option <?= $tdata['max_results_sel_10'] ?> id="10">10</option>
        </select>

    </form>
</div>
