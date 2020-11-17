<?php
/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<div class="wanted_page">
    <?php if (!empty($tdata['title']) && !empty($tdata['tag_type'])) { ?>

        <h2><?= $tdata['L_WANTED_ADD'] ?></h2>
        <div class="wanted_add_container">
            <form method="POST" action="">
                <span class="wanted_title"><?= $tdata['title'] ?></span>
                <?= $tdata['tag_type'] . $tdata['tags_quality'] . $tdata['tags_ignore'] ?>
                <select name="check_day">
                    <option value="L_DAY_ALL"><?= $tdata['L_DAY_ALL'] ?></option>
                    <option value="L_DAY_MON"><?= $tdata['L_DAY_MON'] ?></option>
                    <option value="L_DAY_TUE"><?= $tdata['L_DAY_TUE'] ?></option>
                    <option value="L_DAY_WED"><?= $tdata['L_DAY_WED'] ?></option>
                    <option value="L_DAY_THU"><?= $tdata['L_DAY_THU'] ?></option>
                    <option value="L_DAY_FRI"><?= $tdata['L_DAY_FRI'] ?></option>
                    <option value="L_DAY_SAT"><?= $tdata['L_DAY_SAT'] ?></option>
                    <option value="L_DAY_SUN"><?= $tdata['L_DAY_SUN'] ?></option>
                </select>
                <input type="submit" name="submit_wanted" value="<?= $tdata['L_ADD'] ?>" />
            </form>
        </div>
    <?php } ?>

    <div class="wanted_details">
        <?= isset($tdata['wanted_item']) ? $tdata['wanted_item'] : null ?>
    </div>
    <div class="wanted_list">
        <?= isset($tdata['wanted_list']) ? $tdata['wanted_list'] : null ?>
    </div>
</div>