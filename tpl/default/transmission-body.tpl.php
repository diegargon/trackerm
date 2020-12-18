<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="tor_container">
    <form method="POST" action="">
        <div class="tor_general_options">
            <input type="submit" class="submit_btn" name="start_all" value="<?= $tdata['L_START_ALL'] ?>">
            <input type="submit" class="submit_btn" name="stop_all" value="<?= $tdata['L_STOP_ALL'] ?>">
        </div>
    </form>
    <?= $tdata['body'] ?>
</div>