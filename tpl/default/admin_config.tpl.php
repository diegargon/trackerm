<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
//var_dump($tdata);
//var_dump($tdata['cfg']);

if ($tdata['first']) {
    ?>
    <div class="config_cats">
        <?php
        foreach ($tdata['categories'] as $category) {
            ?>
            <a href="index.php?page=config&category=<?= $category['cat_raw'] ?>"><?= $category['cat_display'] ?></a>
        <?php } ?>
    </div>
    <form method="POST"><input class="submit_btn" type="submit" name="submit_config" value="<?= $LNG['L_SUBMIT'] ?>"/>
        <div class="catTable">
            <?php
        }
//
//REP
//
        foreach ($tdata['cfg'] as $config) {
            $data_row = '';
            ?>
            <div class="catRow border_blue">
                <div class="catCell">
                    <?php
                    if ($config['type'] == 3) {
                        ?>
                        <select name="config_keys[<?= $config['cfg_key'] ?>]">
                            <option <?= $config['selected_no'] ?> value="0"><?= $LNG['L_NO'] ?></option>
                            <option <?= $config['selected_yes'] ?> value="1"><?= $LNG['L_YES'] ?></option>
                        </select>
                        <?php
                        /* TODO: CONFIGSELECT */
                    } else if ($config['type'] == 8) {
                        ?>
                        <select name="config_id[<?= $config['cfg_key'] ?>]">
                            <?php
                            if ($config['cfg_value_array']) {
                                foreach ($config['cfg_value_array'] as $value_key => $value) {
                                    ?><option value="<?= $value_key ?>"><?= $value ?></option><?php
                                }
                            }
                            ?>
                        </select>
                        <input class="action_btn" type="submit" name="config_remove[<?= $config['cfg_key'] ?>]" value="<?= $LNG['L_DELETE'] ?>" />
                        <br/><input size="10" type="text" name="add_item[<?= $config['cfg_key'] ?>]" value="" />
                        <input class="action_btn" type="submit" name="config_add[<?= $config['cfg_key'] ?>]" value="<?= $LNG['L_ADD'] ?>" />
                        <input type="hidden" name="add_before[<?= $config['cfg_key'] ?>]" value="0" />
                        <div class="inline" data-tip="<?= $LNG['L_ADD_BEFORE'] ?>">
                            <input type="checkbox" name="add_before[<?= $config['cfg_key'] ?>]" value="1" />
                        </div>
                        <?php
                    } else {
                        ?><input type="text" name="config_keys[<?= $config['cfg_key'] ?>]" value="<?= $config['cfg_value'] ?>" /><?php
                    }
                    ?>
                </div>
                <div class = "catCell"><?= $config['cfg_desc'] ?></div>
            </div>
            <?php
        }

//
//ENDREP
//
        if ($tdata['last']) {
            ?>
        </div>
    </form>
    <?php
}




