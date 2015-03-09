<header class="navbar navbar-default navbar-static-top" role="navigation">
    <div class="container-fluid">
        <div class="pull-right welcome-wrapper">
            <ul>
                <li class="dropdown">
                <a ng-href="" class="dropdown-toggle welcome-text" data-toggle="dropdown" role="button" aria-expanded="false">Welcome : 
                    <span class="username"><?php echo \yii::$app->user->identity->first_name." ".\yii::$app->user->identity->last_name;?></span>
                    <span class="caret"></span></a>
                    <ul class="dropdown-menu weclome-list dropdown-menu-right" role="menu">
                        <?php if(\yii::$app->user->identity->lastTokens) { ?>
                            <li>Last Login IP : <span class="ip-address pl-5">
                                    <?php echo \yii::$app->user->identity->lastTokens->login_ip; ?>
                                </span></li>
                            <li>Last login location : <span class="location pl-5">
                                <?php echo \yii::$app->user->identity->lastTokens->login_location; ?>
                                </span>
                            </li>
                        <?php } else { ?>
                            <li>This is your first time login.</li>
                        <?php } ?>
                    </ul>
                 </li>
                 <li ng-non-bindable>
                     <a class="btn btn-primary btn-small" href="<?php echo \yii::$app->getUrlManager()->createUrl("/site/logout")?>" data-method="post">
                         Logout
                         <i class="fa fa-sign-out"></i>
                     </a>
                </li>
            </ul>
            <?php if(\yii::$app->user->identity->lastTokens) { ?>
            <div class="last-login">Last login : 
                <span class="date-time text-muted">
                    {{<?php echo \yii::$app->user->identity->lastTokens->created_on;?>*1000 | date : "dd MMM yyyy h:mm:ss"}}
                </span>
            </div>
            <?php } ?>
        </div>
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/#/"><img src="<?php echo \yii::$app->urlManager->createUrl("/images/logo.png")?>" alt="" /></a>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="nav-container">
        <div class="container-fluid">
            <nav class="navbar-collapse collapse">
                <?php
                    use yii\bootstrap\Nav;
                    ?>
                    <?php if (!Yii::$app->user->isGuest) { ?>
                        <?php
                            echo Nav::widget([
                                'options' => ['class' => 'nav navbar-nav navbar-left'],
                                'items' => \backend\models\Menu::getMenu(),
                            ]);
                        ?>
                    <?php } ?>
                <?php
                if(\yii::$app->user->identity->role_details->type=='Client') { ?>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <div class="col-xs-12 pull-right">
                            <form class="" role="search" ng-submit="searchTags(globalSearch)">
                                <div class="input-group">
                                    <input type="text" class="form-control search-control" placeholder="Tag Name, Description, UID or Product Code" name="searchTags" autocomplete="false" style="padding:6px 6px;" ng-model="globalSearch" ng-required="true">
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary search-btn" type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>
                </ul>
                <?php } ?>
            </nav><!--/.nav-collapse -->
        </div>
    </div><!--/nav-container-->
</header>