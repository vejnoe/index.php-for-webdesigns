<?php $made_by =
"<!--
		Webdesign index.php - Beta v1.2
		https://github.com/vejnoe/index.php-for-webdesigns

		|||||||||||||||   Vejnø
		|||   |||   |||   Andreas Vejnø Andersen
		|||   |||   |||   www.vejnoe.dk
		|||||||||||||||   © 2015
	-->
";



// Settings

// Project settings
$name = "Project Title"; // Surfix for page title (Project name).
$update = 1; // To force browser to update, plus the number every time you update your files.

// Page settings
$background_color = "#FFF"; // Background color, if your layout don't have full with.
$page_min_width = "960px"; // Page with.
$margin_bottom = '0px'; // If you Photoshop workflow cuts the buttom margin of your layout.

// Menu settings
$active_color = "#87CEEB"; // Menu active link color (Default #87CEEB)
$color_menu_background = "#2F3238"; // Menu background color














































// Debug
if (isset($_GET ["debug"])) { $debug = $_GET["debug"]; }
# $debug = true;


/**
 * Function
 * Cleand titles - Strip sorting numbers and extensions
 */
function clean_title ($title) {
	// Slet endelsen...
	$title = pathinfo ($title);
	$title = $title['filename'];

	// Strip away @2x
	if (
		strpos ($title, '@2x') == true ||
		strpos ($title, '@3x') == true ||
		strpos ($title, '@4x') == true
	) {
		$title = substr ($title, 0, -3);
	}

	$title_striped = $title;
	// Så længe den starter med et nummer eller et tegn...
	while(is_numeric (substr ($title_striped,0,1)) || substr ($title_striped,0,1) == '_' || substr ($title_striped,0,1) == '-' || substr ($title_striped,0,1) == ' ' || substr ($title_striped,0,1) == '.') {
		$title_striped = substr ($title_striped,1);
	}
	// Hvis nu der kun var et nummer så går tilbage til bare at slette endelsen...
	if ($title_striped == '') {
		$title = $title;
	} else {
		$title = $title_striped;
	}
	return($title);
}


function get_title ($url) {
	$url = explode('/', $url);
	if (isset($url[1])) {
		return clean_title($url[1]);
	} else {
		return clean_title($url[0]);
	}
}


/**
 * Function
 * Is the file a image?
 */
function filter_files_and_folders ($file_name) {
	if (
		(
			substr($file_name, -3) == 'png' ||
			substr($file_name, -3) == 'gif' ||
			substr($file_name, -3) == 'jpg' ||
			substr($file_name, -4) == 'jpeg' ||
			is_dir($file_name)
		)	&& (
			substr ($file_name,0,1) != '.' &&
			substr ($file_name,0,1) != '_' &&
			substr ($file_name,0,1) != '$' &&
			strpos ($file_name, '/.') === false
		) && (
			strpos($file_name, 'node_modules') === false
		)
	) {
		return true;
	} else {
		return false;
	}
}


/**
 * Function
 * Getting files into the array $files.
 */
function list_dir_v2($dir, $prefix = '') {
	$dir = rtrim($dir, '\\/');
	$result = array();
	$i = 1;

	$di = new RecursiveDirectoryIterator($dir);
	foreach (new RecursiveIteratorIterator($di) as $file_name => $file) {
		if (filter_files_and_folders(substr($file_name,2))) {
			$result[] = substr($file_name,2);
		}
	}
	sort($result, SORT_NATURAL | SORT_FLAG_CASE);
	return $result;
}


/**
 * Function
 * Slugify
 */
function slugify($text) {
  // replace non letter or digits by -
  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
  // trim
  $text = trim($text, '-');
  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
  // lowercase
  $text = strtolower($text);
  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);
  if (empty($text)) {
    return 'n-a';
  }
  return $text;
}


/**
 * Function
 * Converting raw path to slug
 */
function slugify_url($url) {
	$output = '';
	if (isset($url[1])) {
		$output .= slugify(clean_title($url[0]));
		$output .= '/';
		$output .= slugify(clean_title($url[1]));
	} else {
		$output .= slugify(clean_title($url[0]));
	}
	return $output;
}


/**
 * Function
 * Converting raw path to slug
 */
function slugify_array($array, $new_array) {
	$i = 0;
	foreach ($array as $item) {
		//explode('/', $item);
		$item = explode('/', $item);
		$new_array[] = slugify_url($item);
	}
	return $new_array;
}


/**
 * Function
 * Url to the next page
 */
function next_url($files_slug, $file_id) {
	if (count($files_slug) > $file_id+1) {
		return "?q=" . $files_slug[$file_id+1];
	} else {
		return false;
	}
}


/**
 * Function
 * Url to the previus page
 */
function prev_url($files_slug, $file_id) {
	if (isset($_GET["q"]) && $file_id == 1) {
		// If first page, clean up url
		return '/';
	} else if (isset($_GET["q"]) && $file_id != 0) {
		return "?q=" . $files_slug[$file_id-1];
	} else {
		return false;
	}
}


/**
 * Function
 * Url to the menu
 */
function url ($file, $file_slug, $i, $file_id) {
	$output = '';
	$output .= '<li id="nr'. $i .'"';
	if ($i == $file_id) {
		$output .= ' class="active"';
	}
	$output .= '><a href="?q=' . $file_slug . '">';
		$output .= clean_title($file);
	$output .= '</a></li>';
	return $output;
}



$files = list_dir_v2('.');
$files_slug = array();
$files_slug = slugify_array($files, $files_slug);


// Testing if there are any image files in the project folder
if (count($files) === 0) {
	print '<h1>Cool you have now installed Vejnø Viewer file!<br> Now go a head an put some images in the same folder...</h1>';
	die; // TODO make this look pretty, and set an alert not kill the page...
}


// File ID - Set the current page and sub page.
if (count($files) == 1) {
	$page = 0;
} else if (isset($_GET["q"]) && $_GET["q"] != "") {
	$page = array_search($_GET["q"], $files_slug);
} else {
	$page = 0;
}
$file_id = (int) $page;
// Check if the file exsist
if (!isset($files[$file_id])) {
	print 'File do not exsist'; die;
}


// File path
$file_path = $files[$file_id];



// Getting file info.
$file_details = getimagesize($file_path);
list($file_width, $file_height, $file_type, $file_attr) = $file_details;

?><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<?php print $made_by; ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="robots" content="none">
	<meta name="googlebot" content="noarchive">
	<title><?php
		print get_title($files[$file_id]);
		print ' — ' . $name;
	?></title>
	<?php if (count($files) > 1): ?>
		<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
		<script>
			function nowGoTo(destination) {
				currentSelected = $('nav li.active').attr('id').substring(10,2);
				currentSelected = parseInt(currentSelected);

				if (destination == 'next') {
					newSelection = currentSelected+1;
					if (!$('nav li').last().hasClass('active')) {
						newSelection = 'nav li#nr' + newSelection.toString() + ' a';
						window.location = $(newSelection).attr('href');
					};
				} else if (destination == 'prev') {
					newSelection = currentSelected-1;
					if (newSelection != 0) {
						newSelection = 'nav li#nr' + newSelection.toString() + ' a';
						window.location = $(newSelection).attr('href');
					};
				};
			};
			function toggle(thisThing) {
				if (thisThing == 'help') {
					if (!$('#help').hasClass('view')) {
						$('#help').fadeIn().addClass('view');
						$('nav').addClass('view');
						$('#quick-help').fadeOut();
					} else {
						$('#help').fadeOut().removeClass('view');
						$('nav').removeClass('view');
						$('#quick-help').fadeOut();
					}
				}

			}


			$("html").keydown(function(event) {
				if (event.which == 13) {
					// Enter
					if (!$('nav').hasClass('view')) {
						$('nav').addClass('view');
					} else if ($('nav li.focus').hasClass('active')) {
						$('nav').removeClass('view');
						$('.overlay').fadeOut().removeClass('view');
					} else {
						window.location = $('nav li.focus a').attr('href');
					}
				} else if (event.which == 38) {
					// Up
					// TODO: Make this one and the next a function...
					if (!$('nav').hasClass('view')) {
						$('nav').addClass('view');
					};

					currentSelected = $('nav li.focus').attr('id').substring(10,2);
					newSelection = currentSelected-1;
					if (newSelection != 0) {
						$('nav li.focus').removeClass('focus');
						newSelection = 'nav li#nr' + newSelection;
						$(newSelection).addClass('focus');
					};

					return false;
				} else if (event.which == 40) {
					// Down
					if (!$('nav').hasClass('view')) {
						$('nav').addClass('view');
					};

					currentSelected = $('nav li.focus').attr('id').substring(10,2);
					currentSelected = parseInt(currentSelected);
					newSelection = currentSelected+1;

					if (!$('nav li').last().hasClass('focus')) {
						$('nav li.focus').removeClass('focus');
						newSelection = 'nav li#nr' + newSelection.toString();
						$(newSelection).addClass('focus');
						//console.log(newSelection);
						//console.log($(newSelection));
					};

					return false;

				} else if (event.which == 39) {
					// Right
					if (!$('nav').hasClass('view')) {
						nowGoTo('next');
					};
				} else if (event.which == 37) {
					// Left
					if (!$('nav').hasClass('view')) {
						nowGoTo('prev');
					};
				} else if (event.which == 171 || event.which == 191 || event.which == 187) {
					// ?
					if (!$('#help').hasClass('view')) {
						$('#help').fadeIn().addClass('view');
						$('nav').addClass('view');
						$('#quick-help').fadeOut();
					} else {
						$('#help').fadeOut().removeClass('view');
						$('nav').removeClass('view');
						$('#quick-help').fadeOut();
					}
				} else if (event.which == 27) {
					// ESC
					$('.overlay').fadeOut().removeClass('view');
					$('nav').removeClass('view');
				}
				//console.log(event.which);
			});

			$(document).ready(function() {
				$("nav li.active").addClass('focus');
				$('.overlay').click(function(){
					$('.overlay').fadeOut().removeClass('view');
					$('nav').removeClass('view');
				});
				setTimeout( function(){
		 			$('#quick-help').fadeOut();
				},5000);

				$('a.prev, a.next').click(function () {
				    var href = $(this).attr('href');

				    // Redirect only after 500 milliseconds
				    if (!$(this).data('timer')) {
				       $(this).data('timer', setTimeout(function () {
				          window.location = href;
				       }, 300));
				    }
				    return false; // Prevent default action (redirecting)
				});

				$('a.prev, a.next').dblclick(function () {
				    clearTimeout($(this).data('timer'));
				    $(this).data('timer', null);

				    if (!$('#help').hasClass('view')) {
						$('#help').fadeIn().addClass('view');
						$('nav').addClass('view');
						$('#quick-help').fadeOut();
					} else {
						$('#help').fadeOut().removeClass('view');
						$('nav').removeClass('view');
						$('#quick-help').fadeOut();
					}

				    return false;
				});

				$('.open-nav').click(function() {
					if (!$('#help').hasClass('view')) {
						$('nav').addClass('view');
						$('#quick-help').fadeOut();
					} else {
						$('#help').fadeOut().removeClass('view');
						$('nav').removeClass('view');
						$('#quick-help').fadeOut();
					}

					return false;
				});
			});
		</script>
		<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,600' rel='stylesheet'>
	<?php endif ?>
	<style>
		/* Layout */
		html,body,span,p,ol,ul,li,a,em,h1,h2,h3,h4,h5,h6  {
			font-family: 'Source Sans Pro', sans-serif;
			font-weight: 300;
			font-size: 16px;
		}
		body {
			margin: 0 0 <?php print $margin_bottom; ?>;
			min-width: <?php print $page_min_width; ?>;
			height: <?php print $file_height; ?>px;
		}
		body.retina {
			height: <?php print $file_height/2; ?>px;
		}
		.next,
		.prev {
			display: block;
			width: 50%;
			height: 100%;
			position: fixed;
			top: 0;
			cursor: w-resize;
			z-index: 1;
		}
		.next {
			left: 50%;
			cursor: e-resize;
		}
		figure {
			background: url('<?php print $file_path . '?v=' . $update; ?>') top center <?php print $background_color; ?> no-repeat;
			height: 100%;
			margin: 0;
			padding: 0;
			z-index: 0;
		}
		.retina figure {
			background-size: <?php print $file_width/2; ?>px;
		}

		/* Text */
		h1 {
			font-size: 60px;
		}
		h2 {
			font-size: 14px;
			font-weight: 600;
			text-transform: uppercase;
		}
		h3 {
			font-size: 18px;
			font-weight: 300;
			color: #e7e5e9;
		}
		strong {
			font-weight: 600;
		}

		/* Menu */
		nav {
			background: <?php print $color_menu_background; ?>;
			box-shadow: inset rgba(39, 37, 41, .30) -5px 0 8px;
			width: 300px;
			height: 100%;
			position: fixed;
			overflow-x: hidden;
			overflow-y: auto;
			top: 0;
			z-index: 20;
			-webkit-transform: translate3d(-100%, 0, 0);
			transform: translate3d(-100%, 0, 0);

			-webkit-transition: all 0.5s;
			transition: all 0.5s;
		}
		nav.view {
			-webkit-transform: translate3d(0, 0, 0);
			transform: translate3d(0, 0, 0);
		}
		nav ul {
			font: normal 16px / normal 'Varela Round', Helvetica, Arial, sans-serif;
			text-shadow: rgba(46, 43, 49, 0.35) 0 -1px 0;
			padding: 0;
			margin: 40px 20px;
		}
		nav ul ul {
			border-bottom: 1px solid #46434a;
			margin: 0 0 20px;
			padding: 0 0 10px;
		}
		nav li {
			color: #fff; /*#a19fa4;*/
			margin: 0 0 5px 0;
			list-style: none;
		}
		nav ul li.active a {
			color: <?php print $active_color; ?>;
		}
		nav ul li.folder li.active,
		nav ul li.folder li.focus.active {
			list-style: disc;
			color: <?php print $active_color; ?>;
		}
		nav ul li.folder li.focus.active {
			text-shadow: 0 0 2px #fff;
		}
		nav li.folder {
			margin: 0 0 5px;
			list-style: none;
		}
		nav li.folder li {
			color: #B8B9BC; /*#a19fa4;*/
			margin: 0 0 5px 20px;
			list-style: circle;
		}
		nav h3 {
			border-top: 1px solid #46434a;
			margin: 20px 0 5px;
			padding: 10px 0 0;
		}
		nav li.folder+li.folder h3,
		nav li:first-child h3 {
			border-top: none;
			margin: -10px 0 5px;
			padding: 0 0 0;
		}
		nav li a {
			display: block;
		}
		nav a {
			color: #B8B9BC; /*#a19fa4;*/
			text-decoration: none;
		}
		nav a:hover,
		nav ul li.folder li:hover,
		nav ul li.folder li.focus {
			color: #fff; /*#e7e5e9;*/
		}
		nav li.focus,
		nav li.focus a {
			color: #fff;
		}
		nav li:last-child ul {
			border-bottom: none;
		}
		/* Menu - Open */
		.open-nav,
		.open-nav div {
			display: block;
			height: 100%;
			width: 50px;
			position: fixed;
			z-index: 2;
			top: 0;
		}
		.open-nav div {
			box-shadow: inset rgba(39, 37, 41, .30) -5px 0 8px;
			color: rgba(255,255,255,0.2);
			text-decoration: none;
			font-weight: 700;
			font-size: 28px;
			line-height: 4px;
			text-align: center;
			padding-top: 10px;

			width: 26px;
			background: <?php print $color_menu_background; ?>;
			-webkit-transform: translate3d(-100%, 0, 0);
			transform: translate3d(-100%, 0, 0);

			-webkit-transition: all 0.5s;
			transition: all 0.5s;
		}
		.open-nav:hover div {
			-webkit-transform: translate3d(0, 0, 0);
			transform: translate3d(0, 0, 0);
		}


		/* Menu - Info */
		.info {
			font-size: 12px;
			padding: 10px 20px;
			color: #a19fa4;
			font-weight: 600;
		}
		.info a {
			color: #a19fa4;
			font-size: 12px;
			text-decoration: underline;
			font-weight: 600;
		}
		.info a:hover {
			text-decoration: none;
		}

		/* Overlays */
		.overlay {
			z-index: 10;
			top: 0;
		}
		.overlay#help {
			background-color: rgba(255, 255, 255, 0.9);
			position: fixed;
			height: 100%;
			width: 100%;
			display: none;
			overflow-x: hidden;
			overflow-y: auto;
		}
		.overlay#help .help {
			margin-left: 300px;
			padding: 20px 60px;
		}
		.overlay#quick-help {
			height: 100%;
			position: fixed;
			width: 100%;
		}
		.overlay#quick-help .quick-help {
			background: none repeat scroll 0 0 rgba(255, 255, 255, 0.9);
			border-radius: 4px;
			left: 50%;
			margin: -90px auto auto -250px;
			padding: 10px 20px 20px;
			position: fixed;
			top: 50%;
			width: 500px;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
		}

		/* Keys */
		.key {
			font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
			font-size: 14px;
			font-weight: bold;

			border-radius: 3px;
			border: 1px solid rgba(0,0,0,0.2);
			border-bottom: 2px solid rgba(0,0,0,0.2);
			box-shadow: 0 0 6px rgba(0,0,0,0.07);
			color: #777;

			width: 40px;
			line-height: 40px;
			display: inline-block;
			text-align: center;
			background: #46434a;
			background: #fff;
			margin-right: 10px;
			vertical-align: baseline;
		}
		.key:hover {
			background: rgb(250,250,250);
		}
		.key.enter {
			padding-top: 20px;
		}
		.key.left,
		.key.right,
		.key.up,
		.key.down {
			padding-bottom: 2px;
			line-height: 38px;
		}
		.key.esc {
			font-size: 11px;
		}

		/* Help page */
		.keys {
			padding: 10px 0 0;
			border-top: 1px solid rgba(0,0,0,0.1);
			margin: 10px 0 0;
		}
		.keys li {
			margin-bottom: 10px;
			list-style: none;
		}
	</style>
</head>
<body<?php if(strpos($files[$file_id], '@2x')) { print ' class="retina"'; } ?>><?php

// Debugging
	if(isset($debug)) { ?>
	<div style="background: rgba(0,0,0,.8); margin: 40px; padding: 30px; position: absolute;">
		<pre style="color:white;">

??: <?php print_r((substr('readme.smd', -3, 1) == '.' || substr('readme.smd', -4, 1) == '.' || substr('readme.smd', -5, 1) == '.')) ?>

$_GET["p"]: <?php if (isset($_GET["p"])) { print $_GET["p"]; } else { print 'Er ikke sat'; } ?>

$file_id: <?php print_r($file_id);	?>

$file_path: <?php print $file_path; ?>

$file_name: <?php print $file_name; ?>

clean_title($file_name): <?php print clean_title($file_name); ?>



Nummer? <?php print_r(is_numeric(substr($file_path,0,1))); ?>

gettype: <?php // print gettype($files[0]); ?>



<?php if (gettype($files[$file_id]) == 'array') {
	print 'Dette er et array()';
} ?>

<?php print_r($files); ?>
<?php print_r($files_slug); ?>

		</pre>
	</div>
	<?php }
// END if debug
	?>

	<?php if (count($files) == 1): ?>
		<figure></figure>
	<?php else: ?>
		<figure></figure>
		<a href="#" class="open-nav"><div>≣</div></a>
		<?php if (prev_url($files_slug, $file_id)): ?>
			<a href="<?php print prev_url($files_slug, $file_id); ?>" class="prev"></a>
		<?php endif ?>
		<?php if (next_url($files_slug, $file_id)): ?>
			<a href="<?php print next_url($files_slug, $file_id); ?>" class="next"></a>
		<?php endif ?>

		<div class="overlay" id="help">
			<div class="help">
				<h1><?php print $name; ?></h1>
				<h2>Keyboard shortcut</h2>
				<ul class="keys">
					<li><span class="key">?</span> Show this help page</li>
				</ul>
				<ul class="keys">
					<li><span class="key left">&#x2190;</span><span class="key right">&#x2192;</span> Navigating pages</li>
				</ul>
				<ul class="keys">
					<li><span class="key enter">&#x21a9;</span> Toggle menu view<li>
				</ul>
				<ul class="keys">
					<li><span class="key up">&#x2191;</span> Move selection up in menu</li>
					<li><span class="key down">&#x2193;</span> Move selection down in menu</li>
					<li><span class="key enter">&#x21a9;</span> Go to selection<li>
				</ul>
				<ul class="keys">
					<li><span class="key esc">esc</span> Close all overlays<li>
				</ul>
			</div>
		</div>
		<?php if ($file_id == 0) { ?>
		<div class="overlay" id="quick-help">
			<div class="quick-help">
				<h2><?php print $name; ?></h2>
				<ul class="keys">
					<li><span class="key left">&#x2190;</span><span class="key right">&#x2192;</span> to navigating pages or press <span class="key enter" style="margin-left: 10px;">&#x21a9;</span> to toggle menu view.<li>
				</ul>
			</div>
		</div>
		<?php } ?>
		<nav>
			<div class="info">Press <strong>?</strong> for help&nbsp;&mdash;&nbsp;<a href="https://github.com/vejnoe/index.php-for-webdesigns" target="_blank" title="GitHub">Beta v1.2, change log</a></div>
			<ul class="navigation">
				<?php
				$i = 0;
				$folder = false;

				foreach ($files as $file) {
					$file = explode('/', $file);
					if (!$folder && isset($file[1])) {
						$folder = $file[0];
						print '<li class="folder"><h3>' . clean_title($folder) . '</h3><ul>';
						print url ($file[1], $files_slug[$i], $i, $file_id);
					} else if ($folder == $file[0] && isset($file[1])) {
						print url ($file[1], $files_slug[$i], $i, $file_id);
					} else if ($folder != $file[0] && isset($file[1])) {
						print '</ul></li>';
						$folder = $file[0];
						print '<li class="folder"><h3>' . clean_title($folder) . '</h3><ul>';
						print url ($file[1], $files_slug[$i], $i, $file_id);
					} else if ($folder && $folder != $file[0] && !isset($file[1])) {
						print '</ul></li>';
						print url ($file[0], $files_slug[$i], $i, $file_id);
						$folder = false;
					} else if (!$folder && !isset($file[1])) {
						print url ($file[0], $files_slug[$i], $i, $file_id);
					}
					$i++;

				}
				?>
			</ul>
		</nav>
	<?php endif; ?>

</body>
</html>
