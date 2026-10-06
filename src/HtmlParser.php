<?php

/*
	Copyright 2025 WolfNet Computing C.I.C.

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

	namespace MD_Reader;

	class HtmlParser {
		private $NumberOfPasses = 3;

		private $Configuration;
		private $OriginalFileContent;

		private $FindMDNewline = "/[ ]{2}/";
		private $FindMDHeader1 = '/^(# )/';
		private $FindMDHeader2 = '/^(## )/';
		private $FindMDHeader3 = '/^(### )/';
		private $FindMDHeader4 = '/^(#### )/';
		private $FindMDHeader5 = '/^(##### )/';
		private $FindMDHeader6 = '/^(###### )/';
		private $FindMDLink1 = '/\[.+\]\(.+\)/';
		private $FindMDLink2 = '/\).+\[/';
		private $FindMDUnorderedListItem = '/^( (?:-|\*|\+) )/';
		private $FindMDFirstOrderedListItem = '/^( [1][\.] )/';
		private $FindMDAnyOrderedListItem = '/^( [\d]+[\.] )/';
		private $FindMDBoldTextItem1 = '/[\*]{2}.+[\*]{2}/';
		private $FindMDBoldTextItem2 = '/[\_]{2}.+[\_]{2}/';
		private $FindMDItalicTextItem1 = '/[\*].+[\*]/';
		private $FindMDItalicTextItem2 = '/[\_].+[\_]/';
		private $FindMDEscape = '/[\\\]{1}/';
		private $FindMDCodeLine = '/[`]{1}.+[`]{1}/';
		private $FindMDCodeBlock = '/[`]{3}/';

		function __construct($configref) {
			$this->Configuration = $configref->Configuration;
			$mdfile = fopen($this->Configuration["doc_root"] . "/" . $this->Configuration["doc_file"], 'r') or die('Unable to open file!');
			$this->OriginalFileContent = fread($mdfile, filesize($this->Configuration["doc_root"] . "/" . $this->Configuration["doc_file"]));
			fclose($mdfile);
			clearstatcache();
		}

		function InsertIntoArray(&$array, $index, $data) {
			$array = array_pad($array, count($array) + 1, "");
			for ($i = count($array) - 1; $i > $index; $i--) {
				$array[$i] = $array[$i - 1];
			}
			$array[$index] = $data;
		}

		# Returns the HTML formatted array of lines contained in the $HtmlFormattedMarkdown array...
		function DisplayFormatted() {
			$FormattedOutput = explode("\n", $this->OriginalFileContent);
			$wasunorderedlist = False;
			$wasorderedlist = False;
			$wascodeblock = False;
			$this->InsertIntoArray($FormattedOutput, 0, "<body>");
			for ($i = 0; $i < count($FormattedOutput); $i++) {
				$FormattedOutput[$i] = str_replace(array("\r\n", "\n", "\r"), "", $FormattedOutput[$i]);
				# Check for escaped characters...
				if (preg_match('/[`]{3}/', $FormattedOutput[$i], $regexarray1) == 1) {
					if ($wascodeblock == False) {
						$wascodeblock = True;
						$FormattedOutput[$i] = "<code>";
					} else {
						$wascodeblock = False;
						$FormattedOutput[$i] = "</code>";
					}
				}
				if (preg_match_all('/([\\\][\*])/', $FormattedOutput[$i], $regexarray1) > 0) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						$FormattedOutput[$i] = preg_replace('/([\\\][\*])/', "&ast;", $FormattedOutput[$i]);
					}
				}
				if (preg_match_all('/([\\\][\_])/', $FormattedOutput[$i], $regexarray1) > 0) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						$FormattedOutput[$i] = preg_replace('/([\\\][\_])/', "&lowbar;", $FormattedOutput[$i]);
					}
				}
				if (preg_match_all('/([\\\][\\\])/', $FormattedOutput[$i], $regexarray1) > 0) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						$FormattedOutput[$i] = preg_replace('/([\\\][\\\])/', "&bsol;", $FormattedOutput[$i]);
					}
				}
				if (preg_match_all('/([\\\][!])/', $FormattedOutput[$i], $regexarray1) > 0) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						$FormattedOutput[$i] = preg_replace('/([\\\][!])/', "&excl;", $FormattedOutput[$i]);
					}
				}
				if (preg_match_all('/([\\\][`])/', $FormattedOutput[$i], $regexarray1) > 0) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						$FormattedOutput[$i] = preg_replace('/([\\\][`])/', "&grave;", $FormattedOutput[$i]);
					}
				}
				# if Markdown Code Snippet...
				if (preg_match($this->FindMDCodeLine, $FormattedOutput[$i], $regexarray1) == 1) {
					if (preg_match_all($this->FindMDCodeLine, substr($regexarray1[0], 1, strlen($regexarray1[0]) - 2), $regexarray2)  > 0) {
						$finalstr = "";
						for ($j = 0; $j < count($regexarray1); $j++) {
							$coderemoved = explode("`", substr($regexarray1[$j], 1, strlen($regexarray1[$j]) - 2));
							for ($l = 0; $l < count($coderemoved); $l++) {
								if ($coderemoved[$l] == "") {
									array_splice($coderemoved, $l, 1);
									$l--;
								}
							}
							for ($l = 0; $l < count($coderemoved); $l += 2) {
								if (array_key_exists($l + 1, $coderemoved)) {
									$finalstr = $finalstr . "<code>" . $coderemoved[$l] . "</code>" . $coderemoved[$l + 1];
								} else {
									$finalstr = $finalstr . "<code>" . $coderemoved[$l] . "</code>";
								}
							}
							$FormattedOutput[$i] = preg_replace($this->FindMDCodeLine, $finalstr, $FormattedOutput[$i]);
						}
					} else {
						$FormattedOutput[$i] = preg_replace($this->FindMDCodeLine, "<code>" . substr($regexarray1[0], 1, strlen($regexarray1[0]) - 2) . "</code>", $FormattedOutput[$i]);
					}
				}
				# if Markdown Bold Text...
				if (preg_match($this->FindMDBoldTextItem1, $FormattedOutput[$i], $regexarray1) == 1) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						if (preg_match_all($this->FindMDBoldTextItem1, $regexarray1[$j], $regexarray2) > 0) {
							$finalstr = "";
							for ($k = 0; $k < count($regexarray2); $k++) {
								for ($l = 0; $l < count($regexarray2[$k]); $l++) {
									$boldremoved = explode($regexarray2[$k][$l], $regexarray1[$j]);
									if ($k != 0) {
										$finalstr = $finalstr . $boldremoved[0];
									}
									$finalstr = $finalstr . preg_replace('/(^[\*]{2})/', "<strong>", substr($regexarray2[$k][$l], 0, strlen($regexarray2[$k][$l]) - 2)) . "</strong>";
								}
							}
							$FormattedOutput[$i] = preg_replace($this->FindMDBoldTextItem1, $finalstr, $FormattedOutput[$i]);
						} else {
							$FormattedOutput[$i] = preg_replace('/(^[\*]{2})/', "<strong>", substr($FormattedOutput[$i], 2, strlen($FormattedOutput[$i] - 4))) . "</strong>";
						}
					}
				}
				if (preg_match($this->FindMDBoldTextItem2, $FormattedOutput[$i], $regexarray1) == 1) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						if (preg_match_all($this->FindMDBoldTextItem2, $regexarray1[$j], $regexarray2) > 0) {
							$finalstr = "";
							for ($k = 0; $k < count($regexarray2); $k++) {
								for ($l = 0; $l < count($regexarray2[$k]); $l++) {
									$boldremoved = explode($regexarray2[$k][$l], $regexarray1[$j]);
									if ($k != 0) {
										$finalstr = $finalstr . $boldremoved[0];
									}
									$finalstr = $finalstr . preg_replace('/(^[\_]{2})/', "<strong>", substr($regexarray2[$k][$l], 0, strlen($regexarray2[$k][$l]) - 2)) . "</strong>";
								}
							}
							$FormattedOutput[$i] = preg_replace($this->FindMDBoldTextItem2, $finalstr, $FormattedOutput[$i]);
						} else {
							$FormattedOutput[$i] = preg_replace('/(^[\_]{2})/', "<strong>", substr($FormattedOutput[$i], 2, strlen($FormattedOutput[$i] - 4))) . "</strong>";
						}
					}
				}
				# if Markdown Italic Text...
				if (preg_match($this->FindMDItalicTextItem1, $FormattedOutput[$i], $regexarray1) == 1) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						if (preg_match_all($this->FindMDItalicTextItem1, $regexarray1[$j], $regexarray2) > 0) {
							$finalstr = "";
							for ($k = 0; $k < count($regexarray2); $k++) {
								for ($l = 0; $l < count($regexarray2[$k]); $l++) {
									$italicremoved = explode($regexarray2[$k][$l], $regexarray1[$j]);
									if ($k != 0) {
										$finalstr = $finalstr . $italicremoved[0];
									}
									$finalstr = $finalstr . preg_replace('/(^[\*])/', "<em>", substr($regexarray2[$k][$l], 0, strlen($regexarray2[$k][$l]) - 1)) . "</em>";
								}
							}
							$FormattedOutput[$i] = preg_replace($this->FindMDItalicTextItem1, $finalstr, $FormattedOutput[$i]);
						} else {
							$FormattedOutput[$i] = preg_replace('/(^[\*])/', "<em>", substr($FormattedOutput[$i], 1, strlen($FormattedOutput[$i] - 2))) . "</em>";
						}
					}
				}
				if (preg_match($this->FindMDItalicTextItem2, $FormattedOutput[$i], $regexarray1) == 1) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						if (preg_match_all($this->FindMDItalicTextItem2, $regexarray1[$j], $regexarray2) > 0) {
							$finalstr = "";
							for ($k = 0; $k < count($regexarray2); $k++) {
								for ($l = 0; $l < count($regexarray2[$k]); $l++) {
									$italicremoved = explode($regexarray2[$k][$l], $regexarray1[$j]);
									if ($k != 0) {
										$finalstr = $finalstr . $italicremoved[0];
									}
									$finalstr = $finalstr . preg_replace('/(^[\_])/', "<em>", substr($regexarray2[$k][$l], 0, strlen($regexarray2[$k][$l]) - 1)) . "</em>";
								}
							}
							$FormattedOutput[$i] = preg_replace($this->FindMDItalicTextItem2, $finalstr, $FormattedOutput[$i]);
						} else {
							$FormattedOutput[$i] = preg_replace('/(^[\_])/', "<em>", substr($FormattedOutput[$i], 1, strlen($FormattedOutput[$i] - 2))) . "</em>";
						}
					}
				}
				# if Markdown Unordered List...
				if (preg_match($this->FindMDUnorderedListItem, $FormattedOutput[$i], $regexarray1) == 1) {
					if ($wasunorderedlist) {
						$FormattedOutput[$i] = preg_replace($this->FindMDUnorderedListItem, "<li>", $FormattedOutput[$i]) . "</li>";
					} else {
						$wasunorderedlist = True;
						$this->InsertIntoArray($FormattedOutput, $i, "<ul>");
						continue;
					}
				} else {
					if ($wasunorderedlist) {
						$wasunorderedlist = False;
						$this->InsertIntoArray($FormattedOutput, $i, "</ul><br>");
						continue;
					}
				}
				# if Markdown Ordered List...
				if (preg_match($this->FindMDFirstOrderedListItem, $FormattedOutput[$i], $regexarray1) == 1) {
					if ($wasorderedlist) {
						$FormattedOutput[$i] = preg_replace($this->FindMDFirstOrderedListItem, "<li>", $FormattedOutput[$i]) . "</li>";
					} else {
						$wasorderedlist = True;
						$this->InsertIntoArray($FormattedOutput, $i, "<ol>");
						continue;
					}
				} elseif ((preg_match($this->FindMDAnyOrderedListItem, $FormattedOutput[$i], $regexarray1) == 1) && $wasorderedlist) {
					if ($wasorderedlist) {
						$FormattedOutput[$i] = preg_replace($this->FindMDAnyOrderedListItem, "<li>", $FormattedOutput[$i]) . "</li>";
					}
				} elseif ($wasorderedlist) {
					$wasorderedlist = False;
					$this->InsertIntoArray($FormattedOutput, $i, "</ol><br>");
					continue;
				}
				# if Markdown Header level 1...
				if (preg_match($this->FindMDHeader1, $FormattedOutput[$i]) == 1) {
					# Need to remove the carriage returns and line feeds...
					$FormattedOutput[$i] = preg_replace($this->FindMDHeader1, '<h1>', $FormattedOutput[$i]);
					$FormattedOutput[$i] = $FormattedOutput[$i] . '</h1><br>';
				}
				# if Markdown Header level 2...
				if (preg_match($this->FindMDHeader2, $FormattedOutput[$i]) == 1) {
					# Need to remove the carriage returns and line feeds...
					$FormattedOutput[$i] = preg_replace($this->FindMDHeader2, '<h2>', $FormattedOutput[$i]);
					$FormattedOutput[$i] = $FormattedOutput[$i] . '</h2><br>';
				}
				# if Markdown Header level 3...
				if (preg_match($this->FindMDHeader3, $FormattedOutput[$i]) == 1) {
					# Need to remove the carriage returns and line feeds...
					$FormattedOutput[$i] = preg_replace($this->FindMDHeader3, '<h3>', $FormattedOutput[$i]);
					$FormattedOutput[$i] = $FormattedOutput[$i] . '</h3><br>';
				}
				# if Markdown Header level 4...
				if (preg_match($this->FindMDHeader4, $FormattedOutput[$i]) == 1) {
					# Need to remove the carriage returns and line feeds...
					$FormattedOutput[$i] = preg_replace($this->FindMDHeader4, '<h4>', $FormattedOutput[$i]);
					$FormattedOutput[$i] = $FormattedOutput[$i] . '</h4><br>';
				}
				# if Markdown Header level 5...
				if (preg_match($this->FindMDHeader5, $FormattedOutput[$i]) == 1) {
					# Need to remove the carriage returns and line feeds...
					$FormattedOutput[$i] = preg_replace($this->FindMDHeader5, '<h5>', $FormattedOutput[$i]);
					$FormattedOutput[$i] = $FormattedOutput[$i] . '</h5><br>';
				}
				# if Markdown Header level 6...
				if (preg_match($this->FindMDHeader6, $FormattedOutput[$i]) == 1) {
					# Need to remove the carriage returns and line feeds...
					$FormattedOutput[$i] = preg_replace($this->FindMDHeader6, '<h6>', $FormattedOutput[$i]);
					$FormattedOutput[$i] = $FormattedOutput[$i] . '</h6><br>';
				}
				# if Markdown Link...
				if (preg_match_all($this->FindMDLink1, $FormattedOutput[$i], $regexarray1) > 0) {
					for ($j = 0; $j < count($regexarray1[0]); $j++) {
						if (preg_match_all($this->FindMDLink2, $regexarray1[0][$j], $regexarray2) > 0) {
							$finalstr = "";
							for ($k = 0; $k < count($regexarray2[0]); $k++) {
								$explodedlink = explode(substr($regexarray2[0][$k], 1, (strlen($regexarray2[0][$k]) - 2)), $regexarray1[0][$j]);
								for ($l = 0; $l < count($explodedlink); $l++) {
									$str = substr($explodedlink[$l], 1, (strlen($explodedlink[$l]) - 2));
									$linkcontent = explode("](", $str);
									if ($l != 0) {
										$finalstr = $finalstr . substr($regexarray2[0][$k], 1, (strlen($regexarray2[0][$k]) - 2));
									}
									if (preg_match('/^(http)/', $linkcontent[1]) == 1) {
										$finalstr = $finalstr . "<a href=\"" . $linkcontent[1] . "\" target=\"_top\">" . $linkcontent[0] . "</a>";
									} else {
										$fileinfo = pathinfo($linkcontent[1]);
										if ($fileinfo["extension"] == "md") {
											$finalstr = $finalstr . "<a href=\"" . $this->Configuration["doc_handler"] . "?" . $this->Configuration["method_var"] . "=" . $this->Configuration["doc_root"] . "/" . $fileinfo["basename"] . "\" target=\"_top\">" . $linkcontent[0] . "</a>";
										} else {
											$finalstr = $finalstr . "<a href=\"" . $linkcontent[1] . "\">" . $linkcontent[0] . "</a>";
										}
									}
								}
							}
							$FormattedOutput[$i] = preg_replace($this->FindMDLink1, $finalstr, $FormattedOutput[$i]);
						} else {
							$str = substr($regexarray1[0][$j], 1, (strlen($regexarray1[0][$j]) - 2));
							$linkcontent = explode("](", $str);
							if (preg_match('/^(http)/', $linkcontent[1]) == 1) {
								$FormattedOutput[$i] = preg_replace($this->FindMDLink1, "<a href=\"" . $linkcontent[1] . "\" target=\"_top\">" . $linkcontent[0] . "</a>", $FormattedOutput[$i]);
							} else {
								$fileinfo = pathinfo($linkcontent[1]);
								if ($fileinfo["extension"] == "md") {
									$FormattedOutput[$i] = preg_replace($this->FindMDLink1, "<a href=\"" . $this->Configuration["doc_handler"] . "?" . $this->Configuration["method_var"] . "=" . $this->Configuration["doc_root"] . "/" . $fileinfo["basename"] . "\" target=\"_top\">" . $linkcontent[0] . "</a>", $FormattedOutput[$i]);
								} else {
									$FormattedOutput[$i] = preg_replace($this->FindMDLink1, "<a href=\"" . $linkcontent[1] . "\">" . $linkcontent[0] . "</a>", $FormattedOutput[$i]);
								}
							}
						}
					}
				}
				# if Markdown New Line...
				$FormattedOutput[$i] = preg_replace($this->FindMDNewline, '<br>', $FormattedOutput[$i]);
			}
			$this->InsertIntoArray($FormattedOutput, count($FormattedOutput), "<body>");
			$this->InsertIntoArray($FormattedOutput,0, "</head>");
			if (isset($this->Configuration["head_data"])) {
				$counter = 0;
				for ($i = count($this->Configuration["head_data"]) - 1; $i >= 0; $i--) {
					$this->InsertIntoArray($FormattedOutput, 0, $this->Configuration["head_data"][$i]);
				}
			}
			$this->InsertIntoArray($FormattedOutput,0, "<head>");
			for ($i = 0; $i < count($FormattedOutput); $i++) {
				if (preg_match_all('/(["])/', $FormattedOutput[$i], $regexarray1) > 0) {
					for ($j = 0; $j < count($regexarray1); $j++) {
						$FormattedOutput[$i] = preg_replace('/(["])/', '&quot;', $FormattedOutput[$i]);
					}
				}
			}
			return $FormattedOutput;
		}
	}
?>
