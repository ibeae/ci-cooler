<!DOCTYPE html>
<!--[if lt IE 7]>      <html ng-app="myApp" class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html ng-app="myApp" class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html ng-app="myApp" class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html ng-app="myApp" class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo isset($title) ? $title : config_item('project_title') ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <?php echo Content::link('admin/css/bootstrap.min.css') ?>
        <style>
            body {
                padding-top: 60px;
                padding-bottom: 40px;
            }
        </style>
        <?php echo Content::link('admin/css/bootstrap-responsive.min.css') ?>
        <?php echo Content::link('admin/css/main.css') ?>
        <?php echo Content::script('admin/js/vendor/modernizr-2.6.1-respond-1.1.0.min.js') ?>
        <script>
            var site = {base : '<?php echo site_url() ?>'}
        </script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
        <![endif]-->

        <!-- This code is taken from http://twitter.github.com/bootstrap/examples/hero.html -->

        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="#"><?php echo config_item('project_title') ?></a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="active"><a href="#/index">Home</a></li>
                            <li><a href="#/modules">Installed Modules</a></li>
                            <li><a href="#contact">Contact</a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="#/account/login">login</a></li>
                                </ul>
                            </li>
                        </ul>
                        <!-- <form class="navbar-form pull-right">
                            <input class="span2" type="text" placeholder="Email">
                            <input class="span2" type="password" placeholder="Password">
                            <button type="submit" class="btn">Sign in</button>
                        </form> -->
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>

        <div id="view" ng-view class="container">