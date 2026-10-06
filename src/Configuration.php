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

	class Configuration {
		private $DefaultConfiguration = [
			'method'	=>	"GET"
		];
		public $Configuration;

		function __construct($configarray) {
			$this->Configuration = $this->DefaultConfiguration;
			$mdinfo = pathinfo($configarray["doc_file"]);
			$this->Configuration["doc_root"] = preg_replace('/( [\.][\/] )/', "", $mdinfo["dirname"]);
			$this->Configuration["doc_file"] = preg_replace('/( [\.][\/] )/', "", $mdinfo["basename"]);
			foreach ($configarray as $index => $configitem) {
				if ($index != "doc_file" && $index != "doc_root") {
					$this->Configuration[$index] = $configitem;
				}
			}
			if (array_key_exists("method_var", $this->Configuration) != True) {
				throw new Exception("class MD_Reader\Configuration doesn't contain a method_var key.");
			}
			if (array_key_exists("doc_handler", $this->Configuration) != True) {
				throw new Exception("class MD_Reader\Configuration doesn't contain a doc_handler key.");
			}
			if (array_key_exists("doc_file", $this->Configuration) != True) {
				throw new Exception("class MD_Reader\Configuration doesn't contain a doc_file key.");
			}
		}
	}
?>
