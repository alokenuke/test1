<?php
use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
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
    <title><?= Html::encode(Yii::$app->params['sitename'].": ".$this->title) ?></title>
    <?php $this->head() ?>
    <script>
        var SiteUrl = "<?php echo Yii::getAlias("@web");?>";
    </script>
    <style>
        a.navbar-brand {padding: 5px;}
        .navbar-fixed-top.navbar {border-bottom: 1px solid #ccc;height: 60px;}
        .navbar.navbar-fixed-top {background-color: #fff;}
        .help-block {font-size: 12px;}
    </style>
</head>
<body data-spy="scroll" class="ng-cloak">
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php if (!Yii::$app->user->isGuest) { ?>
        <?php
            NavBar::begin([
                'brandLabel' => '<img src="/img/logo_inner.png" alt="SiteTrack"/>',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-fixed-top',
                ],
            ]);
            $menuItems = [
                ['label' => 'Dashboard', 'url' => ['/dashboard']],
            ];
            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
            } else {
                $menuItems[] = [
                    'label' => 'Manage Tags',
                    'url' => '/#r-tags',
                ];
                $menuItems[] = [
                    'label' => 'View / Print Reports',
                    'url' => ['#r-reports'],
                ];
                $menuItems[] = [
                    'label' => 'Logout (' . Yii::$app->user->identity->username . ')',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post']
                ];
            }
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
            ]);
            NavBar::end();
        ?>
        <?php } ?>

        <div class="container">
        <?php Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ;
        ?>
        <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
        <p class="pull-left">&copy; sitetrack-nfc.com <?= date('Y') ?> | All Rights Reserved.</p>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
