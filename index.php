<!doctype html>
<html lang="en" ng-app="capacityTickets">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>CE_Online</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <!-- Place favicon.ico in the root directory -->

		<link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/bootstrap.css">
		<link rel="stylesheet" href="css/bootstrap-theme.css">
		<link rel="stylesheet" href="css/angular-spinkit.css">
		
        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
    </head>
    <body style="padding-top: 60px;">
	<div class="wrapper">
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <!-- Add your site or application content here -->
		<div ng-include src="'parts/header.html'"></div>
		<div ui-view></div>
        <script src="js/angular.min.js"></script>
		<script src="js/angular-filter.min.js"></script>
		<script src="js/angular-touch.min.js"></script>
		<script src="js/angular-ui-router.js"></script>
		<script src="js/ui-bootstrap-tpls-0.13.4.js"></script>
		<script src="js/angular-spinkit.js"></script>
		<script src="js/jquery-1.11.3.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.3.min.js"><\/script>')</script>
		<script src="js/bootstrap.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/ng-main.js"></script>
		<script src="js/jq-main.js"></script>
		
	</div>
    </body>
</html>
