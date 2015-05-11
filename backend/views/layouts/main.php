<?php
use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" ng-app="siteTrackApp">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title ng-bind-html="page_title || '<?= Html::encode(Yii::$app->params['sitename'].": Dashboard".$this->title) ?>'"></title>
    <?php $this->head() ?>
    <script>
        var SiteUrl = "<?php echo Yii::getAlias("@web");?>";
    </script>
</head>
<body data-spy="scroll" class="ng-cloak">
    <?php $this->beginBody() ?>
        <?php if (!Yii::$app->user->isGuest){ echo $this->render("header");}?>
        <div class="wrap">
            <?php echo $content ?>
        </div>
        <?php echo $this->render("footer")?>
    <?php $this->endBody() ?>
    <?php if(!\yii::$app->user->isGuest && Yii::$app->getRequest()->getUserIP() != "::1") { ?>
        <script type="text/javascript" src="http://assets.freshdesk.com/widget/freshwidget.js"></script>
        <script type="text/javascript">
                FreshWidget.init("", {"queryString": "&widgetType=popup", "utf8": "✓", "widgetType": "popup", "buttonType": "text", "buttonText": "Customer Support", "buttonColor": "white", "buttonBg": "#58abe1", "alignment": "2", "offset": "350px", "formHeight": "500px", "url": "https://sitetrack.freshdesk.com"} );
        </script>
    <?php } ?>
</body>
</html>
<?php $this->endPage() ?>
