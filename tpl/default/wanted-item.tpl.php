<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);
?>

<?php
if (isset($tdata['tpl_var_ary_first'])) {
    ?>
    <div class="wanted_item_separator">
        <span class=""><?= $tdata['tpl_head'] ?></span>
        <?php
    }
    ?>
    <div class="wanted_item_container">
        <?php if ($prefs->getPrefsItem('expand_all')) { ?>
            <div class="wanted_item_expanded">
            <?php } else { ?>
                <div class="wanted_item_hidden">
                <?php } ?>
                <div class="wanted_poster"><img alt="poster" src="<?= $tdata['poster'] ?>"></div>
            </div>

            <div class="wanted_item_minimal">
                <div class="tag_type"><?= $tdata['lang_media_type'] ?></div>
                <span class="wanted_item_part_hidden"><?= $LNG['L_CHECKDAY'] ?></span>
                <div class="tag_day">
                    <form class="form_inline" method="POST" >
                        <select onchange="this.form.submit()" <?= $tdata['wanted_status'] != -1 ? ' disabled ' : null; ?> name="check_day[<?= $tdata['id'] ?>]">
                            <option <?= $tdata['day_check'] == -1 ? ' selected ' : null ?>value="-1"><?= $LNG['L_NEVER'] ?></option>
                            <option <?= $tdata['day_check'] == 0 ? ' selected ' : null ?>value="0"><?= $LNG['L_DAY_ALL'] ?></option>
                            <option <?= $tdata['day_check'] == 1 ? ' selected ' : null ?>value="1"><?= $LNG['L_DAY_MON'] ?></option>
                            <option <?= $tdata['day_check'] == 2 ? ' selected ' : null ?> value="2"><?= $LNG['L_DAY_TUE'] ?></option>
                            <option <?= $tdata['day_check'] == 3 ? ' selected ' : null ?>value="3"><?= $LNG['L_DAY_WED'] ?></option>
                            <option <?= $tdata['day_check'] == 4 ? ' selected ' : null ?>value="4"><?= $LNG['L_DAY_THU'] ?></option>
                            <option <?= $tdata['day_check'] == 5 ? ' selected ' : null ?>value="5"><?= $LNG['L_DAY_FRI'] ?></option>
                            <option <?= $tdata['day_check'] == 6 ? ' selected ' : null ?>value="6"><?= $LNG['L_DAY_SAT'] ?></option>
                            <option <?= $tdata['day_check'] == 7 ? ' selected ' : null ?>value="7"><?= $LNG['L_DAY_SUN'] ?></option>
                        </select>
                    </form>
                </div>
                <div class="tag_title"><a onClick="show_loading();" class="wanted_link" href="<?= $tdata['link'] ?>"><?= $tdata['link_name'] ?></a></div>
            </div>

            <!-- HIDDE -->
            <?php if ($prefs->getPrefsItem('expand_all')) { ?>
                <div class="wanted_item_expanded">
                <?php } else { ?>
                    <div class="wanted_item_hidden">
                    <?php } ?>
                    <div class="tag_state"><?= $tdata['status_name'] ?></div>
                    <div class="tag_added"><?= $LNG['L_ADDED'] ?>: <?= $tdata['created'] ?></div>
                    <div class="tag_check"><?= $LNG['L_CHECKED'] ?>: <?= $tdata['last_check'] ?></div>
                    <br/>
                    <span><?= $LNG['L_ALT_WANTED_TITLE'] ?></span>
                    <div class="wanted_tip" data-tip="<?= $LNG['L_TIP_FIX_TITLE'] ?>">
                        <div class="tag_custom_title"><form class="form_wanted" method="post" >
                                <input class="wanted_title_input" name="custom_title[<?= $tdata['id'] ?>]"  type="text" onChange="this.form.submit()" value="<?= !empty($tdata['custom_title']) ? $tdata['custom_title'] : $tdata['title'] ?>"/></form>
                        </div>
                    </div>
                    <br/>
                    <div class="wanted_tip" data-tip="<?= $LNG['L_TIP_COMMA'] ?>">
                        <span><?= $LNG['L_IGNORE'] ?>:&nbsp;</span>
                        <div class="tag_input"><form class="form_wanted" method="post" ><input class="wanted_input_red" name="ignore_tags[<?= $tdata['id'] ?>]" type="text" onChange="this.form.submit()" value="<?= !empty($tdata['custom_words_ignore']) ? $tdata['custom_words_ignore'] : null ?>"/></form></div>
                    </div>

                    <div class="wanted_tip" data-tip="<?= $LNG['L_TIP_COMMA'] ?>">
                        <div class="tag_input"><form class="form_wanted" method="post" >
                                <span><?= $LNG['L_REQUIRE'] ?>:&nbsp;</span>
                                <input class="wanted_input" name="require_tags[<?= $tdata['id'] ?>]"  type="text" onChange="this.form.submit()" value="<?= !empty($tdata['custom_words_require']) ? $tdata['custom_words_require'] : null ?>"/>
                            </form>
                        </div>
                    </div>
                    <br/>
                    <span><?= $LNG['L_NOCOUNT'] ?>:</span>
                    <div class="wanted_tip" data-tip="<?= $LNG['L_NOCOUNT'] ?>">
                        <div class="tag_nocount">
                            <form class="form_wanted" method="POST" >
                                <input type="hidden" name="nocount[<?= $tdata['id'] ?>]" value="0">
                                <input  <?= !empty($tdata['ignore_count']) ? "checked" : null ?> onChange="this.form.submit()" type="checkbox" name="nocount[<?= $tdata['id'] ?>]]" value="1">
                            </form>
                        </div>
                    </div>
                    <span><?= $LNG['L_ONLY_PROPER'] ?>:</span>
                    <div class="wanted_tip" data-tip="<?= $LNG['L_ONLY_PROPER'] ?>">
                        <div class="tag_proper">
                            <form class="form_wanted" method="POST" >
                                <input type="hidden" name="only_proper[<?= $tdata['id'] ?>]" value="0">
                                <input  <?= !empty($tdata['only_proper']) ? "checked" : null ?> onChange="this.form.submit()" type="checkbox" name="only_proper[<?= $tdata['id'] ?>]]" value="1">
                            </form>
                        </div>
                    </div>
                    <div class="tag_id">
                        <?php if (!empty($tdata['elink'])) { ?>
                            <a href="<?= $tdata['elink'] ?>" target="_blank">TMDB</a>
                            <?php
                        }
                        ?>
                    </div>
                    <br/>
                    <a href="<?= $tdata['iurl'] . '&delete=' . $tdata['id'] ?>" class="action_link"><?= $LNG['L_DELETE'] ?></a>
                </div>
            </div><!-- wanted_item_container -->
            <?php
            if (isset($tdata['tpl_var_ary_last'])) {
                ?>
            </div>
            <?php
        }
        ?>