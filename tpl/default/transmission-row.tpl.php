<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="tor_row_container">
    <form method="POST" >
        <div class="tor_download">
            <?php if ($tdata['percent'] == 100) { ?>
                <div class="tor_name"><?= $tdata['name'] ?> </div>
            <?php } else { ?>
                <div class="tor_name_downloading"><?= $tdata['name'] ?> </div>
            <?php } ?>
        </div>
        <div class="tor_tags">
            <div class="tor_action">
                <?php if ($tdata['show_start']) { ?>
                    <input type="submit" class="submit_btn" name="start" value="<?= $LNG['L_START'] ?>"/>
                    <?php
                }
                if ($tdata['show_stop']) {
                    ?>
                    <input type="submit" class="submit_btn" name="stop" value="<?= $LNG['L_STOP'] ?>"/>
                <?php } ?>
                <input type="submit" class="submit_btn" name="delete" value="<?= $LNG['L_DELETE'] ?>" <?= "onclick=\"return confirm('sure?');\"" ?> />
                <input type="hidden" name="tid[]" value="<?= $tdata['id'] ?>"/>
            </div>
            <div class="tor_tag"><?= $tdata['id'] ?></div>

            <div class="tor_tag"><?= $LNG['L_COMPLETED'] . ': ' . $tdata['percent'] ?>%</div>
            <div class="tor_tag"><?= $LNG['L_STATUS'] . ': ' . $tdata['status_name'] ?> </div>
            <div class="tor_tag"><?= $LNG['L_DESTINATION'] . ': ' . $tdata['downloadDir'] ?> </div>
        </div>
    </form>
</div>
