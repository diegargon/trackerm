<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
?>

<h2><?= $tdata['head'] ?></h2>
<form method="post">
    <div class="divTableID">
        <div class="divTableRow">
            <div class="divTableCellID">
                <?= $tdata['L_TITLE'] ?>
            </div>
            <div class="divTableCellID">
                <?= $tdata['predictible_title'] ?>
            </div>
        </div>
        <div class="divTableRow">
            <div class="divTableCellID">
                <?= $tdata['L_FILENAME'] ?>
            </div>
            <div class="divTableCellID">
                <?= $tdata['file_name'] ?>
            </div>
        </div>
        <div class="divTableRow">
            <div class="divTableCellID">
                <?= $tdata['L_PATH'] ?>
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
    <input type="text" name="submit_title" value="<?= $tdata['search_title'] ?>" />
    <input class="submit_btn" type="submit" name="search" value="<?= $tdata['L_SEARCH'] ?>"/>
    <?php
    if (!empty($tdata['select'])) {
        ?>
        <input class="submit_btn" type="submit" name="identify" value="<?= $tdata['L_IDENTIFY'] ?>"/>
    <?php } ?>
    <div>
        <?= !empty($tdata['select']) ? $tdata['select'] : null ?>
    </div>
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

