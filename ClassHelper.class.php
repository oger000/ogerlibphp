<?PHP
/*
#LICENSE BEGIN
**********************************************************************
* OgerArch - Archaeological Database is released under the GNU General Public License (GPL) <http://www.gnu.org/licenses>
* Copyright (C) Gerhard Öttl <gerhard.oettl@ogersoft.at>
**********************************************************************
#LICENSE END
*/



/**
* Helper class for class and object handling
*/
class ClassHelper {


	/**
	* get only public properties from an object
	*/
	public static function getObjectVars($obj) {

		return get_object_vars($obj);
	}


	/**
	* assign public variables for this object from elsewhere
	* Only existing variables of the TO object are updated
	* @preserve:
	*  - true: variables not existing in FROM are preserved
	*  - false: (default) variables not existing in FROM are set to null
	*/
	public static function assignTo(&$to, $from, $preserve = false, $guess = false) {

		// if values are from an object then convert to array
		if (is_object($from))
			$from = get_object_vars($from);

		// force array
		if (!is_array($from))
			$from = array();

		// assign each public object variable from array
		foreach(get_object_vars($to) as $key => $dummy) {

			// reset temp vars
			unset($searchKeys);
			unset($value);

			// create possible keys
			$searchKeys[] = $key;
			// if guess is allowed than also search for lowercase and uppercase key
			if ($guess) {
				$searchKeys[] = strtolower($key);
				$searchKeys[] = strtoupper($key);
			}

			// look if key exists
			foreach ($searchKeys as $searchKey) {
				if (array_key_exists($searchKey, $from)) {
					$value = $from[$searchKey];
					$found = true;
					break;
				}
			}


			// if preserve is set than assign only if value is found
			if ($preserve && !$found)
				continue;

			// now assign
			$to->$key = $value;

		}  // end of loop over public properties

		return $to;
	}


	/**
	* assign public variables from elsewhere to this
	* All variables of the FROM object are transfered
	*/
	public static function assignFrom($from, &$to) {

		// if values are from an object then convert to array
		if (is_object($from))
			$from = get_object_vars($from);

		// force array
		if (!is_array($from))
			$from = array();

		// assign each public object variable from array
		foreach($from as $key => $value) {
			$to->$key = $value;
		}  // end of loop over public properties

		return $to;
	}

}

?>
