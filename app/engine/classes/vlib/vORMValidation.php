<?php
/**
 * Handles validation for vActiveRecord classes
 * 
 * @copyright  Copyright (c) 2014-2019 Alan Johnston, Velus Universe Ltd
 * @author     Alan Johnston [aj] <alan.johnston@velusuniverse.co.uk>
 * @author     Alan Johnston, Velus Universe Ltd [aj-vu] <alan.johnston@velusuniverse.co.uk>
 * @license    http://veluslib.opensource.velusuniverse.com/license
 * 
 * @package    Velus Lib
 * 
 * @version    0.0.1b
 * @changes    0.0.1b    The initial implementation [aj, 2014-12-13]
 * 
 * @link       http://veluslib.opensource.velusuniverse.com/vORMValidation
 */
class vORMValidation
{
	// The following constants allow for nice looking callbacks to static methods
	const addConditionalRule       = 'vORMValidation::addConditionalRule';
	const addManyToManyRule        = 'vORMValidation::addManyToManyRule';
	const addOneOrMoreRule         = 'vORMValidation::addOneOrMoreRule';
	const addOneToManyRule         = 'vORMValidation::addOneToManyRule';
	const addOnlyOneRule           = 'vORMValidation::addOnlyOneRule';
	const addRegexReplacement      = 'vORMValidation::addRegexReplacement';
	const addRegexRule             = 'vORMValidation::addRegexRule';
	const addRequiredRule          = 'vORMValidation::addRequiredRule';
	const addStringReplacement     = 'vORMValidation::addStringReplacement';
	const addValidValuesRule       = 'vORMValidation::addValidValuesRule';
	const hasValue                 = 'vORMValidation::hasValue';
	const inspect                  = 'vORMValidation::inspect';
	const removeStringReplacement  = 'vORMValidation::removeStringReplacement';
	const removeRegexReplacement   = 'vORMValidation::removeRegexReplacement';
	const reorderMessages          = 'vORMValidation::reorderMessages';
	const replaceMessages          = 'vORMValidation::replaceMessages';
	const reset                    = 'vORMValidation::reset';
	const setColumnCaseInsensitive = 'vORMValidation::setColumnCaseInsensitive';
	const setMessageOrder          = 'vORMValidation::setMessageOrder';
	const validate                 = 'vORMValidation::validate';
	const validateRelated          = 'vORMValidation::validateRelated';	
	
	
	/**
	 * Columns that should be treated as case insensitive when checking uniqueness
	 * 
	 * @var array
	 */
	static private $case_insensitive_columns = array();
	
	/**
	 * Conditional rules
	 * 
	 * @var array
	 */
	static private $conditional_rules = array();
	
	/**
	 * Ordering rules for messages
	 * 
	 * @var array
	 */
	static private $message_orders = array();
	
	/**
	 * One or more rules
	 * 
	 * @var array
	 */
	static private $one_or_more_rules = array();
	
	/**
	 * Only one rules
	 * 
	 * @var array
	 */
	static private $only_one_rules = array();
	
	/**
	 * Regular expression replacements performed on each message
	 * 
	 * @var array
	 */
	static private $regex_replacements = array();
	
	/**
	 * Rules that require at least one or more *-to-many related records to be associated
	 * 
	 * @var array
	 */
	static private $related_one_or_more_rules = array();
	
	/**
	 * Rules that require a value to match a regular expression
	 * 
	 * @var array
	 */
	static private $regex_rules = array();
	
	/**
	 * Rules that require a value be present in a column even if the database schema doesn't require it
	 * 
	 * @var array
	 */
	static private $required_rules = array();
	
	/**
	 * String replacements performed on each message
	 * 
	 * @var array
	 */
	static private $string_replacements = array();
	
	/**
	 * Valid values rules
	 * 
	 * @var array
	 */
	static private $valid_values_rules = array();
	
	
	/**
	 * Adds a conditional rule
	 * 
	 * If a non-empty value is found in one of the `$main_columns`, or if
	 * specified, a value from the `$conditional_values` array, all of the
	 * `$conditional_columns` will also be required to have a value.
	 *
	 * @param  mixed         $class                The class name or instance of the class this rule applies to
	 * @param  string|array  $main_columns         The column(s) to check for a value
	 * @param  mixed         $conditional_values   If `NULL`, any value in the main column will trigger the conditional column(s), otherwise the value must match this scalar value or be present in the array of values
	 * @param  string|array  $conditional_columns  The column(s) that are to be required
	 * @return void
	 */
	static public function addConditionalRule($class, $main_columns, $conditional_values, $conditional_columns)
	{
		$class = vORM::getClass($class);
		
		if (!isset(self::$conditional_rules[$class])) {
			self::$conditional_rules[$class] = array();
		}
		
		settype($main_columns, 'array');
		settype($conditional_columns, 'array');
		if ($conditional_values !== NULL) {
			settype($conditional_values, 'array');
		}	
		
		$rule = array();
		$rule['main_columns']        = $main_columns;
		$rule['conditional_values']  = $conditional_values;
		$rule['conditional_columns'] = $conditional_columns;
		
		self::$conditional_rules[$class][] = $rule;
	}
	
	
	/**
	 * Add a many-to-many rule that requires at least one related record is associated with the current record
	 *
	 * @param  mixed  $class          The class name or instance of the class to add the rule for
	 * @param  string $related_class  The name of the related class
	 * @param  string $route          The route to the related class
	 * @return void
	 */
	static public function addManyToManyRule($class, $related_class, $route=NULL)
	{
		$class         = vORM::getClass($class);
		$related_class = vORM::getRelatedClass($class, $related_class);
		
		if (!isset(self::$related_one_or_more_rules[$class])) {
			self::$related_one_or_more_rules[$class] = array();
		}
		
		if (!isset(self::$related_one_or_more_rules[$class][$related_class])) {
			self::$related_one_or_more_rules[$class][$related_class] = array();
		}
		
		$route = vORMSchema::getRouteName(
			vORMSchema::retrieve($class),
			vORM::tablize($class),
			vORM::tablize($related_class),
			$route,
			'many-to-many'
		);
		
		self::$related_one_or_more_rules[$class][$related_class][$route] = TRUE;
	}
	
	
	/**
	 * Adds a one-or-more rule that requires at least one of the columns specified has a value
	 *
	 * @param  mixed $class    The class name or instance of the class the columns exists in
	 * @param  array $columns  The columns to check
	 * @return void
	 */
	static public function addOneOrMoreRule($class, $columns)
	{
		$class = vORM::getClass($class);
		
		settype($columns, 'array');
		
		if (!isset(self::$one_or_more_rules[$class])) {
			self::$one_or_more_rules[$class] = array();
		}
		
		$rule = array();
		$rule['columns'] = $columns;
		
		self::$one_or_more_rules[$class][] = $rule;
	}
	
	
	/**
	 * Add a one-to-many rule that requires at least one related record is associated with the current record
	 *
	 * @param  mixed  $class          The class name or instance of the class to add the rule for
	 * @param  string $related_class  The name of the related class
	 * @param  string $route          The route to the related class
	 * @return void
	 */
	static public function addOneToManyRule($class, $related_class, $route=NULL)
	{
		$class         = vORM::getClass($class);
		$related_class = vORM::getRelatedClass($class, $related_class);
		
		if (!isset(self::$related_one_or_more_rules[$class])) {
			self::$related_one_or_more_rules[$class] = array();
		}
		
		if (!isset(self::$related_one_or_more_rules[$class][$related_class])) {
			self::$related_one_or_more_rules[$class][$related_class] = array();
		}
		
		$route = vORMSchema::getRouteName(
			vORMSchema::retrieve($class),
			vORM::tablize($class),
			vORM::tablize($related_class),
			$route,
			'one-to-many'
		);
		
		self::$related_one_or_more_rules[$class][$related_class][$route] = TRUE;
	}
	
	
	/**
	 * Add an only-one rule that requires exactly one of the columns must have a value
	 *
	 * @param  mixed $class    The class name or instance of the class the columns exists in
	 * @param  array $columns  The columns to check
	 * @return void
	 */
	static public function addOnlyOneRule($class, $columns)
	{
		$class = vORM::getClass($class);
		
		settype($columns, 'array');
		
		if (!isset(self::$only_one_rules[$class])) {
			self::$only_one_rules[$class] = array();
		}
		
		$rule = array();
		$rule['columns'] = $columns;
		
		self::$only_one_rules[$class][] = $rule;
	}
	
	
	/**
	 * Adds a call to [http://php.net/preg_replace `preg_replace()`] for each message
	 * 
	 * Regex replacement is done after the `post::validate()` hook, and right
	 * before the messages are reordered.
	 * 
	 * If a message is an empty string after replacement, it will be
	 * removed from the list of messages.
	 * 
	 * @param  mixed  $class    The class name or instance of the class the columns exists in
	 * @param  string $search   The PCRE regex to search for - see http://php.net/pcre for details
	 * @param  string $replace  The string to replace with - all $ and \ are used in back references and must be escaped with a \ when meant literally
	 * @return void
	 */
	static public function addRegexReplacement($class, $search, $replace)
	{
		$class = vORM::getClass($class);
		
		if (!isset(self::$regex_replacements[$class])) {
			self::$regex_replacements[$class] = array(
				'search'  => array(),
				'replace' => array()
			);
		}
		
		self::$regex_replacements[$class]['search'][]  = $search;
		self::$regex_replacements[$class]['replace'][] = $replace;
	}
	
	
	/**
	 * Adds a rule to validate a column against a PCRE regular expression - the rule is not run if the value is `NULL`
	 * 
	 * @param  mixed  $class    The class name or instance of the class the columns exists in
	 * @param  string $column   The column to match with the regex
	 * @param  string $regex    The PCRE regex to match against - see http://php.net/pcre for details
	 * @param  string $message  The message to use if the value does not match the regular expression
	 * @return void
	 */
	static public function addRegexRule($class, $column, $regex, $message)
	{
		$class = vORM::getClass($class);
		
		if (!isset(self::$regex_rules[$class])) {
			self::$regex_rules[$class] = array();
		}
		
		self::$regex_rules[$class][$column] = array(
			'regex'   => $regex,
			'message' => $message
		);
	}
	
	
	/**
	 * Requires that a column have a non-`NULL` value
	 * 
	 * Before using this method, try setting the database column to `NOT NULL`
	 * and remove any default value. Such a configuration will trigger the same
	 * functionality as this method, and will enforce the rule on the database
	 * level for any other code that queries it.
	 *
	 * @param  mixed $class    The class name or instance of the class the column(s) exists in
	 * @param  array $columns  The column or columns to check - each column will require a value
	 * @return void
	 */
	static public function addRequiredRule($class, $columns)
	{
		$class = vORM::getClass($class);
		
		settype($columns, 'array');
		
		if (!isset(self::$required_rules[$class])) {
			self::$required_rules[$class] = array();
		}
		
		foreach ($columns as $column) {
			self::$required_rules[$class][$column] = TRUE;
		}
	}
	
	
	/**
	 * Adds a call to [http://php.net/str_replace `str_replace()`] for each message
	 * 
	 * String replacement is done after the `post::validate()` hook, and right
	 * before the messages are reordered.
	 * 
	 * If a message is an empty string after replacement, it will be
	 * removed from the list of messages.
	 * 
	 * @param  mixed  $class    The class name or instance of the class the columns exists in
	 * @param  string $search   The string to search for
	 * @param  string $replace  The string to replace with
	 * @return void
	 */
	static public function addStringReplacement($class, $search, $replace)
	{
		$class = vORM::getClass($class);
		
		if (!isset(self::$string_replacements[$class])) {
			self::$string_replacements[$class] = array(
				'search'  => array(),
				'replace' => array()
			);
		}
		
		self::$string_replacements[$class]['search'][]  = $search;
		self::$string_replacements[$class]['replace'][] = $replace;
	}
	
	
	/**
	 * Restricts a column to having only a value from the list of valid values
	 * 
	 * Please note that `NULL` values are always allowed, even if not listed in
	 * the `$valid_values` array, if the column is not set as `NOT NULL`.
	 * 
	 * This functionality can also be accomplished by added a `CHECK` constraint
	 * on the column in the database, or using a MySQL `ENUM` data type.
	 *
	 * @param  mixed   $class         The class name or instance of the class this rule applies to
	 * @param  string  $column        The column to validate
	 * @param  array   $valid_values  The valid values to check - `NULL` values are always allows if the column is not set to `NOT NULL`
	 * @return void
	 */
	static public function addValidValuesRule($class, $column, $valid_values)
	{
		$class = vORM::getClass($class);
		
		if (!isset(self::$valid_values_rules[$class])) {
			self::$valid_values_rules[$class] = array();
		}
		
		settype($valid_values, 'array');
		
		self::$valid_values_rules[$class][$column] = $valid_values;
		
		vORM::registerInspectCallback($class, $column, self::inspect);
	}
	
	
	/**
	 * Validates a value against the database schema
	 *
	 * @param  vSchema        $schema       The schema object for the object
	 * @param  vActiveRecord  $object       The instance of the class the column is part of
	 * @param  string         $column       The column to check
	 * @param  array          &$values      An associative array of all values going into the row (needs all for multi-field unique constraint checking)
	 * @param  array          &$old_values  The old values from the record
	 * @return string  An error message for the column specified
	 */
	static private function checkAgainstSchema($schema, $object, $column, &$values, &$old_values)
	{
		$class = get_class($object);
		$table = vORM::tablize($class);
		
		$info = $schema->getColumnInfo($table, $column);
		// Make sure a value is provided for required columns
		$schema_not_null = $info['not_null'] && $info['default'] === NULL && $info['auto_increment'] === FALSE;
		$rule_not_null   = isset(self::$required_rules[$class][$column]);
		if ($values[$column] === NULL && ($schema_not_null || $rule_not_null)) {
			return self::compose(
				'%sPlease enter a value',
				vValidationException::formatField(vORM::getColumnName($class, $column))
			);
		}
		
		$message = self::checkDataType($schema, $class, $column, $values[$column]);
		if ($message) { return $message; }
		
		// Make sure a valid value is chosen
		if (isset($info['valid_values']) && $values[$column] !== NULL && !in_array($values[$column], $info['valid_values'])) {
			return self::compose(
				'%1$sPlease choose from one of the following: %2$s',
				vValidationException::formatField(vORM::getColumnName($class, $column)),
				join(', ', $info['valid_values'])
			);
		}
		
		// Make sure the value isn't too long
		if ($info['type'] == 'varchar' && isset($info['max_length']) && $values[$column] !== NULL && is_string($values[$column]) && vUTF8::len($values[$column]) > $info['max_length']) {
			return self::compose(
				'%1$sPlease enter a value no longer than %2$s characters',
				vValidationException::formatField(vORM::getColumnName($class, $column)),
				$info['max_length']
			);
		}
		
		// Make sure the value is the proper length
		if ($info['type'] == 'char' && isset($info['max_length']) && $values[$column] !== NULL && is_string($values[$column]) && vUTF8::len($values[$column]) != $info['max_length']) {
			return self::compose(
				'%1$sPlease enter exactly %2$s characters',
				vValidationException::formatField(vORM::getColumnName($class, $column)),
				$info['max_length']
			);
		}
		
		// Make sure the value fits in the numeric range
		if (self::stringlike($values[$column]) && in_array($info['type'], array('integer', 'float')) && $info['min_value'] && $info['max_value'] && ($info['min_value']->gt($values[$column]) || $info['max_value']->lt($values[$column]))) {
			return self::compose(
				'%1$sPlease enter a number between %2$s and %3$s',
				vValidationException::formatField(vORM::getColumnName($class, $column)),
				$info['min_value']->__toString(),
				$info['max_value']->__toString()
			);
		}
		
		$message = self::checkForeignKeyConstraints($schema, $class, $column, $values);
		if ($message) { return $message; }
	}
	
	
	/**
	 * Validates against a conditional rule
	 *
	 * @param  vSchema $schema               The schema object for the class specified
	 * @param  string  $class                The class this rule applies to
	 * @param  array   &$values              An associative array of all values for the record
	 * @param  array   $main_columns         The columns to check for a value
	 * @param  array   $conditional_values   If `NULL`, any value in the main column will trigger the conditional columns, otherwise the value must match one of these
	 * @param  array   $conditional_columns  The columns that are to be required
	 * @return array  The error messages for the rule specified
	 */
	static private function checkConditionalRule($schema, $class, &$values, $main_columns, $conditional_values, $conditional_columns)
	{
		$check_for_missing_values = FALSE;
		
		foreach ($main_columns as $main_column) {
			$matches_conditional_value = $conditional_values !== NULL && in_array($values[$main_column], $conditional_values);
			$has_some_value            = $conditional_values === NULL && strlen((string) $values[$main_column]);
			if ($matches_conditional_value || $has_some_value) {
				$check_for_missing_values = TRUE;
				break;	
			}	
		}
		
		if (!$check_for_missing_values) {
			return;	
		}
		
		$table = vORM::tablize($class);
		$messages = array();
		foreach ($conditional_columns as $conditional_column) {
			$default_is_space = $schema->getColumnInfo($table, $conditional_column, 'default') === '';
			if ($values[$conditional_column] !== NULL && (!$default_is_space || ($default_is_space && $values[$conditional_column] !== ''))) { continue; }
			$messages[$conditional_column] = self::compose(
				'%sPlease enter a value',
				vValidationException::formatField(vORM::getColumnName($class, $conditional_column))
			);
		}
		if ($messages) {
			return $messages;
		}
	}
	
	
	/**
	 * Validates a value against the database data type
	 *
	 * @param  vSchema $schema  The schema object for the class
	 * @param  string  $class   The class the column is part of
	 * @param  string  $column  The column to check
	 * @param  mixed   $value   The value to check
	 * @return string  An error message for the column specified
	 */
	static private function checkDataType($schema, $class, $column, $value)
	{
		$table       = vORM::tablize($class);
		$column_info = $schema->getColumnInfo($table, $column);
		
		if ($value !== NULL) {
			switch ($column_info['type']) {
				case 'varchar':
				case 'char':
				case 'text':
				case 'blob':
					if (!is_string($value) && !is_numeric($value)) {
						return self::compose(
							'%sPlease enter a string',
							vValidationException::formatField(vORM::getColumnName($class, $column))
						);
					}
					break;
				case 'integer':
					if (!is_numeric($value)) {
						return self::compose(
							'%sPlease enter a whole number',
							vValidationException::formatField(vORM::getColumnName($class, $column))
						);
					}
					break;
				case 'float':
					if (!is_numeric($value)) {
						return self::compose(
							'%sPlease enter a number',
							vValidationException::formatField(vORM::getColumnName($class, $column))
						);
					}
					break;
				case 'timestamp':
					try {
						new vTimestamp($value);	
					} catch (vValidationException $e) {
						return self::compose(
							'%sPlease enter a date/time',
							vValidationException::formatField(vORM::getColumnName($class, $column))
						);
					}
					break;
				case 'date':
					try {
						new vDate($value);	
					} catch (vValidationException $e) {
						return self::compose(
							'%sPlease enter a date',
							vValidationException::formatField(vORM::getColumnName($class, $column))
						);
					}
					break;
				case 'time':
					try {
						new vTime($value);	
					} catch (vValidationException $e) {
						return self::compose(
							'%sPlease enter a time',
							vValidationException::formatField(vORM::getColumnName($class, $column))
						);
					}
					break;
				
			}
		}
	}
	
	
	/**
	 * Validates values against foreign key constraints
	 *
	 * @param  vSchema $schema   The schema object for the class
	 * @param  string  $class    The class to check the foreign keys for
	 * @param  string  $column   The column to check
	 * @param  array   &$values  The values to check
	 * @return string  An error message for the column specified
	 */
	static private function checkForeignKeyConstraints($schema, $class, $column, &$values)
	{
		if ($values[$column] === NULL) {
			return;
		}
		
		$db           = vORMDatabase::retrieve($class, 'read');
		$table        = vORM::tablize($class);
		$foreign_keys = $schema->getKeys($table, 'foreign');
		
		foreach ($foreign_keys AS $foreign_key) {
			if ($foreign_key['column'] == $column) {
				try {
					
					$params = array(
						"SELECT %r FROM %r WHERE " . vORMDatabase::makeCondition($schema, $table, $column, '=', $values[$column]),
						$foreign_key['foreign_column'],
						$foreign_key['foreign_table'],
						$foreign_key['foreign_column'],
						$values[$column]
					);
					$result = call_user_func_array($db->translatedQuery, $params);
					$result->tossIfNoRows();
					
				} catch (vNoRowsException $e) {
					return self::compose(
						'%sThe value specified is invalid',
						vValidationException::formatField(vORM::getColumnName($class, $column))
					);
				}
			}
		}
	}
	
	
	/**
	 * Validates against a one-or-more rule
	 *
	 * @param  vSchema $schema   The schema object for the table
	 * @param  string  $class    The class the columns are part of
	 * @param  array   &$values  An associative array of all values for the record
	 * @param  array   $columns  The columns to check
	 * @return string  An error message for the rule
	 */
	static private function checkOneOrMoreRule($schema, $class, &$values, $columns)
	{
		settype($columns, 'array');
		
		$found_value = FALSE;
		foreach ($columns as $column) {
			if (self::hasValue($schema, $class, $values, $column)) {
				$found_value = TRUE;
			}
		}
		
		if (!$found_value) {
			$column_names = array();
			foreach ($columns as $column) {
				$column_names[] = vORM::getColumnName($class, $column);
			}
			return self::compose(
				'%sPlease enter a value for at least one',
				vValidationException::formatField(join(', ', $column_names))
			);
		}
	}
	
	
	/**
	 * Validates against an only-one rule
	 *
	 * @param  vSchema $schema   The schema object for the table
	 * @param  string  $class    The class the columns are part of
	 * @param  array   &$values  An associative array of all values for the record
	 * @param  array   $columns  The columns to check
	 * @return string  An error message for the rule
	 */
	static private function checkOnlyOneRule($schema, $class, &$values, $columns)
	{
		settype($columns, 'array');
		
		$column_names = array();
		foreach ($columns as $column) {
			$column_names[] = vORM::getColumnName($class, $column);
		}
		
		$found_value = FALSE;
		foreach ($columns as $column) {
			if (self::hasValue($schema, $class, $values, $column)) {
				if ($found_value) {
					return self::compose(
						'%sPlease enter a value for only one',
						vValidationException::formatField(join(', ', $column_names))
					);
				}
				$found_value = TRUE;
			}
		}
		
		if (!$found_value) {
			return self::compose(
				'%sPlease enter a value for one',
				vValidationException::formatField(join(', ', $column_names))
			);	
		}	
	}
	
	
	/**
	 * Makes sure a record with the same primary keys is not already in the database
	 *
	 * @param  vSchema        $schema       The schema object for the object
	 * @param  vActiveRecord  $object       The instance of the class to check
	 * @param  array          &$values      An associative array of all values going into the row (needs all for multi-field unique constraint checking)
	 * @param  array          &$old_values  The old values for the record
	 * @return array  A single element associative array with the key being the primary keys joined by ,s and the value being the error message
	 */
	static private function checkPrimaryKeys($schema, $object, &$values, &$old_values)
	{
		$class = get_class($object);
		$table = vORM::tablize($class);
		
		$db = vORMDatabase::retrieve($class, 'read');
		
		$pk_columns = $schema->getKeys($table, 'primary');
		$columns    = array();
		
		$found_value  = FALSE;
		foreach ($pk_columns as $pk_column) {
			$columns[] = vORM::getColumnName($class, $pk_column);
			if ($values[$pk_column]) {
				$found_value = TRUE;	
			}
		}
		
		if (!$found_value) {
			return;	
		}
		
		$different = FALSE;
		foreach ($pk_columns as $pk_column) {
			if (!vActiveRecord::hasOld($old_values, $pk_column)) {
				continue;	
			}
			$old_value = vActiveRecord::retrieveOld($old_values, $pk_column);
			$value     = $values[$pk_column];
			if (self::isCaseInsensitive($class, $pk_column) && self::stringlike($value) && self::stringlike($old_value)) {
				if (vUTF8::lower($value) != vUTF8::lower($old_value)) {
					$different = TRUE;
				}	
			} elseif ($old_value != $value) {
				$different = TRUE;	
			}
		}
		
		if (!$different) {
			return;	
		}
		
		try {
			$params = array(
				"SELECT %r FROM %r WHERE ",
				$pk_columns,
				$table
			);
			
			$column_info = $schema->getColumnInfo($table);
			
			$conditions = array();
			foreach ($pk_columns as $pk_column) {
				$value = $values[$pk_column];
				
				// This makes sure the query performs the way an insert will
				if ($value === NULL && $column_info[$pk_column]['not_null'] && $column_info[$pk_column]['default'] !== NULL) {
					$value = $column_info[$pk_column]['default'];
				}
				
				if (self::isCaseInsensitive($class, $pk_column) && self::stringlike($value)) {
					$condition    = vORMDatabase::makeCondition($schema, $table, $pk_column, '=', $value);
					$conditions[] = str_replace('%r', 'LOWER(%r)', $condition);
					$params[] = $pk_column;
					$params[] = vUTF8::lower($value);
					
				} else {
					$conditions[] = vORMDatabase::makeCondition($schema, $table, $pk_column, '=', $value);
					$params[] = $pk_column;
					$params[] = $value;
				} 
			}
			$params[0] .= join(' AND ', $conditions);
			
			$result = call_user_func_array($db->translatedQuery, $params);
			$result->tossIfNoRows();
			
			return array(join(',', $pk_columns) => self::compose(
				'Another %1$s with the same %2$s already exists',
				vORM::getRecordName($class),
				vGrammar::joinArray($columns, 'and')
			));
			
		} catch (vNoRowsException $e) { }
	}
	
	
	/**
	 * Validates against a regex rule
	 *
	 * @param  string  $class    The class the column is part of
	 * @param  array   &$values  An associative array of all values for the record
	 * @param  string  $column   The column to check
	 * @param  string  $regex    The PCRE regular expression
	 * @param  string  $message  The message to use if the value does not match the regular expression
	 * @return string  An error message for the rule
	 */
	static private function checkRegexRule($class, &$values, $column, $regex, $message)
	{
		if ($values[$column] === NULL) {
			return;
		}
		
		if (preg_match($regex, $values[$column])) {
			return;
		}
		
		return self::compose(
			'%s' . str_replace('%', '%%', $message),
			vValidationException::formatField(vORM::getColumnName($class, $column))
		);
	}
	
	
	/**
	 * Validates against a *-to-many one or more rule
	 *
	 * @param  vActiveRecord $object            The object being checked
	 * @param  array         &$values           The values for the object
	 * @param  array         &$related_records  The related records for the object
	 * @param  string        $related_class     The name of the related class
	 * @param  string        $route             The name of the route from the class to the related class
	 * @return string  An error message for the rule
	 */
	static private function checkRelatedOneOrMoreRule($object, &$values, &$related_records, $related_class, $route)
	{
		$related_table   = vORM::tablize($related_class);
		$class           = get_class($object);
		
		$exists          = $object->exists();
		$records_are_set = isset($related_records[$related_table][$route]);
		$has_records     = $records_are_set && $related_records[$related_table][$route]['count'];
		
		if ($exists && (!$records_are_set || $has_records)) {
			return;
		}
		
		if (!$exists && $has_records) {
			return;	
		}
		
		return self::compose(
			'%sPlease select at least one',
			vValidationException::formatField(vGrammar::pluralize(vORMRelated::getRelatedRecordName($class, $related_class, $route)))
		);
	}
	
	
	/**
	 * Validates values against unique constraints
	 *
	 * @param  vSchema        $schema       The schema object for the object
	 * @param  vActiveRecord  $object       The instance of the class to check
	 * @param  array          &$values      The values to check
	 * @param  array          &$old_values  The old values for the record
	 * @return array  An aray of error messages for the unique constraints
	 */
	static private function checkUniqueConstraints($schema, $object, &$values, &$old_values)
	{
		$class = get_class($object);
		$table = vORM::tablize($class);
		
		$db       = vORMDatabase::retrieve($class, 'read');
		$key_info = $schema->getKeys($table);
		
		$pk_columns  = $key_info['primary'];
		$unique_keys = $key_info['unique'];
		
		$messages = array();
		foreach ($unique_keys AS $unique_columns) {
			settype($unique_columns, 'array');
			
			// NULL values are unique
			$found_not_null = FALSE;
			foreach ($unique_columns as $unique_column) {
				if ($values[$unique_column] !== NULL) {
					$found_not_null = TRUE;
				}
			}
			if (!$found_not_null) {
				continue;
			}
			
			$params = array(
				"SELECT %r FROM %r WHERE ",
				$key_info['primary'],
				$table	
			);
			
			$column_info = $schema->getColumnInfo($table);
			
			$conditions = array();
			foreach ($unique_columns as $unique_column) {
				$value = $values[$unique_column];
				
				// This makes sure the query performs the way an insert will
				if ($value === NULL && $column_info[$unique_column]['not_null'] && $column_info[$unique_column]['default'] !== NULL) {
					$value = $column_info[$unique_column]['default'];
				}
				
				if (self::isCaseInsensitive($class, $unique_column) && self::stringlike($value)) {
					$condition    = vORMDatabase::makeCondition($schema, $table, $unique_column, '=', $value);
					$conditions[] = str_replace('%r', 'LOWER(%r)', $condition);
					$params[] = $table . '.' . $unique_column;
					$params[] = vUTF8::lower($value);
					
				} else {
					$conditions[] = vORMDatabase::makeCondition($schema, $table, $unique_column, '=', $value);
					$params[] = $table . '.' . $unique_column;
					$params[] = $value;
				}
			}
			
			$params[0] .= join(' AND ', $conditions);
			
			if ($object->exists()) {
				foreach ($pk_columns as $pk_column) {
					$value = vActiveRecord::retrieveOld($old_values, $pk_column, $values[$pk_column]);
					$params[0] .= ' AND ' . vORMDatabase::makeCondition($schema, $table, $pk_column, '<>', $value);
					$params[] = $table . '.' . $pk_column;
					$params[] = $value;
				}
			}
			
			try {
				$result = call_user_func_array($db->translatedQuery, $params);
				$result->tossIfNoRows();
			
				// If an exception was not throw, we have existing values
				$column_names = array();
				foreach ($unique_columns as $unique_column) {
					$column_names[] = vORM::getColumnName($class, $unique_column);
				}
				if (sizeof($column_names) == 1) {
					$messages[join('', $unique_columns)] = self::compose(
						'%sThe value specified must be unique, however it already exists',
						vValidationException::formatField(join('', $column_names))
					);
				} else {
					$messages[join(',', $unique_columns)] = self::compose(
						'%sThe values specified must be a unique combination, however the specified combination already exists',
						vValidationException::formatField(join(', ', $column_names))
					);
				}
			
			} catch (vNoRowsException $e) { }
		}
		
		return $messages;
	}
	
	
	/**
	 * Validates against a valid values rule
	 *
	 * @param  string $class         The class this rule applies to
	 * @param  array  &$values       An associative array of all values for the record
	 * @param  string $column        The column the rule applies to
	 * @param  array  $valid_values  An array of valid values to check the column against
	 * @return string  The error message for the rule specified
	 */
	static private function checkValidValuesRule($class, &$values, $column, $valid_values)
	{
		if ($values[$column] === NULL) {
			return;	
		}
		
		if (!in_array($values[$column], $valid_values)) {
			return self::compose(
				'%1$sPlease choose from one of the following: %2$s',
				vValidationException::formatField(vORM::getColumnName($class, $column)),
				join(', ', $valid_values)
			);
		}
	}
	
	
	/**
	 * Composes text using vText if loaded
	 * 
	 * @param  string  $message    The message to compose
	 * @param  mixed   $component  A string or number to insert into the message
	 * @param  mixed   ...
	 * @return string  The composed and possible translated message
	 */
	static private function compose($message)
	{
		$args = array_slice(func_get_args(), 1);
		
		if (class_exists('vText', FALSE)) {
			return call_user_func_array(
				array('vText', 'compose'),
				array($message, $args)
			);
		} else {
			return vsprintf($message, $args);
		}
	}
	
	
	/**
	 * Makes sure each rule array is set to at least an empty array
	 *
	 * @internal
	 * 
	 * @param  string $class  The class to initilize the arrays for
	 * @return void
	 */
	static private function initializeRuleArrays($class)
	{
		self::$conditional_rules[$class]         = (isset(self::$conditional_rules[$class]))         ? self::$conditional_rules[$class]         : array();
		self::$one_or_more_rules[$class]         = (isset(self::$one_or_more_rules[$class]))         ? self::$one_or_more_rules[$class]         : array();
		self::$only_one_rules[$class]            = (isset(self::$only_one_rules[$class]))            ? self::$only_one_rules[$class]            : array();
		self::$regex_rules[$class]               = (isset(self::$regex_rules[$class]))               ? self::$regex_rules[$class]               : array();
		self::$related_one_or_more_rules[$class] = (isset(self::$related_one_or_more_rules[$class])) ? self::$related_one_or_more_rules[$class] : array();
		self::$valid_values_rules[$class]        = (isset(self::$valid_values_rules[$class]))        ? self::$valid_values_rules[$class]        : array();
	}
	
	
	/**
	 * Adds metadata about features added by this class
	 * 
	 * @internal
	 * 
	 * @param  string $class      The class being inspected
	 * @param  string $column     The column being inspected
	 * @param  array  &$metadata  The array of metadata about a column
	 * @return void
	 */
	static public function inspect($class, $column, &$metadata)
	{
		if (!empty(self::$valid_values_rules[$class][$column])) {
			$metadata['valid_values'] = self::$valid_values_rules[$class][$column];
		}
	}
	
	
	/**
	 * Checks to see if a columns has a value, but based on the schema and if the column allows NULL
	 * 
	 * If the columns allows NULL values, than anything other than NULL
	 * will be returned as TRUE. If the column does not allow NULL and
	 * the value is anything other than the "empty" value for that data type,
	 * then TRUE will be returned.
	 * 
	 * The values that are considered "empty" for each data type are as follows.
	 * Please note that there is no "empty" value for dates, times or
	 * timestamps.
	 * 
	 *  - Blob: ''
	 *  - Boolean: FALSE
	 *  - Float: 0.0
	 *  - Integer: 0
	 *  - String: ''
	 *
	 * @internal
	 * 
	 * @param  vSchema $schema   The schema object for the table
	 * @param  string  $class    The class the column is part of
	 * @param  array   &$values  An associative array of all values for the record
	 * @param  array   $columns  The column to check
	 * @return string  An error message for the rule
	 */
	static public function hasValue($schema, $class, &$values, $column)
	{
		$value = $values[$column];
		
		if ($value === NULL) {
			return FALSE;	
		}
		
		$table       = vORM::tablize($class);
		$data_type   = $schema->getColumnInfo($table, $column, 'type');
		$allows_null = !$schema->getColumnInfo($table, $column, 'not_null');
		
		if ($allows_null) {
			return TRUE;
		}
		
		switch ($data_type) {
			case 'blob':
			case 'char':
			case 'text':
			case 'varchar':
				if ($value === '') {
					return FALSE;	
				}
				break;
			
			case 'boolean':
				if ($value === FALSE) {
					return FALSE;	
				}
				break;
			
			case 'integer':
				if ($value === 0 || $value === '0') {
					return FALSE;	
				}
				break;
			
			case 'float':
				if (preg_match('#^0(\.0*)?$|^\.0+$#D', $value)) {
					return FALSE;	
				}
				break;
		}
		
		return TRUE;
	}
	
	
	/**
	 * Checks to see if a column has been set as case insensitive
	 *
	 * @internal
	 * 
	 * @param  string $class   The class to check
	 * @param  string $column  The column to check
	 * @return boolean  If the column is set to be case insensitive
	 */
	static private function isCaseInsensitive($class, $column)
	{
		return isset(self::$case_insensitive_columns[$class][$column]);
	}
	
	
	/**
	 * Returns FALSE if the string is empty - used for array filtering
	 * 
	 * @param  string $string  The string to check
	 * @return boolean  If the string is not blank
	 */
	static private function isNonBlankString($string)
	{
		return ((string) $string) !== '';
	}
	
	
	/**
	 * Removes a regex replacement
	 * 
	 * @internal
	 * 
	 * @param  mixed  $class    The class name or instance of the class the columns exists in
	 * @param  string $search   The string to search for
	 * @param  string $replace  The string to replace with
	 * @return void
	 */
	static public function removeRegexReplacement($class, $search, $replace)
	{
		$class = vORM::getClass($class);
		
		if (!isset(self::$regex_replacements[$class])) {
			self::$regex_replacements[$class] = array(
				'search'  => array(),
				'replace' => array()
			);
		}
		
		$replacements = count(self::$regex_replacements[$class]['search']);
		
		for ($i = 0; $i < $replacements; $i++) {
			$match_search  = self::$regex_replacements[$class]['search'][$i] == $search;
			$match_replace = self::$regex_replacements[$class]['replace'][$i] == $replace;
			if ($match_search && $match_replace) {
				unset(self::$regex_replacements[$class]['search'][$i]);
				unset(self::$regex_replacements[$class]['replace'][$i]);
			}
		}
		
		// Remove the any gaps in the arrays
		self::$regex_replacements[$class]['search']  = array_merge(self::$regex_replacements[$class]['search']);
		self::$regex_replacements[$class]['replace'] = array_merge(self::$regex_replacements[$class]['replace']);
	}
	
	
	/**
	 * Removes a string replacement
	 * 
	 * @internal
	 * 
	 * @param  mixed  $class    The class name or instance of the class the columns exists in
	 * @param  string $search   The string to search for
	 * @param  string $replace  The string to replace with
	 * @return void
	 */
	static public function removeStringReplacement($class, $search, $replace)
	{
		$class = vORM::getClass($class);
		
		if (!isset(self::$string_replacements[$class])) {
			self::$string_replacements[$class] = array(
				'search'  => array(),
				'replace' => array()
			);
		}
		
		$replacements = count(self::$string_replacements[$class]['search']);
		
		for ($i = 0; $i < $replacements; $i++) {
			$match_search  = self::$string_replacements[$class]['search'][$i] == $search;
			$match_replace = self::$string_replacements[$class]['replace'][$i] == $replace;
			if ($match_search && $match_replace) {
				unset(self::$string_replacements[$class]['search'][$i]);
				unset(self::$string_replacements[$class]['replace'][$i]);
			}
		}
		
		// Remove the any gaps in the arrays
		self::$string_replacements[$class]['search']  = array_merge(self::$string_replacements[$class]['search']);
		self::$string_replacements[$class]['replace'] = array_merge(self::$string_replacements[$class]['replace']);
	}
	
	
	/**
	 * Reorders list items in an html string based on their contents
	 * 
	 * @internal
	 * 
	 * @param  string $class     The class to reorder messages for
	 * @param  array  $messages  An array of the messages
	 * @return array  The reordered messages
	 */
	static public function reorderMessages($class, $messages)
	{
		if (!isset(self::$message_orders[$class])) {
			return $messages;
		}
			
		$matches = self::$message_orders[$class];
		
		$ordered_items = array_fill(0, sizeof($matches), array());
		$other_items   = array();
		
		foreach ($messages as $key => $message) {
			foreach ($matches as $num => $match_string) {
				$string = is_array($message) ? $message['name'] : $message;
				if (vUTF8::ipos($string, $match_string) !== FALSE) {
					$ordered_items[$num][$key] = $message;
					continue 2;
				}
			}
			
			$other_items[$key] = $message;
		}
		
		$final_list = array();
		foreach ($ordered_items as $ordered_item) {
			$final_list = array_merge($final_list, $ordered_item);
		}
		return array_merge($final_list, $other_items);
	}
	
	
	/**
	 * Takes a list of messages and performs string and regex replacements on them
	 * 
	 * @internal
	 * 
	 * @param  string $class     The class to reorder messages for
	 * @param  array  $messages  The array of messages
	 * @return array  The new array of messages
	 */
	static public function replaceMessages($class, $messages)
	{
		if (isset(self::$string_replacements[$class])) {
			foreach ($messages as $key => $message) {
				if (is_array($message)) {
					continue;
				}
				$messages[$key] = str_replace(
					self::$string_replacements[$class]['search'],
					self::$string_replacements[$class]['replace'],
					$message	
				);
			}
		}
		
		if (isset(self::$regex_replacements[$class])) {
			foreach ($messages as $key => $message) {
				if (is_array($message)) {
					continue;
				}
				$messages[$key] = preg_replace(
					self::$regex_replacements[$class]['search'],
					self::$regex_replacements[$class]['replace'],
					$message	
				);
			}
		}
		
		return array_filter($messages, array('vORMValidation', 'isNonBlankString'));
	}
	
	
	/**
	 * Resets the configuration of the class
	 * 
	 * @internal
	 * 
	 * @return void
	 */
	static public function reset()
	{
		self::$case_insensitive_columns  = array();
		self::$conditional_rules         = array();
		self::$message_orders            = array();
		self::$one_or_more_rules         = array();
		self::$only_one_rules            = array();
		self::$regex_replacements        = array();
		self::$related_one_or_more_rules = array();
		self::$regex_rules               = array();
		self::$required_rules            = array();
		self::$string_replacements       = array();
		self::$valid_values_rules        = array();
	}
	
	
	/**
	 * Sets a column to be compared in a case-insensitive manner when checking `UNIQUE` and `PRIMARY KEY` constraints
	 *
	 * @param  mixed  $class   The class name or instance of the class the column is located in
	 * @param  string $column  The column to set as case-insensitive
	 * @return void
	 */
	static public function setColumnCaseInsensitive($class, $column)
	{
		$class  = vORM::getClass($class);
		$table  = vORM::tablize($class);
		$schema = vORMSchema::retrieve($class);
		
		$type = $schema->getColumnInfo($table, $column, 'type');
		$valid_types = array('varchar', 'char', 'text');
		if (!in_array($type, $valid_types)) {
			throw new vProgrammerException(
				'The column specified, %1$s, is of the data type %2$s. Must be one of %3$s to be treated as case insensitive.',
				$column,
				$type,
				join(', ', $valid_types)
			);
		}
		
		if (!isset(self::$case_insensitive_columns[$class])) {
			self::$case_insensitive_columns[$class] = array();
		}
		
		self::$case_insensitive_columns[$class][$column] = TRUE;
	}
	
	
	/**
	 * Allows setting the order that the list items in a message will be displayed
	 *
	 * All string comparisons during the reordering process are done in a
	 * case-insensitive manner.
	 * 
	 * @param  mixed $class    The class name or an instance of the class to set the message order for
	 * @param  array $matches  This should be an ordered array of strings. If a line contains the string it will be displayed in the relative order it occurs in this array.
	 * @return void
	 */
	static public function setMessageOrder($class, $matches)
	{
		$class = vORM::getClass($class);
		
		// Handle the alternate form allowed with vValidation::setMessageOrder()
		$args = func_get_args();
		array_shift($args);
		if (count($args) != 1) {
			$matches = $args;
		}
		
		uasort($matches, array('self', 'sortMessageMatches'));
		self::$message_orders[$class] = $matches;
	}
	
	
	/**
	 * Compares the message matching strings by longest first so that the longest matches are made first
	 *
	 * @param  string $a  The first string to compare
	 * @param  string $b  The second string to compare
	 * @return integer  `-1` if `$a` is longer than `$b`, `0` if they are equal length, `1` if `$a` is shorter than `$b`
	 */
	static private function sortMessageMatches($a, $b)
	{
		if (strlen($a) == strlen($b)) {
			return 0;	
		}
		if (strlen($a) > strlen($b)) {
			return -1;	
		}
		return 1;
	}
	
	
	/**
	 * Returns `TRUE` for non-empty strings, numbers, objects, empty numbers and string-like numbers (such as `0`, `0.0`, `'0'`)
	 * 
	 * @param  mixed $value  The value to check
	 * @return boolean  If the value is string-like
	 */
	static private function stringlike($value)
	{
		if ((!is_string($value) && !is_object($value) && !is_numeric($value)) || !strlen(trim($value))) {
			return FALSE;	
		}
		
		return TRUE;
	}
	
	
	/**
	 * Validates values for an vActiveRecord object against the database schema and any additional rules that have been added
	 *
	 * @internal
	 * 
	 * @param  vActiveRecord  $object      The instance of the class to validate
	 * @param  array          $values      The values to validate
	 * @param  array          $old_values  The old values for the record
	 * @return array  An array of messages
	 */
	static public function validate($object, $values, $old_values)
	{
		$class  = get_class($object);
		$table  = vORM::tablize($class);
		$schema = vORMSchema::retrieve($class);
		
		self::initializeRuleArrays($class);
		
		$validation_messages = array();
		
		// Convert objects into values for validation
		foreach ($values as $column => $value) {
			$values[$column] = vORM::scalarize($class, $column, $value);
		}
		foreach ($old_values as $column => $column_values) {
			foreach ($column_values as $key => $value) {
				$old_values[$column][$key] = vORM::scalarize($class, $column, $value);
			}
		}
		
		$message_array = self::checkPrimaryKeys($schema, $object, $values, $old_values);
		if ($message_array) { $validation_messages[key($message_array)] = current($message_array); }
		
		$column_info = $schema->getColumnInfo($table);
		foreach ($column_info as $column => $info) {
			$message = self::checkAgainstSchema($schema, $object, $column, $values, $old_values);
			if ($message) { $validation_messages[$column] = $message; }
		}
		
		$messages = self::checkUniqueConstraints($schema, $object, $values, $old_values);
		if ($messages) { $validation_messages = array_merge($validation_messages, $messages); }
		
		foreach (self::$valid_values_rules[$class] as $column => $valid_values) {
			$message = self::checkValidValuesRule($class, $values, $column, $valid_values);
			if ($message) { $validation_messages[$column] = $message; }
		}
		
		foreach (self::$regex_rules[$class] as $column => $rule) {
			$message = self::checkRegexRule($class, $values, $column, $rule['regex'], $rule['message']);
			if ($message) { $validation_messages[$column] = $message; }
		}
		
		foreach (self::$conditional_rules[$class] as $rule) {
			$messages = self::checkConditionalRule($schema, $class, $values, $rule['main_columns'], $rule['conditional_values'], $rule['conditional_columns']);
			if ($messages) { $validation_messages = array_merge($validation_messages, $messages); }
		}
		
		foreach (self::$one_or_more_rules[$class] as $rule) {
			$message = self::checkOneOrMoreRule($schema, $class, $values, $rule['columns']);
			if ($message) { $validation_messages[join(',', $rule['columns'])] = $message; }
		}
		
		foreach (self::$only_one_rules[$class] as $rule) {
			$message = self::checkOnlyOneRule($schema, $class, $values, $rule['columns']);
			if ($message) { $validation_messages[join(',', $rule['columns'])] = $message; }
		}
		
		return $validation_messages;
	}
	
	
	/**
	 * Validates related records for an vActiveRecord object
	 *
	 * @internal
	 * 
	 * @param  vActiveRecord $object            The object to validate
	 * @param  array         &$values           The values for the object
	 * @param  array         &$related_records  The related records for the object
	 * @return array         An array of messages
	 */
	static public function validateRelated($object, &$values, &$related_records)
	{
		$class = get_class($object);
		$table = vORM::tablize($class);
		
		$validation_messages = array();
		
		// Check related rules 
		foreach (self::$related_one_or_more_rules[$class] as $related_class => $routes) {
			foreach ($routes as $route => $enabled) {
				$message = self::checkRelatedOneOrMoreRule($object, $values, $related_records, $related_class, $route);
				if ($message) { $validation_messages[vORM::tablize($related_class)] = $message; }
			}
		}
		
		$related_messages    = vORMRelated::validate($class, $values, $related_records);
		$validation_messages = array_merge($validation_messages, $related_messages);
		
		return $validation_messages;
	}
}



/**
 * Copyright (c) Alan Johnston of Velus Universe Ltd <alan.johnston@velusuniverse.co.uk>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */