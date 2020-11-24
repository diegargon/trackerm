<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>
<form method="POST" action="">
    <br/>
    <div class="tor_download">
        <div class="tor_name"><?= $tdata['name'] ?> </div>
    </div>
    <div class="tor_tags">
        <div class="tor_action">
            <input type="submit" class="submit_btn" name="start" value="<?= $tdata['L_START'] ?>"/>
            <input type="submit" class="submit_btn" name="stop" value="<?= $tdata['L_STOP'] ?>"/>
            <input type="submit" class="submit_btn" name="delete" value="<?= $tdata['L_DELETE'] ?>" <?= "onclick=\"return confirm('sure?');\"" ?> />
            <input type="hidden" name="tid[]" value="<?= $tdata['id'] ?>"/>
        </div>
        <div class="tor_tag"><?= $tdata['id'] ?></div>
        <?= $tdata['percentDone'] == 1 ? $percentDone = '100' : $percentDone = (float) $tdata['percentDone']; ?>
        <div class="tor_tag"><?= $tdata['L_COMPLETED'] . ': ' . $percentDone ?>%</div>
        <div class="tor_tag"><?= $tdata['L_STATUS'] . ': ' . $tdata[$tdata['status']] ?> </div>
        <div class="tor_tag"><?= $tdata['L_DESTINATION'] . ': ' . $tdata['downloadDir'] ?> </div>
    </div>
</form>