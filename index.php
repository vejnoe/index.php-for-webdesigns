<?php
/*

	Webdesign index.php - a2.2
	https://github.com/vejnoe/index.php-for-webdesigns

	|||||||||||||||   Vejnø
	|||   |||   |||   Andreas Vejnø Andersen
	|||   |||   |||   www.vejnoe.dk
	|||||||||||||||   © 2013

*/



// Settings

$name = "Project Title"; // Surfix for page title (Project name).

$background_color = "#FFF"; // Background color, if your layout don't have full with.
$active_color = "#87CEEB"; // Menu active link color (Default #87CEEB)
$page_min_width = "960px"; // Page with.
$margin_bottom = '0px'; // If you Photoshop workflow cuts the buttom margin of your layout.

$update = 1; // To force browser to update, plus the number every time you update your files.














































// Debug
if (isset($_GET ["debug"])) { $debug = $_GET["debug"]; }
# $debug = true;


// Strip sorting numbers and extensions
function clean_title ($title) {
	// Slet endelsen...
	$title = pathinfo ($title);
	$title = $title['filename'];
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


// Files to skip
function skip_file ($file_name) {
	if (
		$file_name == "." ||
		$file_name == ".." ||
		$file_name == ".DS_Store" ||
		$file_name == "index.php" ||
		
		substr ($file_name,0,1) == '.' ||
		substr ($file_name,0,1) == '_' ||
		substr ($file_name,0,1) == '$'
	) {
		return true;
	} else if (
		substr($file_name, -3) != 'png' &&
		substr($file_name, -3) != 'gif' &&
		substr($file_name, -3) != 'jpg' &&
		substr($file_name, -4) != 'jpeg' &&
		(substr($file_name, -3, 1) == '.' || substr($file_name, -4, 1) == '.' || substr($file_name, -5, 1) == '.')
	) {
		return true;
	} else {
		return false;
	}
}

// Getting files into the array $files.
$files = array();
if ($level_1_handle = opendir ('.')) {
	$i = 1; $ii = 1;
	while (false !== ($level_1_entry = readdir ($level_1_handle))) {
		if (!skip_file ($level_1_entry)) {
			$files[$i] = $level_1_entry;
			if (is_dir($level_1_entry)) {
				$files[$i] = array();
				if ($level_2_handle = opendir ($level_1_entry)) {
					while (false !== ($level_2_entry = readdir ($level_2_handle))) {
						if (!skip_file ($level_2_entry)) {
							$files[$i][] = $level_1_entry;
							$files[$i][$ii] = $level_2_entry;
							$ii++;
						}
					}
					closedir ($level_2_handle);
				}
			}
			$i++; $ii = 1;
		}
	}
	closedir ($level_1_handle);
}


// File ID - Set the current page and sub page.
if (isset($_GET["p"])) { $page = $_GET["p"]; } else { $page = 1; }
$file_id = (int) $page;

if (isset($_GET["s"])) {
	$sub_page = $_GET["s"];
} else if (gettype($files[$page]) == 'array') {
	$sub_page = 1;
} else {
	$sub_page = 0;
}
$sub_file_id = (int) $sub_page;


// File name
if (gettype($files[$file_id]) == 'array') {
	if (isset($sub_file_id)) {
		$i = $sub_file_id;
	} else {
		$i = 1;
	}
	$file_name = $files[$file_id][$i];
} else {
	$file_name = $files[$file_id];
}


// File path
if (gettype($files[$file_id]) == 'array') {
	$file_path = rawurlencode($files[$file_id][0]) . '/' . rawurlencode($files[$file_id][$sub_page]);
} else {
	$file_path = rawurlencode($files[$file_id]);
}
if (gettype($files[$file_id]) == 'array') {
	$file_url = $files[$file_id][0] . '/' . $files[$file_id][$sub_page];
} else {
	$file_url = $files[$file_id];
}


// Getting file info.
$file_details = getimagesize($file_url);
list($width, $height, $type, $attr) = $file_details;


// Next url
function next_url($files, $file_id, $sub_file_id) {
	if (((gettype($files[$file_id]) != 'array') && ($file_id == count($files))) || ((gettype($files[$file_id]) == 'array') && (($sub_file_id+1) == count($files[$file_id])) && (empty($files[$file_id+1])))) {
		print '?p=1';
	} else {
		if (gettype($files[$file_id]) == 'array') {
			if (!empty($files[$file_id][$sub_file_id+1])) {
				print '?p=';
				print $file_id;
				print '&s=';
				print $sub_file_id+1;
			} else {
				print '?p=';
				print $file_id+1;
			}
		} else if (gettype($files[$file_id+1]) == 'array') {
				print '?p=';
				print $file_id+1;
		} else {
			print '?p=';
			print $file_id+1;
		}
	}
}

// Prev url
function prev_url($files, $file_id, $sub_file_id) {
	if (isset($files[$file_id-1])) {
		$prev_files_is_array = gettype($files[$file_id-1]);
	} else {
		$prev_files_is_array = false;
	}

	if ($file_id == 1 && $sub_file_id == 1) {
		return false;
	} else if (gettype($files[$file_id]) == 'array' && $sub_file_id >= 2) {
		print '?p=';
		print $file_id;
		print '&s=';
		print $sub_file_id-1;
	} else if ($prev_files_is_array) {
		print '?p=';
		print $file_id-1;
		print '&s=';
		print count($files[$file_id-1])-1;
	} else if ($file_id-1 == 1) {
		print '?p=1';
	} else {
		print '?p=';
		print $file_id-1;
	}
}

?><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<!--

		Webdesign index.php - a2.2
		https://github.com/vejnoe/index.php-for-webdesigns

		|||||||||||||||   Vejnø
		|||   |||   |||   Andreas Vejnø Andersen
		|||   |||   |||   www.vejnoe.dk
		|||||||||||||||   © <?php print date('Y'); ?>

	-->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title><?php
		print clean_title($file_name);
		if ($sub_file_id > 1) {
			print ' - ' . clean_title($files[$file_id][0]);
		}
		print ' — ' . $name;
	?></title>

	<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
	<!--<script src="jquery-1.9.1.js"></script>-->
	<script>
		$(document).ready(function() {
			$(".menu li.active").addClass('focus');
		});
		$("html").keydown(function(event) {
			if (event.which == 13) {
				// Enter
				if (!$('.menu').hasClass('view')) {
					$('.menu').addClass('view');
				} else if ($('.menu li.focus').hasClass('active')) {
					$('.menu').removeClass('view');
				} else {
					window.location = $('.menu li.focus a').attr('href');
				}
			} else if (event.which == 38) {
				// Up
				// TODO: Make this one and the next a function...
				if (!$('.menu').hasClass('view')) {
					$('.menu').addClass('view');
				};

				currentSelected = $('.menu li.focus').attr('id').substring(10,2);
				newSelection = currentSelected-1;
				if (newSelection != 0) {
					$('.menu li.focus').removeClass('focus');
					newSelection = '.menu li#nr' + newSelection;
					$(newSelection).addClass('focus');
				};

				return false;
			} else if (event.which == 40) {
				// Down
				if (!$('.menu').hasClass('view')) {
					$('.menu').addClass('view');
				};
				
				currentSelected = $('.menu li.focus').attr('id').substring(10,2);
				currentSelected = parseInt(currentSelected);
				newSelection = currentSelected+1;

				if (!$('.menu li').last().hasClass('focus')) {
					$('.menu li.focus').removeClass('focus');
					newSelection = '.menu li#nr' + newSelection.toString();
					$(newSelection).addClass('focus');
					console.log(newSelection);
					console.log($(newSelection));
				};

				return false;
				
			} else if (event.which == 39) {
				// Right
				if (!$('.menu').hasClass('view')) {
					currentSelected = $('.menu li.active').attr('id').substring(10,2);
					currentSelected = parseInt(currentSelected);
					newSelection = currentSelected+1;
					
					if (!$('.menu li').last().hasClass('active')) {
						newSelection = '.menu li#nr' + newSelection.toString() + ' a';
						window.location = $(newSelection).attr('href');
					};
				};
			} else if (event.which == 37) {
				// Left
				if (!$('.menu').hasClass('view')) {
					currentSelected = $('.menu li.active').attr('id').substring(10,2);
					currentSelected = parseInt(currentSelected);
					newSelection = currentSelected-1;
					
					if (newSelection != 0) {
						newSelection = '.menu li#nr' + newSelection.toString() + ' a';
						window.location = $(newSelection).attr('href');
					};
				};
			}
			//console.log(event.which);
		});
	</script>

	<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,600' rel='stylesheet'>
	<style>
	html,body,p,ol,ul,li,a,em {
		font-family: 'Source Sans Pro', sans-serif;
		font-weight: 300;
		font-size: 16px;
	}
	body {
		background: url('<?php print $file_path . '?v=' . $update; ?>') top center <?php print $background_color; ?> no-repeat;
		margin: 0 0 <?php print $margin_bottom; ?>;
	}
	.menu {
		background: #4d4a51;
		box-shadow: inset rgba(39, 37, 41, .30) -5px 0 8px;
		width: 300px;
		height: 100%;
		position: fixed;
		overflow-y: auto;
		visibility: visible;
		-webkit-transform: translate3d(-100%, 0, 0);
		transform: translate3d(-100%, 0, 0);

		-webkit-transition: all 0.5s;
		transition: all 0.5s;
	}
	.menu.view {
		visibility: visible;
		-webkit-transform: translate3d(0, 0, 0);
		transform: translate3d(0, 0, 0);
	}
	.menu ul {
		font: normal 16px / normal 'Varela Round', Helvetica, Arial, sans-serif;
		text-shadow: rgba(46, 43, 49, 0.35) 0 -1px 0;
		padding: 0;
		margin: 40px 20px;
	}
	.menu ul ul {
		border-bottom: 1px solid #46434a;
		margin: 0 0 20px;
		padding: 0 0 10px;
	}
	.menu li {
		color: #a19fa4;
		margin: 0 0 5px 0;
		list-style: none;
	}
	.menu ul li.active a {
		color: <?php print $active_color; ?>;
	}
	.menu ul li.folder li.active {
		list-style: disc;
		color: <?php print $active_color; ?>;
	}
	.menu li.folder {
		margin: 0 0 5px;
		list-style: none;
	}
	.menu li.folder li {
		color: #a19fa4;
		margin: 0 0 5px 20px;
		list-style: circle;
	}
	.menu h3 {
		border-top: 1px solid #46434a;
		font-size: 18px;
		font-weight: normal;
		color: #e7e5e9;
		margin: 20px 0 5px;
		padding: 10px 0 0;
	}
	.menu li.folder+li.folder h3,
	.menu li:first-child h3 {
		border-top: none;
		margin: -10px 0 5px;
		padding: 0 0 0;
	}
	.menu a {
		color: #a19fa4;
		text-decoration: none;
	}
	.menu a:hover {
		color: #e7e5e9;
	}
	.menu li.focus,
	.menu li.focus a {
		color: #fff;
	}
	.info {
		text-align: right;
		font-size: 12px;
		width: 280px;
		padding: 10px;
	}
	.info a {
		font-size: 12px;
	}
	</style>
</head>
<body><?php
	// Debugging
	if(isset($debug)) { ?>
	<div style="background: rgba(255,255,255,.8); margin: 40px; padding: 30px; position: fixed;">
		<pre>

??: <?php print_r((substr('readme.smd', -3, 1) == '.' || substr('readme.smd', -4, 1) == '.' || substr('readme.smd', -5, 1) == '.')) ?>

$_GET["p"]: <?php if (isset($_GET["p"])) { print $_GET["p"]; } else { print 'Er ikke sat'; } ?>

$file_id: <?php print_r($file_id);	?>

$sub_file_id: <?php print $sub_file_id; ?>

$file_path: <?php print $file_path; ?>

$file_name: <?php print $file_name; ?>

empty($files[$file_id][$sub_file_id+1]): <?php print_r(empty($files[$file_id][$sub_file_id+1])); ?>

clean_title($file_name): <?php print clean_title($file_name); ?>



Nummer? <?php print_r(is_numeric(substr($file_path,0,1))); ?>

gettype: <?php // print gettype($files[0]); ?>

Next: <?php next_url($files, $file_id, $sub_file_id); ?>

<?php if (gettype($files[$file_id]) == 'array') {
	print 'Dette er et array()';
} ?>



<?php print_r($files); ?>

		</pre>
	</div>
	<?php }
	// END if debug
	?>
	
	
	<?php if (count($files) == 1): ?>
		<div style="display: block; width: <?php print $page_min_width; ?>; height: <?php print $height; ?>px; margin: auto;"></div>
	<?php else: ?>
		<div class="menu">
			<div class="info"><a href="https://github.com/vejnoe/index.php-for-webdesigns" target="_blank">a2.2</a></div>
			<ul class="navigation">
				<?php
				
				$i = 1;
				$ii = 1;
				$iii = 1;
				
				foreach ($files as $file)
					{
						if (gettype($file) == 'array') {
							print '<li class="folder"><h3>' . clean_title($file[0]) . '</h3><ul>';
							foreach (array_slice($file, 1) as $subfile) {
								print '<li id="nr'. $iii .'"';
								if ($i == $file_id && $ii == $sub_file_id) {
									print ' class="active"';
								}
								print '><a href="?p=' . $i . '&s=' . $ii . '">' . clean_title($subfile) . '</a></li>';
								$ii++;
								$iii++;
							}
							$ii = 1;
							print '</ul></li>';
						} else {
							print '<li id="nr'. $iii .'"';
							if ($i == $file_id) {
								print ' class="active"';
							}
							print '><a href="?p=' . $i . '">' . clean_title($file) . '</a></li>';
							$iii++;
						}
						$i++;
						
					}
				?>
			</ul>
			<!--<a href="<?php prev_url($files, $file_id, $sub_file_id); ?>">Back [<?php prev_url($files, $file_id, $sub_file_id); ?>]</a>-->
		</div>

		<a href="<?php next_url($files, $file_id, $sub_file_id); ?>" style="display: block; width: <?php print $page_min_width; ?>; height: <?php print $height; ?>px; margin: auto;"></a>
	<?php endif; ?>
</body>
</html>