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

    <?php if (in_array($tdata['page'], ['wanted'])) { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <span><?= $LNG['L_EXPAND_ALL'] ?></span>
            <input type="hidden" name="expand_all" value="0"/>
            <input onClick="show_loading();" type="checkbox" <?= !empty($prefs->getPrefsItem('expand_all')) ? 'checked' : null ?> name="expand_all" onChange="this.form.submit()" value="1"/>
        </form>
    <?php } ?>

    <?php if (in_array($tdata['page'], ['library', 'library_movies', 'library_shows'])) { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <div class="inline" data-tip="<?= $LNG['L_VIEW_MODE'] ?>">
                <label class="switch">
                    <input type="hidden" name="view_mode" value="0"/>
                    <input type="checkbox" name="view_mode"  <?= !empty($prefs->getPrefsItem('view_mode')) ? 'checked' : null ?>  onChange="show_loading();this.form.submit();"/>
                    <span class="slider round"></span>
                </label>
            </div>
        </form>

        <?= $LNG['L_IDENTIFY'] ?>:
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <select class="num_ident_toshow" name="num_ident_toshow" onChange="show_loading();this.form.submit();">
                <option <?= $tdata['max_id_sel_0'] ?> value="0">0</option>
                <option <?= $tdata['max_id_sel_5'] ?> value="5">5</option>
                <option <?= $tdata['max_id_sel_10'] ?> value="10">10</option>
                <option <?= $tdata['max_id_sel_20'] ?> value="20">20</option>
                <option <?= $tdata['max_id_sel_50'] ?> value="50">50</option>
            </select>
        </form>
    <?php } ?>

    <?php if (!in_array($tdata['page'], ['wanted', 'transmission', 'index', 'view', 'view_group', 'view_genres'])) { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <span class="html_ico">&#8741;</span>
            <select class="num_columns_results" name="num_columns_results" onChange="show_loading();this.form.submit();">
                <option <?= $tdata['max_columns_sel_none'] ?> id="default"><?= $LNG['L_DEFAULT'] ?></option>
                <option <?= $tdata['max_columns_sel_1'] ?> value="1">1</option>
                <option <?= $tdata['max_columns_sel_2'] ?> value="2">2</option>
                <option <?= $tdata['max_columns_sel_4'] ?> value="4">4</option>
                <option <?= $tdata['max_columns_sel_6'] ?> value="6">6</option>
                <option <?= $tdata['max_columns_sel_8'] ?> value="8">8</option>
                <option <?= $tdata['max_columns_sel_10'] ?> value="10">10</option>
            </select>
            <span class="html_ico">&#9868;</span>
            <select class="num_rows_results" name="num_rows_results" onChange="show_loading();this.form.submit();">
                <option <?= $tdata['max_rows_sel_none'] ?> value="<?= $LNG['L_DEFAULT'] ?>"><?= $LNG['L_DEFAULT'] ?></option>
                <option <?= $tdata['max_rows_sel_1'] ?> value="1">1</option>
                <option <?= $tdata['max_rows_sel_2'] ?> value="2">2</option>
                <option <?= $tdata['max_rows_sel_4'] ?> value="4">4</option>
                <option <?= $tdata['max_rows_sel_6'] ?> value="6">6</option>
                <option <?= $tdata['max_rows_sel_8'] ?> value="8">8</option>
                <option <?= $tdata['max_rows_sel_10'] ?> value="10">10</option>
                <option <?= $tdata['max_rows_sel_25'] ?> value="25">25</option>
                <option <?= $tdata['max_rows_sel_50'] ?> value="50">50</option>
            </select>
        </form>
    <?php } ?>
    <?php if (in_array($tdata['page'], ['library', 'library_movies'])) { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <label for="show_collections"><?= $LNG['L_SHOW_COLLECTIONS'] ?> </label>
            <input type="hidden" name="show_collections" value="0"/>
            <input id="show_collections" type="checkbox" name="show_collections"  <?= !empty($prefs->getPrefsItem('show_collections')) ? 'checked' : null ?>  onChange="show_loading();this.form.submit();"/>
        </form>
    <?php } ?>
    <?php if (in_array($tdata['page'], ['library', 'library_movies', 'library_shows'])) { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <label for="show_genres"><?= $LNG['L_GENRES'] ?> </label>
            <input type="hidden" name="show_genres" value="0"/>
            <input id="show_genres" type="checkbox" name="show_genres"  <?= !empty($prefs->getPrefsItem('show_genres')) ? 'checked' : null ?>  onChange="show_loading();this.form.submit();"/>
        </form>
    <?php } ?>
    <?php if ($cfg['want_movies'] && ($tdata['page'] == 'library' || $tdata['page'] == 'library_movies')) { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <input onClick="show_loading();" class="submit_btn" type="submit" name="rebuild_movies" value="<?= $LNG['L_REESCAN_MOVIES'] ?>"/>
        </form>
        <?php
    }

    if ($cfg['want_shows'] && ($tdata['page'] == 'library' || $tdata['page'] == 'library_shows')) {
        ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <input onClick="show_loading();" class="submit_btn" type="submit" name="rebuild_shows" value="<?= $LNG['L_REESCAN_SHOWS'] ?>"/>
        </form>
    <?php } ?>

    <?php if (in_array($tdata['page'], ['tmdb'])) { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <div class="inline">
                <label for="show_trending"><?= $LNG['L_SHOW_TRENDING'] ?> </label>
                <input  type="hidden" name="show_trending" value="0"/>
                <input id="show_trending" <?= !empty($prefs->getPrefsItem('show_trending')) ? 'checked' : null ?>  onChange="show_loading();this.form.submit()" type="checkbox" name="show_trending" value="1"/>
                <label for="show_popular"><?= $LNG['L_SHOW_POPULAR'] ?> </label>
                <input  type="hidden" name="show_popular" value="0"/>
                <input id="show_popular" <?= !empty($prefs->getPrefsItem('show_popular')) ? 'checked' : null ?> onChange="show_loading();this.form.submit()" type="checkbox" name="show_popular" value="1"/>
                <label for="show_today_shows"><?= $LNG['L_TODAY_SHOWS'] ?> </label>
                <input  type="hidden" name="show_today_shows" value="0"/>
                <input id="show_today_shows"  <?= !empty($prefs->getPrefsItem('show_today_shows')) ? 'checked' : null ?> onChange="show_loading();this.form.submit()" type="checkbox" name="show_today_shows" value="1"/>
            </div>
        </form>
    <?php } ?>

    <?php if (in_array($tdata['page'], ['library', 'library_movies', 'library_shows', 'news', 'new_movies', 'new_shows'])) { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <input type="text" size="20"  placeholder="<?= $LNG['L_SEARCH'] ?>" name="search_keyword" onChange="show_loading();this.form.submit();" value="<?= !empty($tdata['search_keyword']) ? $tdata['search_keyword'] : null ?>"/>
        </form>
    <?php } ?>

    <?php if ($tdata['page'] == 'torrents') { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <span><?= $LNG['L_SHOW_CACHED'] ?></span>
            <input type="hidden" name="movies_cached" value="0"/>
            <div class="inline" data-tip="<?= $LNG['L_MOVIES'] ?>">
                <input onClick="show_loading();" type="checkbox" <?= !empty($prefs->getPrefsItem('movies_cached')) ? 'checked' : null ?> name="movies_cached" onChange="this.form.submit()" value="1"/>
            </div>
            <input type="hidden" name="shows_cached" value="0"/>
            <div class="inline" data-tip="<?= $LNG['L_SHOWS'] ?>">
                <input onClick="show_loading();"  type="checkbox" <?= !empty($prefs->getPrefsItem('shows_cached')) ? 'checked' : null ?> name="shows_cached" onChange="this.form.submit()" value="1"/>
            </div>
        </form>
    <?php } ?>

    <?php if (!empty($tdata['page']) && in_array($tdata['page'], ['news', 'new_movies', 'new_shows', 'torrents'])) { ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <span> <?= $LNG['L_FILTER_INDEXER'] ?>:</span>
            <select  name="sel_indexer" onChange="show_loading();this.form.submit();">
                <?= $tdata['sel_indexers'] ?>
            </select>
            <span><?= $LNG['L_FREELECH'] ?></span>
            <input type="hidden" name="only_freelech" value="0"/>
            <input onClick="show_loading();"  type="checkbox" <?= !empty($prefs->getPrefsItem('only_freelech')) ? 'checked' : null ?> name="only_freelech" onChange="this.form.submit()" value="1"/>
        </form>
        <?php
    }

    if (!empty($tdata['page']) && in_array($tdata['page'], ['news', 'new_movies', 'new_shows', 'torrents'])) {
        ?>
        <form method="post" action="?page=<?= $tdata['page'] ?>">
            <div class="ignore_search">
                <!-- ignore words -->
                <span><?= $LNG['L_IGNORE'] ?></span>
                <input type="hidden" name="new_ignore_words_enable" value="0"/>
                <input onClick="show_loading();"  type="checkbox" <?= !empty($prefs->getPrefsItem('new_ignore_words_enable')) ? 'checked' : null ?> name="new_ignore_words_enable" onChange="this.form.submit()" value="1"/>
                <div class="inline" data-tip="<?= $LNG['L_TIP_COMMA'] ?>">
                    <input type="text" size="15" name="new_ignore_keywords" onChange="show_loading();this.form.submit();" value="<?= !empty($prefs->getPrefsItem('new_ignore_keywords')) ? $prefs->getPrefsItem('new_ignore_keywords') : null ?>"/>
                </div>
                <!-- ignore size -->
                <span><?= $LNG['L_SIZE'] ?></span>
                <input type="hidden" name="new_ignore_size_enable" value="0"/>
                <input onClick="show_loading();"  type="checkbox" <?= !empty($prefs->getPrefsItem('new_ignore_size_enable')) ? 'checked' : null ?> name="new_ignore_size_enable" onChange="this.form.submit()" value="1"/>
                <div class="inline" data-tip="<?= $LNG['L_TIP_IGNORE_SIZE'] ?>">
                    <input type="text" size="2" style="font-family:monospace" name="new_ignore_size_min" onChange="show_loading();this.form.submit();" value="<?= $prefs->getPrefsItem('new_ignore_size_min') ? $prefs->getPrefsItem('new_ignore_size_min') : null ?>"/>
                    <input type="text" size="2" style="font-family:monospace" name="new_ignore_size_max" onChange="show_loading();this.form.submit();" value="<?= $prefs->getPrefsItem('new_ignore_size_max') ? $prefs->getPrefsItem('new_ignore_size_max') : null ?>"/>
                </div>
            </div>
        </form>
    <?php } ?>
</div>