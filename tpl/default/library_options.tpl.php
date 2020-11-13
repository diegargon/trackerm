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
        <?= $tdata['L_ID_DISPLAY'] ?>:
        <select name="num_id_show" onchange="this.form.submit()">
            <option <?= $tdata['max_id_sel_0'] ?> id="0">0</option>
            <option <?= $tdata['max_id_sel_5'] ?> id="5">5</option>
            <option <?= $tdata['max_id_sel_10'] ?> id="10">10</option>
            <option <?= $tdata['max_id_sel_20'] ?> id="20">20</option>
            <option <?= $tdata['max_id_sel_50'] ?> id="50">50</option>
        </select>
        <?= $tdata['L_COLUMNS'] ?>:
        <select name="num_columns_results" onchange="this.form.submit()">
            <option <?= $tdata['max_columns_sel_none'] ?> id="default"><?= $tdata['L_DEFAULT'] ?></option>
            <option <?= $tdata['max_columns_sel_1'] ?> id="1">1</option>
            <option <?= $tdata['max_columns_sel_2'] ?> id="2">2</option>
            <option <?= $tdata['max_columns_sel_4'] ?> id="4">4</option>
            <option <?= $tdata['max_columns_sel_6'] ?> id="6">6</option>
            <option <?= $tdata['max_columns_sel_8'] ?> id="8">8</option>
            <option <?= $tdata['max_columns_sel_10'] ?> id="10">10</option>
        </select>
        <?= $tdata['L_ROWS'] ?>:
        <select name="num_rows_results" onchange="this.form.submit()">
            <option <?= $tdata['max_rows_sel_none'] ?> id="default"><?= $tdata['L_DEFAULT'] ?></option>
            <option <?= $tdata['max_rows_sel_1'] ?> id="1">1</option>
            <option <?= $tdata['max_rows_sel_2'] ?> id="2">2</option>
            <option <?= $tdata['max_rows_sel_4'] ?> id="4">4</option>
            <option <?= $tdata['max_rows_sel_6'] ?> id="6">6</option>
            <option <?= $tdata['max_rows_sel_8'] ?> id="8">8</option>
            <option <?= $tdata['max_rows_sel_10'] ?> id="10">10</option>
        </select>
    </form>
</div>
<div class="library_options">
    <form method="GET" action="">
        <input type="text" name="search_text" value="TODO" />
        <input type="hidden" name="page" value="<?= $_GET['page'] ?>">
        <input class="submit_btn"  type="submit" value="<?= $tdata['L_SEARCH'] ?>"/>
    </form>
</div>