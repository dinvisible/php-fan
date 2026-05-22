<?php declare(strict_types=1); ?><section class="test unfolded">
    <h3>{#CHECK_PHP_MODULES}</h3>

    <p><b>{#REQUIRED_PHP_MODULES}</b></p>
    <ul class="module_list">
        <?php foreach ($useRequired as $k => $v) : ?>
        <li><img src="image/<?php echo $v; ?>.gif" /> <?php echo $k; ?></li>
        <?php endforeach ?>
    </ul>
    <?php if (!$allRequired) : ?>
        <p>{#SETUP_REQUIRED_MODULES}!</p>
    <?php endif ?>

        <p><br /><b>{#RECOMMENDED_PHP_MODULES}</b></p>
    <ul class="module_list">
        <?php foreach ($useRecommended as $k => $v) : ?>
        <li><img src="image/<?php echo $v; ?>.gif" /> <?php echo $k; ?></li>
        <?php endforeach ?>
    </ul>
    <?php if (!$allRecommended) : ?>
        <p>{#SETUP_RECOMMENDED_MODULES}.</p>
    <?php endif ?>

</section>
