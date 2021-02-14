<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="menu_options">
    <!--
    <form method="GET" action="">
        <input type="text" name="search_text" value="TODO" />
        <input type="hidden" name="page" value="<?= $_GET['page'] ?>">
        <input class="submit_btn"  type="submit" value="<?= $tdata['L_SEARCH'] ?>"/>
    </form>
    -->
    <form method="post" action="?page=<?= $tdata['page'] ?>">
        <?php if ($tdata['page'] != 'wanted') { ?>
            <?= $tdata['L_IDENTIFY'] ?>:
            <select name="num_ident_toshow" onchange="this.form.submit()">
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
            <?php if ($tdata['want_movies'] && ($tdata['page'] == 'library' || $tdata['page'] == 'library_movies')) { ?>
                <input class="submit_btn" type="submit" name="rebuild_movies" value="<?= $tdata['L_REESCAN_MOVIES'] ?>"/>
                <?php
            }
            if ($tdata['want_shows'] && ($tdata['page'] == 'library' || $tdata['page'] == 'library_shows')) {
                ?>
                <input class="submit_btn" type="submit" name="rebuild_shows" value="<?= $tdata['L_REESCAN_SHOWS'] ?>"/>
            <?php } ?>
        <?php } ?>
        <?php if (!empty($tdata['page']) && in_array($tdata['page'], ['news', 'new_movies', 'new_shows'])) { ?>
            <span> <?= $tdata['L_FILTER_INDEXER'] ?>:</span>
            <select name="sel_indexer" onchange="this.form.submit()">
                <?= $tdata['sel_indexers'] ?>
            </select>
            <div class="ignore_search">
                <!-- ignore words -->
                <span><?= $tdata['L_IGNORE'] ?></span>
                <input type="hidden" name="new_ignore_words_enable" value="0"/>
                <input type="checkbox" <?= !empty($tdata['new_ignore_words_enable']) ? 'checked' : null ?> name="new_ignore_words_enable" onchange="this.form.submit()" value="1"/>

                <div class="inline" data-tip="<?= $tdata['L_TIP_COMMA'] ?>">
                    <input type="text" size="15" name="new_ignore_keywords" onchange="this.form.submit()" value="<?= !empty($tdata['new_ignore_keywords']) ? $tdata['new_ignore_keywords'] : null ?>"/>
                </div>
                <!-- ignore size -->
                <span><?= $tdata['L_SIZE'] ?></span>
                <input type="hidden" name="new_ignore_size_enable" value="0"/>
                <input type="checkbox" <?= !empty($tdata['new_ignore_size_enable']) ? 'checked' : null ?> name="new_ignore_size_enable" onchange="this.form.submit()" value="1"/>
                <div class="inline" data-tip="<?= $tdata['L_TIP_IGNORE_SIZE'] ?>">
                    <input type="text" size="2"  name="new_ignore_size" onchange="this.form.submit()" value="<?= !empty($tdata['new_ignore_size']) ? $tdata['new_ignore_size'] : null ?>"/>
                </div>
            </div>
        <?php } ?>
    </form>
</div>