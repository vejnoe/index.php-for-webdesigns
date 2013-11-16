<?php

// PHP Design Browser a2.0.2


// Settings
$name = "Kunde navn";
$total_pages = 7;
$background_color = "yellow";
$page_min_width = "960px";
$margin_bottom = '0px';
$update = 1;

$debug = false;

# Lav url om til noget mere sexet fx. #1/1





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
		substr ($file_name,0,1) == '_' ||
		substr ($file_name,0,1) == '$'
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


// File name !!!!!!!!!!!!!!!!!!!!!!!
if (gettype($files[$file_id]) == 'array') {
	$file_name = $files[$file_id][$sub_file_id];
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
// function prev_url($files, $file_id, $sub_file_id) {
// 	if (gettype($files[$file_id-1]) == 'array') {
// 		print '?p=';
// 		print $file_id-1;
// 		print '&s=';
// 		print $sub_file_id-1;
// 	} else {
// 		print '?p=';
// 		print $file_id-1;
// 	}
// }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<!--

		||||||||||||||| 	Vejnø
		|||   |||   ||| 	Andreas Vejnø Andersen
		|||   |||   ||| 	www.vejnoe.dk
		||||||||||||||| 	© <?php print date('Y'); ?>

	-->
	<title><?php print clean_title($file_name) . ' - ' . $name; ?></title>
</head>
<body style="background: url(<?php print $file_path . '?v=' . $update . ') top center ' . $background_color . ' no-repeat; margin: 0 0 ' . $margin_bottom; ?>;">
	
	<?php if($debug) { ?>
	<div style="background: rgba(255,255,255,.8); margin: 40px; padding: 30px; position: fixed;">
		<pre>
???: <?php print_r(gettype($files[$file_id]) != 'array'); ?>

???: <?php print_r($file_id == count($files)) ?>

HER ER LØSNINGEN!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! <?php print_r(count($files[8])); ?>

$_GET["p"]: <?php if (isset($_GET["p"])) { print $_GET["p"]; } else { print 'Er ikke sat'; } ?>

$file_id: <?php print_r($file_id);  ?>

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
	<?php } ?>
	
	
	<?php if ($total_pages == 1): ?>
		<div style="display: block; width: <?php print $page_min_width; ?>; height: <?php print $height; ?>px; margin: auto;"></div>
	<?php else: ?>
		<a href="<?php next_url($files, $file_id, $sub_file_id); ?>" style="display: block; width: <?php print $page_min_width; ?>; height: <?php print $height; ?>px; margin: auto;"></a>
	<?php endif; ?>
</body>
</html>