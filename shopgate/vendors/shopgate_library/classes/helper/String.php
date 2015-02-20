<?php
/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

class Shopgate_Helper_String {

	/**
	 * Removes all disallowed HTML tags from a given string.
	 *
	 * By default the following are allowed:
	 *
	 * "ADDRESS", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE", "BODY", "BR",
	 * "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
	 * "EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML",
	 * "ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
	 * "IMG", "INPUT", "P", "SAMP", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
	 * "TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR"
	 *
	 *
	 * @param string $string The input string to be filtered.
	 * @param string[] $removeTags The tags to be removed.
	 * @param string[] $additionalAllowedTags Additional tags to be allowed.
	 *
	 * @return string The sanitized string.
	 */
	public function removeTagsFromString($string, $removeTags = array(), $additionalAllowedTags = array()) {
		// all tags available
		$allowedTags = array("ADDRESS", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE",
							 "BODY", "BR", "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
							 "EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML", "IMG", "INPUT",
							 "ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
							 "P", "SAMP", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
							 "TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR"
		);

		foreach ($allowedTags as &$t) $t = strtolower($t);
		foreach ($removeTags as &$t) $t = strtolower($t);
		foreach ($additionalAllowedTags as &$t) $t = strtolower($t);

		// some tags must be removed completely (including content)
		$string = preg_replace('#<script([^>]*?)>(.*?)</script>#is', '', $string);
		$string = preg_replace('#<style([^>]*?)>(.*?)</style>#is', '', $string);
		$string = preg_replace('#<link([^>]*?)>(.*?)</link>#is', '', $string);

		$string = preg_replace('#<script([^>]*?)/>#is', '', $string);
		$string = preg_replace('#<style([^>]*?)/>#is', '', $string);
		$string = preg_replace('#<link([^>]*?)/>#is', '', $string);

		// add the additional allowed tags to the list
		$allowedTags = array_merge($allowedTags, $additionalAllowedTags);

		// strip the disallowed tags from the list
		$allowedTags = array_diff($allowedTags, $removeTags);

		// add HTML brackets
		foreach ($allowedTags as &$t) $t = "<$t>";

		// let PHP sanitize the string and return it
		return strip_tags($string, implode(",", $allowedTags));
	}
}
