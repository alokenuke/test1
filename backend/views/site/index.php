<div ng-controller="AlertController" id="alertMessages">
    <div ng-repeat="alert in alerts" class="alert alert-{{(alert.type=='error'?'danger':alert.type)}}" data-dismiss="alert" type="{{alert.type}}" close="closeAlert($index)">
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
                                <a ng-href="{{item.link}}" ng-class="{'active': !item.link}">{{item.label}}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col-sm-6 text-right">
                        <h3 class="mt-0 mb-0" ng-bind-html="(head_title?head_title:page_title)"></h3>
                    </div>              
                </div><!--/row-->
                <div class="row mt-15">
                    <div class="col-xs-12" ng-view  ng-animation="am-fade-and-slide-left" autoscroll="true">
                        <?= $this->render('_index'); ?>
                    </div>
                </div>
            </div><!--/content-->
        </div>
    </div><!--/row-->
</section>
<style>
    .ui-select-bootstrap .ui-select-choices-row > a
    {
        padding: 0;
    }
    .ui-select-bootstrap .ui-select-choices-row > a div.ng-binding {
        padding: 3px 20px
    }
</style>