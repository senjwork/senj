<!DOCTYPE html>
<html>
    <head>
        <title><?= $title ?></title>
        <meta name="keywords" content="<?= $keywords ?>" />
        <meta name="description" content="<?= $description ?>" />
        <? foreach ($styles as $file_style): ?>
            <?= HTML::style($file_style) ?>
        <? endforeach ?>
        <? foreach ($scripts as $file_script): ?>
            <?= HTML::script($file_script) ?>
        <? endforeach ?>
    </head>
    <body>
        <div id="body">
            <? if(isset($menu)):?><div id="menu"><?=$menu?></div><? endif;?>

            <?if(isset($page)):?><div id="content"><?=$page?></div><? endif;?>
        </div>
        <?if(isset($footer)):?><div id="footer"><?=$footer?></div><? endif;?>
    </body>
</html>