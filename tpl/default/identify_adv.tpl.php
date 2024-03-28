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

<h2><?= $tdata['head'] ?></h2>
<form method="post">
    <div class="divTableID">
        <div class="divTableRow">
            <div class="divTableCellID">
                <?= $LNG['L_TITLE'] ?>
            </div>
            <div class="divTableCellID">
                <?= $tdata['predictible_title'] ?>
            </div>
        </div>
        <div class="divTableRow">
            <div class="divTableCellID">
                <?= $LNG['L_FILENAME'] ?>
            </div>
            <div class="divTableCellID">
                <?= $tdata['file_name'] ?>
            </div>
        </div>
        <div class="divTableRow">
            <div class="divTableCellID">
                <?= $LNG['L_PATH'] ?>
            </div>
            <div class="divTableCellID">
                <?= $tdata['path'] ?>
            </div>
        </div>
        <div class="divTableRow">
            <div class="divTableCellID">

            </div>
            <div class="divTableCellID">
            </div>
        </div>
    </div>
    <br/>
    <a class="submit_btn" href="javascript: history.back()"><?= $LNG['L_BACK'] ?></a>
    <br/>
    <?php
    if (isset($tdata['is_writable']) && !isset($tdata['is_link'])) {
        ?>
        <input size="50" type="text" name="rename_file" value="<?= $tdata['file_name'] ?>" />
        <input class="submit_btn" type="submit" name="rename_file_btn" value="<?= $LNG['L_RENAME'] ?>"/>
        <br/>
        <?php
    } else {
        ?>
        <div><?= isset($tdata['is_link']) ? $LNG['L_RENAME_DISABLE_DUE_LINK'] : $LNG['L_RENAME_DISABLE_DUE_PERMS'] ?></div>
        <?php
    }
    ?>
    <input type="hidden" name="identify_all" value="<?= $tdata['identify_all'] ?>"/>
    <input size="50" type="text" name="submit_title" value="<?= $tdata['search_title'] ?>" />
    <input class="submit_btn" type="submit" name="search" value="<?= $LNG['L_SEARCH'] ?>"/>
    <?php
    if (isset($tdata['media_results']['items'])) {
        ?>
        <input class="submit_btn" type="submit" name="identify" value="<?= $LNG['L_IDENTIFY'] ?>"/>

        <div>
            <select class="ident_select" onChange="this.form.submit()" name=selected[<?= $tdata['media_results']['id'] ?>]>
                <?php
                foreach ($tdata['media_results']['items'] as $item) {
                    ?>
                    <option <?= $item['value'] == $tdata['media_results']['selected'] ? 'selected' : null ?>  value="<?= $item['value'] ?>"> <?= $item['name'] ?></option>
                    <?php
                }
                ?>
            </select>

        </div>
        <?php
    }
    ?>
    <div>
        <?php
        if (!empty($tdata['selected_poster'])) {
            print '<img width="300" src="' . $tdata['selected_poster'] . '" />';
        }

        if (!empty($tdata['selected_plot'])) {
            print '<p>' . $tdata['selected_plot'] . '</p>';
        }
        ?>
    </div>
    <div>

    </div>

</form>

