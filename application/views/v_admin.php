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
        <? if(isset($menu)):?><?=$menu?><? endif;?>
        
        <?if(isset($page)):?><?=$page?><? endif;?>

        <?if(isset($footer)):?><?=$footer?><? endif;?>
    </body>
</html>