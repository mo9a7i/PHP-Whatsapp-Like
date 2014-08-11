	<!-- Who did this page -->
	<link rel="author" href="http://<?=base_url(); ?>/humans.txt">
	<meta name="author" content="Mohannad Faihan Otaibi" />
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta name="author" content="Mohannad F. Otaibi">
	<meta name="google" content="notranslate" />
	<meta name="robots" content="index,follow" />
	
    <link rel="shortcut icon" href="<?php echo asset_url(); ?>/ico/favicon.ico" type="image/x-icon">
	<link rel="icon" href="<?php echo asset_url(); ?>/ico/favicon.ico" type="image/x-icon">

	<title><?php echo print_title($title);?></title>
    <meta name="description" content="<?php echo print_description($description); ?>">
	<meta name="keywords" content="<?php echo print_keywords($keywords);?>" />
	
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

	<!-- Font Awesome CSS -->
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
	
	<!-- Bootstrap RTL CSS -->
    <!-- <link href="<?php echo asset_url(); ?>/css/bootstrap-rtl.css" rel="stylesheet"> -->
	
	<!-- Theme CSS -->
    <link href="<?php echo asset_url(); ?>/css/style.css" rel="stylesheet">
	
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
	
<?php
if(isset($header_include))
{
	echo $header_include;
}
?>