<div ng-controller="AlertController">
    <div ng-repeat="alert in alerts" class="alert alert-danger alert-{{alert.type}}" data-dismiss="alert" type="{{alert.type}}" close="closeAlert($index)">
        <a href="" class="close" data-dismiss="alert">&times;</a>
        <strong>{{alert.type | titlecase}}!</strong> {{alert.msg}}
    </div>
</div>
<section class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="content primary-bg-wrapper column-gs-sm">
                <div class="row">
                    <div class="col-sm-6">
                        <ol class="breadcrumb">
                            <li ng-repeat="item in breadcrumbs">
                                <a href="{{item.link}}">{{item.label}}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col-sm-6 text-right">
                        <h3 class="mt-0 mb-0">{{page_title}}</h3>
                    </div>              
                </div><!--/row-->
                <div class="row mt-15">
                    <div class="col-xs-12" ng-view  ng-animation="am-fade-and-slide-left">
                        <?= $this->render('_index'); ?>
                    </div>
                </div>
            </div><!--/content-->
        </div>
    </div><!--/row-->
</section>