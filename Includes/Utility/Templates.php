<?php
/************************************************************
 * E-Storefront by Ben M. Ward is licensed under a
 * Creative Commons Attribution 3.0 Unported License.
 * http://creativecommons.org/licenses/by/3.0/
 * Based on a work at https://github.com/Foran/E-Storefront.
 ***********************************************************/

/**
 * Loads, Populates with macros and Displays a template if it can be found
 *
 * @param string $template Template name
 * @param array $macros  Assoiciative Array of macros for search and replace
 */
function DisplayTemplate($template, array $macros = array(), $gzip = false, $group = 'Default') {
	$buffer = LoadTemplate($template, $macros, $group);
	if($gzip && $buffer) $buffer = gzencode($buffer);
	if($buffer === false) echo "No suitable template found";
	else echo $buffer;
}

/**
 * Loads a Template and populates it with macros
 *
 * @param string $template Template name
 * @param array $macros Assoiciative Array of macros for search and replace
 * @return string|bool Populated template. On failure false.
 */
function LoadTemplate($template, array $macros = array(), $group = 'Default') {
	$retval = false;
	
	if(!is_null($template) && file_exists("Templates/".@str_replace('/', '_', @$group)."/".@$template)) {
		if(!isset($macros['PHP_SELF'])) $macros['PHP_SELF'] = $_SERVER['PHP_SELF'];
		$buffer = @file_get_contents("Templates/".@str_replace('/', '_', @$group)."/".$template);
		if(strlen($buffer) > 0) {
			foreach($macros as $macro => $value) {
				$buffer = str_replace("<%$macro%>", $value, $buffer);
			}
			$buffer = preg_replace_callback("|[<][%][^%:]+:?([^%]*)[%][>]|", create_function('$matches', 'return "";'), $buffer);
		}
		else $buffer = @$macros['content'];
		$retval = $buffer;
	}
	elseif($group != 'Default') $retval = LoadTemplate($template, $macros);
	
	return $retval;
}
?>