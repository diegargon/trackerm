<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 *
 */
?>
<form method="post">
    <input type="submit" class="submit_btn clear_btn" name="clear_state" value="<?= $tdata['clear_title'] ?>"/>
</form>

<?php
foreach ($tdata['msg'] as $msg) {
    ?>
    <div class = "state_msg_block">
        <div class="state_time"><?= $msg['created_frmt'] ?></div>
        <div class="state_msg"><?= $msg['msg'] ?></div>
    </div>
    <?php
}
