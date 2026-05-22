<?php declare(strict_types=1); ?><section class="test">
    <h3>{#CHECK_CACHE_DIRECTORIES}</h3>

    <div id="tmp_dir">
        <img src="image/<?php echo $isTmp > 0 ? 'correct' : 'incorrect'; ?>.gif" />
        <?php if (empty($isTmp)) : ?>
            {#TEMP_DIR_IS_NOT_RECOGNIZED}
        <?php else : ?>
            <b>{#TEMP_DIR}:</b>
            <code><?php echo $tempDir; ?></code>
            <?php if ($isTmp < -1) : ?>
                <br />{#TEMP_DIR_IS_NOT_WRITABLE}
            <?php elseif ($isTmp < 0) : ?>
                <br />{#TEMP_DIR_IS_NOT_EXISTS}
            <?php endif ?>
        <?php endif ?>
    </div>
    <ul class="cache_dir_list">
        <?php if ($isTmp > 0) : ?>
            <?php foreach ($cacheDir as $k => $v) : ?>
                <li>
                    <img src="image/<?php echo $v['img']; ?>.gif" />
                    <b><?php echo $k; ?>-cache-dir:</b>
                    <code><?php echo $v['writable'] ? realpath($v['dir']) : $v['dir']; ?></code>
                    <?php if (!$v['writable'] && $v['required']) : ?>
                        <br />{#PLEASE_CREATE_DIR}.
                    <?php elseif (!$v['writable']) : ?>
                        <br />{#NEED_CREATE_DIR}.
                    <?php endif ?>
                </li>
            <?php endforeach ?>
        <?php endif ?>

    </ul>

</section>
