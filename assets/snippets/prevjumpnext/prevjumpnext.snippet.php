<?php
/**
 * PrevJumpNext
 *
 * @category 	snippet
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author		Jako (thomas.jakobi@partout.info)
 * @internal    Based on PrevJumpNext of Jeroen Bosch, OncleBen31 and Aaron Heinrich
 */
define('PJN_PATH', str_replace(MODX_BASE_PATH, '', str_replace('\\', '/', realpath(dirname(__FILE__)))) . '/');
define('PJN_BASE_PATH', MODX_BASE_PATH . PJN_PATH);

if (!class_exists('newChunkie')) {
	include_once PJN_BASE_PATH . 'classes/newchunkie.class.php';
}

// Language setting
$language = (isset($language)) ? $language : 'english';
include (PJN_BASE_PATH . 'lang/english.inc.php');
if ($language != 'english' && file_exists(PJN_BASE_PATH . 'lang/' . $language . '.inc.php')) {
	include (PJN_BASE_PATH . 'lang/' . $language . '.inc.php');
}

// Settings
$startId = (isset($startId)) ? $startId : $modx->documentObject['parent'];
$sortBy = (isset($sortBy)) ? $sortBy : 'createdon';
$sortDir = (isset($sortDir) && $sortDir == 'DESC') ? 'DESC' : 'ASC';
$displayTitle = (isset($displayTitle) && !$displayTitle) ? FALSE : TRUE;
$displayFixed = (isset($displayFixed) && $displayFixed) ? TRUE : FALSE;
$usePlaceHolder = (isset($usePlaceHolder) && $usePlaceHolder) ? TRUE : FALSE;
$useJump = (isset($useJump)) ? $useJump : FALSE;
$indexDocumentId = (isset($indexDocumentId)) ? $indexDocumentId : $modx->documentObject['parent'];
$displayNoPrevNext = (isset($displayNoPrevNext)) ? $displayNoPrevNext : 0;
$noPrevNextText = (isset($noPrevNextText)) ? $noPrevNextText : $_lang['noPrevNext'];
$indexText = (isset($indexText)) ? $indexText : $_lang['index'];
$jumpText = (isset($jumpText)) ? $jumpText : $_lang['jump'];
$firstText = (isset($firstText)) ? $firstText : $_lang['first'];
$prevText = (isset($prevText)) ? $prevText : $_lang['prev'];
$nextText = (isset($nextText)) ? $nextText : $_lang['next'];
$lastText = (isset($lastText)) ? $lastText : $_lang['last'];
$exclude = (isset($exclude)) ? split(',', $exclude) : array();
$separator = (isset($separator)) ? $separator : ' | ';
$displayIndex = (isset($displayIndex) && !$displayIndex) ? FALSE : TRUE;
$displayTotal = (isset($displayTotal) && !$displayTotal) ? FALSE : TRUE;
$firstSymbol = (isset($firstSymbol)) ? $firstSymbol : '';
$prevSymbol = (isset($prevSymbol)) ? $prevSymbol : '';
$nextSymbol = (isset($nextSymbol)) ? $nextSymbol : '';
$lastSymbol = (isset($lastSymbol)) ? $lastSymbol : '';
$firstClass = (isset($firstClass)) ? $firstClass : 'first';
$prevClass = (isset($prevClass)) ? $prevClass : 'prev';
$nextClass = (isset($nextClass)) ? $nextClass : 'next';
$lastClass = (isset($lastClass)) ? $lastClass : 'last';
$indexClass = (isset($indexClass)) ? $indexClass : 'index';
$totalClass = (isset($totalClass)) ? $totalClass : 'total';
$currentNumberClass = (isset($currentNumberClass)) ? $currentNumberClass : 'currentNumber';
$totalNumberClass = (isset($totalNumberClass)) ? $totalNumberClass : 'totalNumber';
$recordTypeName = (isset($recordTypeName)) ? $recordTypeName : 'record';
$includeFolders = (isset($includeFolders) && !$includeFolders) ? FALSE : TRUE;
$ignoreHidden = (isset($ignoreHidden) && !$ignoreHidden) ? FALSE : TRUE;
$maxTitleChars = (isset($maxTitleChars)) ? $maxTitleChars : 0;
$level = (isset($level)) ? $level : 0;
$circle = (isset($circle)) ? $circle : 0;
$useYams = (isset($useYams) && $useYams) ? TRUE : FALSE;
$langid = (isset($langid)) ? $langid : '';

// Check for YAMS and don't execute the snippet if langid is not set
if ($useYams) {
	include_once(MODX_BASE_PATH . 'assets/modules/yams/class/yams.class.inc.php');
	$yams = YAMS::GetInstance();
	//
	if (!isset($langid) || !$yams->IsActiveLangId($langid)) {
		return '';
	}
}

if (!function_exists('getActiveChildrenLevel')) {

	function getActiveChildrenLevel($id = 0, $sort = 'menuindex', $dir = 'ASC', $fields = 'id, pagetitle, description, parent, alias, menutitle', $level = 1) {
		global $modx;

		$children = $modx->getActiveChildren($id, $sort, $dir, $fields);
		if ($level) {
			$activeChildrenLevel = array();
			foreach ($children as $child) {
				if ($child['isfolder']) {
					$levelChildren = getActiveChildrenLevel($child['id'], $sort, $dir, $fields, $level - 1);
					$activeChildrenLevel = array_merge($activeChildrenLevel, $levelChildren);
				}
			}
			$children = array_merge($children, $activeChildrenLevel);
		}
		return $children;
	}

}

$id = $modx->documentIdentifier;
$fields = 'id,pagetitle,isfolder,hidemenu'; // what fields do you want to know
$children = getActiveChildrenLevel($startId, $sortBy, $sortDir, $fields, $level);
$my_array = array();
$y = 1;

// sorting the documents, giving them a sequential and searchable index
foreach ($children as $child) {
	if (!in_array($child['id'], $exclude) && (!$ignoreHidden || !$child['hidemenu']) && !in_array($child['id'], $exclude) && ($includeFolders == 1 || $child['isfolder'] == 0)) {
		if ($child['id'] == $id) {
			$current = $y; // The current page has number $y in the array
		}
		$my_array[$y] = $child;
		$y++;
	}
}

$templateLink = '<a href="[+link+]" class="[+class+]" title="[+prefix+][+title+][+suffix+]">[+prefix+][+title+][+suffix+]</a>';
$templateJump = '<form action="" name="jump" id="jump">' . "\n"
		. '<select name="myjumpbox" onchange="location.href=jump.myjumpbox.options[selectedIndex].value">' . "\n"
		. '[+wrapper+]' . "\n"
		. '</select>' . "\n"
		. '</form>';
$templateJumpOption = '<option value="[+link+]">[+title+]</option>';

$output = '';
if (count($my_array)) {
	$prev = $current - 1;
	$next = $current + 1;
	$total = $y - 1;

	// Construction of the elements
	$parser = new newChunkie($modx, array('basepath' => PJN_PATH));

	// Total line
	$parser->setPlaceholder('recordTypeName', $recordTypeName);
	$parser->setPlaceholder('current', '<span class="' . $currentNumberClass . '">' . $current . '</span>');
	$parser->setPlaceholder('total', '<span class="' . $totalNumberClass . '">' . $total . '</span>');
	$parser->setTpl($_lang['total']);
	$parser->prepareTemplate();
	$totalPlaceHolder = $parser->process();

	// First item
	if ($useYams && $yams->IsMultilingualDocument($my_array[1]['id'])) {
		$parser->setPlaceholder('link', $yams->ConstructResolvedURL($langid, $my_array[1]['id']));
	} else {
		$parser->setPlaceholder('link', $modx->makeUrl($my_array[1]['id']));
	}
	$parser->setPlaceholder('class', $firstClass);
	$parser->setPlaceholder('prefix', $firstSymbol);
	$parser->setPlaceholder('title', $firstText);
	$parser->setTpl($templateLink);
	$parser->prepareTemplate();
	$firstPlaceHolder = $parser->process();

	// Prev item
	if ($prev > 0) {
		$parser->setTpl($templateLink);
	} else {
		$parser->setTpl('');
	}
	$parser->setPlaceholder('class', $prevClass);
	$parser->setPlaceholder('prefix', $prevSymbol);
	if ($prev > 0) {
		if ($displayTitle) {
			if ($useYams && $yams->IsMultilingualDocument($my_array[$prev]['id'])) {
				$yamsTitle = $modx->getTemplateVarOutput(array('pagetitle_' . $langid), $my_array[$prev]['id']);
				$prevText = ($maxTitleChars > 0) ? substr($yamsTitle['pagetitle_' . $langid], 0, $maxTitleChars) . $_lang['ellipse'] : $yamsTitle['pagetitle_' . $langid];
			} else {
				$prevText = ($maxTitleChars > 0) ? substr($my_array[$prev]['pagetitle'], 0, $maxTitleChars) . $_lang['ellipse'] : $my_array[$prev]['pagetitle'];
			}
		}
		if ($useYams && $yams->IsMultilingualDocument($my_array[$prev]['id'])) {
			$parser->setPlaceholder('link', $yams->ConstructResolvedURL($langid, $my_array[$prev]['id']));
		} else {
			$parser->setPlaceholder('link', $modx->makeUrl($my_array[$prev]['id']));
		}
	} else {
		if ($circle) {
			$parser->setTpl($templateLink);
			if ($useYams && $yams->IsMultilingualDocument($my_array[$total]['id'])) {
				$yamsTitle = $modx->getTemplateVarOutput(array('pagetitle_' . $langid), $my_array[$total]['id']);
				$prevText = ($maxTitleChars > 0) ? substr($yamsTitle['pagetitle_' . $langid], 0, $maxTitleChars) . $_lang['ellipse'] : $yamsTitle['pagetitle_' . $langid];
				$parser->setPlaceholder('link', $yams->ConstructResolvedURL($langid, $my_array[$total]['id']));
			} else {
				$prevText = ($maxTitleChars > 0) ? substr($my_array[$total]['pagetitle'], 0, $maxTitleChars) . $_lang['ellipse'] : $my_array[$total]['pagetitle'];
				$parser->setPlaceholder('link', $modx->makeUrl($my_array[$total]['id']));
			}
		}
		if ($displayNoPrevNext) {
			$parser->setTpl($_lang['noPrevNext']);
		}
	}
	$parser->setPlaceholder('title', $prevText);
	$parser->prepareTemplate();
	$prevPlaceHolder = $parser->process();

	// Select List
	$i = 1;
	foreach ($my_array as $my_page) {
		if ($useYams && $yams->IsMultilingualDocument($my_page['id'])) {
			$yamsTitle = $modx->getTemplateVarOutput(array('pagetitle_' . $langid), $my_page['id']);
			$jumpTitle = ($maxTitleChars > 0) ? substr($yamsTitle['pagetitle_' . $langid], 0, $maxTitleChars) . $_lang['ellipse'] : $yamsTitle['pagetitle_' . $langid];
		} else {
			$jumpTitle = ($maxTitleChars > 0) ? substr($my_page['pagetitle'], 0, $maxTitleChars) . $_lang['ellipse'] : $my_page['pagetitle'];
		}
		if ($useYams && $yams->IsMultilingualDocument($my_array[$prev]['id'])) {
			$parser->setPlaceholder('link', $yams->ConstructResolvedURL($langid, $my_page['id']), 'option');
		} else {
			$parser->setPlaceholder('link', $modx->makeUrl($my_page['id']), 'option');
		}
		$parser->setPlaceholder($i . '.title', $jumpTitle, 'option');
		$parser->setTpl($templateJumpOption);
		$parser->prepareTemplate($i, array(), 'option');
		$i++;
	}
	$parser->setPlaceholder('wrapper', $parser->process('option', "\n"), 'wrapper');
	$parser->setTpl($templateJump);
	$parser->prepareTemplate('', array(), 'wrapper');
	$jumpPlaceHolder = $parser->process('wrapper');

	// Index item
	if ($useYams && $yams->IsMultilingualDocument($indexDocumentId)) {
		$parser->setPlaceholder('link', $yams->ConstructResolvedURL($langid, $indexDocumentId));
	} else {
		$parser->setPlaceholder('link', $modx->makeUrl($indexDocumentId));
	}
	$parser->setPlaceholder('class', $indexClass);
	$parser->setPlaceholder('title', $indexText);
	$parser->setTpl($templateLink);
	$parser->prepareTemplate();
	$indexPlaceHolder = $parser->process();

	// Next item
	if ($next <= $total) {
		$parser->setTpl($templateLink);
	} else {
		$parser->setTpl('');
	}
	$parser->setPlaceholder('class', $nextClass);
	$parser->setPlaceholder('suffix', $nextSymbol);
	if ($next <= $total) {
		if ($displayTitle) {
			if ($useYams && $yams->IsMultilingualDocument($my_array[$next]['id'])) {
				$yamsTitle = $modx->getTemplateVarOutput(array('pagetitle_' . $langid), $my_array[$next]['id']);
				$nextText = ($maxTitleChars > 0) ? substr($yamsTitle['pagetitle_' . $langid], 0, $maxTitleChars) . $_lang['ellipse'] : $yamsTitle['pagetitle_' . $langid];
			} else {
				$nextText = ($maxTitleChars > 0) ? substr($my_array[$next]['pagetitle'], 0, $maxTitleChars) . $_lang['ellipse'] : $my_array[$next]['pagetitle'];
			}
		}
		if ($useYams && $yams->IsMultilingualDocument($my_array[$next]['id'])) {
			$parser->setPlaceholder('link', $yams->ConstructResolvedURL($langid, $my_array[$next]['id']));
		} else {
			$parser->setPlaceholder('link', $modx->makeUrl($my_array[$next]['id']));
		}
	} else {
		if ($circle) {
			$parser->setTpl($templateLink);
			if ($useYams && $yams->IsMultilingualDocument($my_array[1]['id'])) {
				$yamsTitle = $modx->getTemplateVarOutput(array('pagetitle_' . $langid), $my_array[1]['id']);
				$prevText = ($maxTitleChars > 0) ? substr($yamsTitle['pagetitle_' . $langid], 0, $maxTitleChars) . $_lang['ellipse'] : $yamsTitle['pagetitle_' . $langid];
				$parser->setPlaceholder('link', $yams->ConstructResolvedURL($langid, $my_array[1]['id']));
			} else {
				$prevText = ($maxTitleChars > 0) ? substr($my_array[1]['pagetitle'], 0, $maxTitleChars) . $_lang['ellipse'] : $my_array[1]['pagetitle'];
				$parser->setPlaceholder('link', $modx->makeUrl($my_array[1]['id']));
			}
		}
		if ($displayNoPrevNext) {
			$parser->setTpl($_lang['noPrevNext']);
		}
	}
	$parser->setPlaceholder('title', $nextText);
	$parser->prepareTemplate();
	$nextPlaceHolder = $parser->process();

	// Last item
	if ($useYams && $yams->IsMultilingualDocument($my_array[$total]['id'])) {
		$parser->setPlaceholder('link', $yams->ConstructResolvedURL($langid, $my_array[$total]['id']));
	} else {
		$parser->setPlaceholder('link', $modx->makeUrl($my_array[$total]['id']));
	}
	$parser->setPlaceholder('class', $lastClass);
	$parser->setPlaceholder('suffix', $lastSymbol);
	$parser->setPlaceholder('title', $lastText);
	$parser->setTpl($templateLink);
	$parser->prepareTemplate();
	$lastPlaceHolder = $parser->process();

	if (!$usePlaceHolder) {
		// prepare output
		$output = array();
		if ($displayTotal) {
			$output[] = '<span class="' . $totalClass . '">' . $totalPlaceHolder . '</span>';
		}
		if ($displayFixed && $prev - 1 > 0) {
			$output[] = $firstPlaceHolder . $separator;
		}
		if ($prev == 0 && $circle) {
			$output[] = $prevPlaceHolder . $separator;
		}
		if ($prev > 0 || $displayNoPrevNext) {
			$output[] = $prevPlaceHolder . (($displayIndex || $useJump || $next <= $total) ? $separator : '');
		}
		if ($useJump) {
			$output[] = $jumpPlaceHolder . (($next <= $total || $displayNoPrevNext) ? $separator : '');
		} elseif ($displayIndex) {
			$output[] = $indexPlaceHolder . (($next <= $total || $displayNoPrevNext) ? $separator : '');
		}
		if ($next <= $total || $displayNoPrevNext) {
			$output[] = $nextPlaceHolder . (($displayFixed && $next + 1 <= $total) ? $separator : '');
		}
		if ($next == $total && $circle) {
			$output[] = $nextPlaceHolder . $separator;
		}
		if ($displayFixed && $next + 1 <= $total) {
			$output[] = $lastPlaceHolder;
		}
		$output = implode("\n", $output);
	} else {
		// Set PlaceHolder
		$modx->setPlaceholder('PJN_total', $totalPlaceHolder);
		$modx->setPlaceholder('PJN_first', $firstPlaceHolder);
		$modx->setPlaceholder('PJN_prev', $prevPlaceHolder);
		$modx->setPlaceholder('PJN_jump', $jumpPlaceHolder);
		$modx->setPlaceholder('PJN_index', $indexPlaceHolder);
		$modx->setPlaceholder('PJN_next', $nextPlaceHolder);
		$modx->setPlaceholder('PJN_last', $lastPlaceHolder);
	}
}
return $output;
?>