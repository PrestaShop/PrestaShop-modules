<?php

class HelperFn
{
	public static function safeFileName($origFileName, $type = 'noDotSlash')
	{
		$fileName = preg_replace('/__/', '$1/', $origFileName, 3);
		$onlyAllowedChars = preg_match('/^[0-9a-z\.\/\-_]+$/ui', $fileName); /* Allow letters, number, dot, forwardslash (do NOT allow \, <, >, %, #, $, etc...) */
		return ((preg_match('/^[0-9a-z\.\-_]+$/ui', $fileName) && $type == 'strict') 
				|| (!strpos($fileName, './') && $type == 'noDotSlash') 
				&& $onlyAllowedChars) ? 
			$origFileName : 
			false;
	}

	public static function noXSS($content, $safeHtmlAllowed = false)
	{
		return $safeHtmlAllowed ? strip_tags($content, '<a><b><div><strong><br><p><span><ul><li><label><h1><h3><h4><h5><h6><img><i><dl><dt><dd><ol>') : 
			htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
	}
}