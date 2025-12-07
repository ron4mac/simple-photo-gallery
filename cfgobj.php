<?php

// default gallery configuration object
$cfgobj = (object) [
	'imgs' => (object) ['r'=>1,'w'=>1200,'h'=>1200,'q'=>90],
	'thms' => (object) ['w'=>120,'h'=>120,'q'=>90],
	'flds' => (object) ['w'=>128,'h'=>128],
	'ssdly' => 6,
	'css' => 'dark',
	'title' => 'My Gallery',
	'desc' => 'A gallery instance',
	'passw' => "\t\t"
];


class Utils {

	public function mergeObjectsRecursively ($obj1, $obj2)
	{
		return $this->_mergeRecursively($obj1, $obj2);
	}

	private function _mergeRecursively ($obj1, $obj2)
	{
		if (is_object($obj2)) {
			$keys = array_keys(get_object_vars($obj2));
			foreach ($keys as $key) {
				if (isset($obj1->{$key}) && is_object($obj1->{$key}) && is_object($obj2->{$key})) {
					$obj1->{$key} = $this->_mergeRecursively($obj1->{$key}, $obj2->{$key});
				} elseif (isset($obj1->{$key}) && is_array($obj1->{$key}) && is_array($obj2->{$key})) {
					$obj1->{$key} = $this->_mergeRecursively($obj1->{$key}, $obj2->{$key});
				} else {
					$obj1->{$key} = $obj2->{$key};
				}
			}
		} elseif (is_array($obj2)) {
			if (is_array($obj1) && is_array($obj2)) {
				$obj1 = array_merge_recursive($obj1, $obj2);
			} else {
				$obj1 = $obj2;
			}
		}

		return $obj1;
	}
}

$utils = new Utils();
