<?php declare(strict_types=1); ?><h2>{#CHECK_SYS_DIRECTORIES}</h2>
<section class="test">
    <h3>{#CHECK_BASE_DIRECTORIES}</h3>
    <p>{#ROOT_DIR}: <code class="important"><?php echo $baseDir; ?></code></p>

    <?php if (empty($indexDir)) : ?>
        <p>
            {#INDEX_FILE_NOT_FOUND}<img src="image/incorrect.gif" />
        </p>
    <?php else : ?>
        <p>
            {#INDEX_FILE}: <code><?php echo $indexDir; ?>/index.php</code>
            <?php if ((string)$baseDir === (string)$indexDir) : ?>
                <img src="image/correct.gif" />
            <?php else : ?>
                <br /><img src="image/warning.gif" /> {#INDEX_FILE_NOT_ROOT}.
            <?php endif ?>
        </p>

        <p>
            <?php if ($isCoreDir) : ?>
                {#CORE_DIR}: <code><?php echo $coreDir; ?></code>
                <?php if ($isCoreUnder) : ?>
                    <img src="image/correct.gif" />
                <?php else : ?>
                    <br /><img src="image/warning.gif" /> {#CORE_IS_NOT_UNDER}.
                <?php endif ?>
            <?php else : ?>
                {#CORE_DIR_NOT_FOUND} - <code><?php echo $coreDir; ?></code><img src="image/incorrect.gif" />
            <?php endif ?>
        </p>

        <p>
            <?php if ($isProjectDir) : ?>
                {#PROJECT_DIR}: <code><?php echo $projectDir; ?></code>
                <?php if ($isProjectUnder) : ?>
                    <img src="image/correct.gif" />
                <?php else : ?>
                    <br /><img src="image/warning.gif" /> {#PROJECT_IS_NOT_UNDER}.
                <?php endif ?>
            <?php elseif ($isDefinedProjectDir) : ?>
                {#PROJECT_DIR_INCORRECT_SET} - <code><?php echo $projectDir; ?></code><img src="image/incorrect.gif" />
            <?php else : ?>
                {#PROJECT_DIR_NOT_FOUND} - <code><?php echo $projectDir; ?></code><img src="image/incorrect.gif" />
            <?php endif ?>
        </p>

        <p>
            <?php if (empty($bootstrapConfig)) : ?>
                <img src="image/warning.gif" /> {#BOOTSTRAP_CONFIG_NOT_FOUND}.
            <?php else : ?>
                {#BOOTSTRAP_CONFIG}: <code><?php echo $bootstrapConfig; ?></code><img src="image/correct.gif" />
            <?php endif ?>
        </p>

    <?php endif ?>

</section>
