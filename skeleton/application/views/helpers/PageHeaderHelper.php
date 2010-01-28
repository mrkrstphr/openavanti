<?php

class PageHeaderHelper extends ViewHelper
{

    public function render($title, $caption, $link, $icon)
    {
    ?>
    
<div class="box-title"> 
    <h2 class="left"><?php echo $title; ?></h2>
    
    <span class="action-link right">
        <a href="<?php echo $link; ?>" title="<?php echo $caption; ?>">
            <img alt="<?php echo $caption; ?>" src="<?php echo $icon; ?>" title="<?php echo $caption; ?>" /> </a>
    
        <a href="<?php echo $link; ?>" title="<?php echo $caption; ?>">
            <?php echo $caption; ?></a>
    </span>
    
    <br class="clear" />
</div>

    <?php
    }

}

?>
