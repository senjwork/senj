<ul >
        <?php foreach ($list as $key => $val): ?>
    <li<?php if (mb_strtolower(Request::initial()->controller()) . '/' . mb_strtolower(Request::initial()->action()) == $key || (mb_strtolower(Request::initial()->controller())  == $key && mb_strtolower(Request::initial()->action()) == 'index'  ) ||  (empty($key) && mb_strtolower(Request::initial()->action()) == 'index'  ) ): ?> class="selected"<?php endif; ?>><a data-pjax="#content" href='/<?= $key ?>'><?= $val ?></a></li>
        <?php endforeach; ?>
</ul>
