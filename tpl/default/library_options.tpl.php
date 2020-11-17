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
    <form method="GET" action="">
        <input type="text" name="search_text" value="TODO" />
        <input type="hidden" name="page" value="<?= $_GET['page'] ?>">
        <input class="submit_btn"  type="submit" value="<?= $tdata['L_SEARCH'] ?>"/>
    </form>
    <form method="post">
        <?= $tdata['L_IDENTIFY'] ?>:
        <select name="num_id_show" onchange="this.form.submit()">
            <option <?= $tdata['max_id_sel_0'] ?> value="0">0</option>
            <option <?= $tdata['max_id_sel_5'] ?> value="5">5</option>
            <option <?= $tdata['max_id_sel_10'] ?> value="10">10</option>
            <option <?= $tdata['max_id_sel_20'] ?> value="20">20</option>
            <option <?= $tdata['max_id_sel_50'] ?> value="50">50</option>
        </select>
        <?= $tdata['L_COLUMNS'] ?>:
        <select name="num_columns_results" onchange="this.form.submit()">
            <option <?= $tdata['max_columns_sel_none'] ?> id="default"><?= $tdata['L_DEFAULT'] ?></option>
            <option <?= $tdata['max_columns_sel_1'] ?> value="1">1</option>
            <option <?= $tdata['max_columns_sel_2'] ?> value="2">2</option>
            <option <?= $tdata['max_columns_sel_4'] ?> value="4">4</option>
            <option <?= $tdata['max_columns_sel_6'] ?> value="6">6</option>
            <option <?= $tdata['max_columns_sel_8'] ?> value="8">8</option>
            <option <?= $tdata['max_columns_sel_10'] ?> value="10">10</option>
        </select>
        <?= $tdata['L_ROWS'] ?>:
        <select name="num_rows_results" onchange="this.form.submit()">
            <option <?= $tdata['max_rows_sel_none'] ?> value="<?= $tdata['L_DEFAULT'] ?>"><?= $tdata['L_DEFAULT'] ?></option>
            <option <?= $tdata['max_rows_sel_1'] ?> value="1">1</option>
            <option <?= $tdata['max_rows_sel_2'] ?> value="2">2</option>
            <option <?= $tdata['max_rows_sel_4'] ?> value="4">4</option>
            <option <?= $tdata['max_rows_sel_6'] ?> value="6">6</option>
            <option <?= $tdata['max_rows_sel_8'] ?> value="8">8</option>
            <option <?= $tdata['max_rows_sel_10'] ?> value="10">10</option>
        </select>
        <input class="submit_btn" type="submit" name="rebuild_movies" value="<?= $tdata['L_REBUILD_MOVIES'] ?>"/>
        <input class="submit_btn" type="submit" name="rebuild_shows" value="<?= $tdata['L_REBUILD_SHOWS'] ?>"/>
    </form>
</div>