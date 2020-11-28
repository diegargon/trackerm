<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>
<div class="info_container">
    <div class="info_column">
        <?php
        foreach ($tdata['col1'] as $item) {
            echo $item;
        }
        ?>
    </div>
    <div class="info_column">
        <?php
        foreach ($tdata['col2'] as $item) {
            echo $item;
        }
        ?>
    </div>
</div>