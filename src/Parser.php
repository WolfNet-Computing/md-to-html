<?php
	namespace WolfNet_Computing\MD_Reader;

	class Parser {
		private $FindMDNewline = '/\s{2,}/';
		private $OriginalFileContent;

		function __construct($file) {
			$mdfile = fopen($file, 'r') or die('Unable to open file!');
			$this->OriginalFileContent = fread($mdfile, filesize($file));
			fclose($mdfile);
			clearstatcache();
		}

		# Returns the HTML formatted array of lines contained in the $HtmlFormattedMarkdown array...
		function Parse() {
			# First to split the string by the markdown double space newline and append the HTML newline to the end of each of the strings in the resulting array...
			$string = preg_replace($this->FindMDNewline, '<br>\\n', $this->OriginalFileContent);
			$array = preg_split('/\n/', $string);
			# More processing to go here...
			return $array;
		}
	}
?>