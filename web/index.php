<?php
require_once("site.php");
$site = startApplication('components');
if ($site->getRedirect()) {
    exit();
};


$component = $site->getComponentObject();
$title = $component->getTitle();           //Нашли заголовок страницы
$css = $component->getCSSFile();
$base = $site->getBaseName();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="KeyWor" content="text/html; charset=utf-8"/>
    <meta name="Keywords" content="<?php print(htmlspecialchars($component->getKeyWords())); ?>"/>
    <meta name="Description" content="<?php print(htmlspecialchars($component->getDescription())); ?>"/>
    <!-- meta <?php print(var_dump($site->url_parameters)) ?> -->
    <base href="<?php print($base) ?>"/>


    <title><?php print(htmlspecialchars($title)); ?></title>
    <link rel="stylesheet" href="css/<?php print($css); ?>" type="text/css"/>
    <script type="text/javascript" src="js/mootools.js"></script>
    <?php $auxScripts = $component->getAuxScripts();
    foreach ($auxScripts as $value) print("<script type=\"text/javascript\" src=\"$value\"></script>");
    ?>

    <!--[if lte IE 7]>
    <style type="text/css">
        .btr_b {
            padding-left: 5px;
        }
    </style><![endif]-->
</head>

<body>
<div class="container">
    <?php $site->renderModule('mod_header'); ?>
    <?php $site->renderContent(); ?>

    <div class=footer>
        <p>Copyright ...</p>
    </div>
</div>
</body>
</html>


