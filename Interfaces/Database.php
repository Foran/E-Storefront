<?php
/************************************************************
 * E-Storefront by Ben M. Ward is licensed under a
 * Creative Commons Attribution 3.0 Unported License.
 * http://creativecommons.org/licenses/by/3.0/
 * Based on a work at https://github.com/Foran/E-Storefront.
 ***********************************************************/

if(!interface_exists('iDatabase')) {
	interface iDatabase {
		/**
		 * @return int
		 */
		function get_Query_Count();
		
		/**
		 * @return array
		 */
		function get_Queries();
		
		/**
		 * @return string
		 */
		function get_Prefix();
		
		/**
		 * @param string $prefix
		 */
		function set_Prefix($prefix);
		
		/**
		 * @param string $host
		 * @param string $username
		 * @param string $password
		 * @param string $database
		 * @return bool
		 */
		function Connect($host = null, $username = null, $password = null, $database = null);
		
		/**
		 * @param string $query
		 * @return bool|int result, false on failure
		 */
		function Query($query);
		
		/**
		 * @param string $table
		 * @param array $values
		 * @param array $functions
		 * @param array $duplicate
		 * @return bool|Primary_Key
		 */
		function BasicInsert($table, array $values, array $functions = array(), array $duplicate = array());
		
		/**
		 * @param string $table
		 * @param array $values
		 * @param array $functions
		 * @return bool|array
		 */
		function BasicSingleSelect($table, array $values = array(), array $functions = array());
		
		/**
		 * @param string $table
		 * @param array $values
		 * @param array $orderBy
		 * @param array $functions
		 * @param int $page
		 * @param int $pageSize
		 * @return bool|array
		 */
		function BasicSelect($table, array $values = array(), array $orderBy = array(), array $functions = array(), $page = -1, $pageSize = 10);
		
		/**
		 * @param string $table
		 * @param array $values
		 * @param array $functions
		 * @return bool
		 */
		function BasicDelete($table, array $values = array(), array $functions = array());
		
		/**
		 * @param string $table
		 * @param string $pass_col
		 * @param string $pass_val
		 * @param array $values
		 * @return bool|array
		 */
		function LoginSelect($table, $pass_col, $pass_val, array $values = array());

		/**
		 * Loads a row from a single database table by Primary Key
		 *
		 * @param string $table
		 * @param string $key_col
		 * @param string $key_val
		 * @return array|bool Returns false on failure otherwise it returns the result as an associative array
		 */
		function PrimaryKeySelect($table, $key_col, $key_val);
		
		/**
		 * @param string $table
		 * @param string $key_col
		 * @param string $key_val
		 * @return bool
		 */
		function PrimaryKeyDelete($table, $key_col, $key_val);
		
		/**
		 * @param string $table
		 * @param string $key_col
		 * @param string $key_val
		 * @param array $values
		 * @param array $functions
		 * @return bool|int
		 */
		function PrimaryKeyUpdate($table, $key_col, $key_val, array $values, array $functions = array());
		
		/**
		 * @return bool|Primary_Key
		 */
		function InsertID();
		
		/**
		 * @return bool|int
		 */
		function AffectedRows();
		
		/**
		 * @param int $result
		 * @return bool|array
		 */
		function FetchRow($result);
		
		/**
		 * @param int $result
		 * @return bool|int
		 */
		function NumRows($result);
		
		/**
		 * @param string $string
		 * @return string
		 */
		function Escape($string);
		
		/**
		 * @return bool
		 */
		function is_Connected();
		
		/**
		 * 
		 */
		function Close();
	}
}
?>