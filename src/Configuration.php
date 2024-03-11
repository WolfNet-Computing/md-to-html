<?php
	namespace MD_Reader;

	class Configuration {
		private $DefaultConfiguration = [
			'method'	=>	"POST",
		];
		public $Configuration;

		function __construct($configarray) {
			$this->Configuration = $this->DefaultConfiguration;
			foreach ($configarray as $index => $configitem) {
				foreach ($this->DefaultConfiguration as $DefaultConfigurationIndex => $DefaultConfigurationItem) {
					$this->Configuration[$index] = $configitem;
				}
			}
			if ($this->Configuration["method"] != "GET" && $this->Configuration["method"] != "POST") {
				throw new Exception("class MD_Reader\Configuration contains an invalid method.");
			}
			if (array_key_exists("method_var") != True) {
				throw new Exception("class MD_Reader\Configuration doesn't contain a method_var key.");
			}
			if (array_key_exists("doc_handler") != True) {
				throw new Exception("class MD_Reader\Configuration doesn't contain a doc_handler key.");
			}
			if (array_key_exists("doc_file") != True) {
				throw new Exception("class MD_Reader\Configuration doesn't contain a doc_file key.");
			}
		}
	}
?>