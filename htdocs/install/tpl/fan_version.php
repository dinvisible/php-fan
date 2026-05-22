<?php declare(strict_types=1); ?><h2>{#RESULT_INFO}</h2>
<section class="test result">
    <h3>{#YOUR_PHP_FAN}</h3>
    <div>
        Version: <b><?php echo $fanVer; ?></b>
    </div>

    <?php if (!empty($logViewer)) : ?>
        <div>
            {#SEE_LOG_VIEWER}: <a href="<?php echo $logViewer; ?>" target="_blank"><?php echo $logViewer; ?></a>.
        </div>
    <?php endif ?>

    <div>
        {#DO_NOT_FORGET_REMOVE_INSTALL}.
    </div>
</section>
