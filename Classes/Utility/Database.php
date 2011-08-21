<?php
/************************************************************
 * E-Storefront by Ben M. Ward is licensed under a
 * Creative Commons Attribution 3.0 Unported License.
 * http://creativecommons.org/licenses/by/3.0/
 * Based on a work at https://github.com/Foran/E-Storefront.
 ***********************************************************/

require_once('Interfaces/Database.php');

if(!class_exists('Utility_Database') && interface_exists('iDatabase')) {
	class Utility_Database implements iDatabase {
		private $m_Connection = null;
		private $m_Prefix = '';
		private $m_Query_Count = 0;
		private $m_Queries = array();
		private $m_Last_Query = null;
		
		function get_Query_Count() {
			return $this->m_Query_Count;
		}
		
		function get_Queries() {
			$retval = array_keys($this->m_Queries);
			
			return $retval;
		}
		
		function get_Prefix() {
			return $this->m_Prefix;
		}
		
		function set_Prefix($prefix) {
			if(is_string($prefix)) $this->m_Prefix = $prefix;
		}
		
		function Connect($host = null, $username = null, $password = null, $database = null) {
			$retval = false;
			$this->Close();
			
			$result = mysql_connect($host, $username, $password);
			if($result) {
				$retval = true;
				$this->m_Connection = $result;
				if(!is_null($database) && !mysql_select_db($database, $this->m_Connection)) {
					$retval = false;
					$this->Close();
				}
			}
			return $retval;
		}
		
		function Query($query) {
			$retval = false;
			
			if($this->is_Connected()) {
				$this->m_Last_Query = $query;
				if(isset($this->m_Queries[$query]) && $this->m_Queries[$query]['Stale'] === false) {
					$retval = $this->m_Queries[$query]['Result'];
					if($retval) @mysql_data_seek($retval, 0);
				}
				else {
					$retval = mysql_query($query, $this->m_Connection);
					$this->m_Query_Count++;
					if(isset($this->m_Queries[$query])) {
						$this->m_Queries[$query]['Count']++;
						$this->m_Queries[$query]['Success'] = $retval ? true : false;
						$this->m_Queries[$query]['Result'] = $retval;
					}
					else {
						$this->m_Queries[$query] = array('Query' => $query, 'Success' => $retval ? true : false, 'Result' => $retval, 'Count' => 1, 'Stale' => true);
					}
				}
				if($retval && eregi('^(insert|update|delete) ', $query)) $this->Invalidate_Cache();
			}
			
			return $retval;
		}
		
		private function Invalidate_Cache() {
			foreach(array_keys($this->m_Queries) as $query) {
				$this->m_Queries[$query]['Stale'] = true;
			}
		}
		
		function BasicInsert($table, array $values, array $functions = array(), array $duplicate = array()) {
			$retval = false;
			
			if(count($values) > 0) {
				$cols = '';
				$vals = '';
				$first = true;
				foreach($values as $column => $value) {
					if($first) $first = false;
					else {
						$cols .= ', ';
						$vals .= ', ';
					}
					$cols .= "`$column`";
					if(is_null($value)) $vals .= "NULL";
					else {
						$val = "'".$this->Escape($value)."'";
						if(isset($functions[$column]) && $val == "''") $val = '';
						$vals .= isset($functions[$column]) ? $functions[$column]."($val)" : $val;
					}
				}
				$query = "INSERT INTO `{$this->m_Prefix}$table` ($cols) VALUES ($vals)";
				if(count($duplicate) > 0) {
					$query .= " ON DUPLICATE KEY UPDATE ";
					$first = true;
					foreach($duplicate as $column => $value) {
						if($first) $first = false;
						else {
							$query .= ', ';
						}
						$query .= "`$column`=";
						if(is_null($value)) $query .= "NULL";
						else {
							$val = "'".$this->Escape($value)."'";
							$query .= isset($functions[$column]) ? $functions[$column]."($val)" : $val;
						}
					}
				}
				$result = $this->Query($query);
				if($result) {
					$retval = $this->InsertID();
				}
			}
			
			return $retval;
		}
		
		function BasicSingleSelect($table, array $values = array(), array $functions = array()) {
			$retval = $this->BasicSelect($table, $values, array(), $functions);
			if($retval && $this->NumRows($retval) != 1) $retval = false;
			
			return $retval;
		}
		
		function BasicSelect($table, array $values = array(), array $orderBy = array(), array $functions = array(), $page = -1, $pageSize = 10) {
			$retval = false;
			
			$query = "SELECT * FROM `{$this->m_Prefix}$table` AS t1";
			if(count($values) > 0) {
				$query .= " WHERE ";
				$first = true;
				foreach($values as $column => $value) {
					if($first) $first = false;
					else $query .= " AND ";
					if(is_null($value)) $query .= "t1.`$column` IS NULL";
					else if(is_array($value)) {
						$query .= strtoupper(@$functions[$column]) == 'NOT IN' ? "t1.`$column` NOT IN (" : "t1.`$column` IN (";
						$subFirst = true;
						foreach($value as $val) {
							if($subFirst) $subFirst = false;
							else $query .= ", ";
							if(is_null($val)) $query .= "NULL";
							else $query .= "'".$this->Escape($val)."'";
						}
						$query .= ")";
					}
					else {
						$val = "'".$this->Escape($value)."'";
						if(isset($functions[$column])) {
							switch(strtoupper($functions[$column])) {
								case 'IS':
								case 'IS NOT':
								case '=':
								case '!=':
								case 'NOT LIKE':
								case 'REGEXP':
								case 'LIKE':
									$query .= "t1.`$column` ".strtoupper($functions[$column])." $val";
									break;
								case 'RREGEXP':
									$query .= "$val ".substr(strtoupper($functions[$column]), 1)." t1.`$column` ";
									break;
								default:
									$query .= "t1.`$column`={$functions[$column]}($val)";
									break;
							}
						}
						else $query .= "t1.`$column`=$val";
					}
				}
			}
			if(count($orderBy) > 0) {
				$query .= " ORDER BY ";
				$first = true;
				foreach($orderBy as $key => $column) {
					if($first) $first = false;
					else $query .= ", ";
					if(is_numeric($key)) $query .= "t1.`$column`";
					else {
						$query .= "t1.`$key`";
						if(in_array(strtoupper($column), array('ASC', 'DESC'))) $query .= ' '.strtoupper($column);
					}
				}
			}
			if($page > -1) {
				$query .= ' LIMIT '.intval($page).', '.intval($pageSize);
			}
			$retval = $this->Query($query);
			if(isset($this->m_Queries[$this->m_Last_Query])) $this->m_Queries[$this->m_Last_Query]['Stale'] = false;
			
			return $retval;
		}
		
		function BasicDelete($table, array $values = array(), array $functions = array()) {
			$retval = false;
			
			$query = "DELETE FROM `{$this->m_Prefix}$table`";
			if(count($values) > 0) {
				$query .= " WHERE ";
				$first = true;
				foreach($values as $column => $value) {
					if($first) $first = false;
					else $query .= " AND ";
					if(is_null($value)) $query .= "`$column` IS NULL";
					else if(is_array($value)) {
						$query .= strtoupper(@$functions[$column]) == 'NOT IN' ? "`$column` NOT IN (" : "`$column` IN (";
						$subFirst = true;
						foreach($value as $val) {
							if($subFirst) $subFirst = false;
							else $query .= ", ";
							if(is_null($val)) $query .= "NULL";
							else $query .= "'".$this->Escape($val)."'";
						}
						$query .= ")";
					}
					else {
						$val = "'".$this->Escape($value)."'";
						if(isset($functions[$column])) {
							switch(strtoupper($functions[$column])) {
								case 'IS':
								case 'IS NOT':
								case '=':
								case '!=':
								case 'NOT LIKE':
								case 'REGEXP':
								case 'LIKE':
									$query .= "`$column` ".strtoupper($functions[$column])." $val";
									break;
								case 'RREGEXP':
									$query .= "$val ".substr(strtoupper($functions[$column]), 1)." `$column` ";
									break;
								default:
									$query .= "`$column`={$functions[$column]}($val)";
									break;
							}
						}
						else $query .= "`$column`=$val";
					}
				}
			}
			$retval = $this->Query($query);
			
			return $retval;
		}
		
		function LoginSelect($table, $pass_col, $pass_val, array $values = array()) {
			$retval = false;
			
			$values[$pass_col] = $pass_val;
			$functions[$pass_col] = 'PASSWORD';
			$result = $this->BasicSingleSelect($table, $values, $functions);
			if($result) {
				$retval = $this->FetchRow($result);
			}

			return $retval;
		}
		
		/**
		 * Loads a row from a single database table by Primary Key
		 *
		 * @param string $table
		 * @param string $key_col
		 * @param string $key_val
		 * @return array|bool Returns false on failure otherwise it returns the result as an associative array
		 */
		function PrimaryKeySelect($table, $key_col, $key_val) {
			$retval = false;

			$query = "SELECT * FROM `{$this->m_Prefix}$table` AS t1 WHERE t1.`{$key_col}`='".$this->Escape($key_val)."'";
			$result = $this->Query($query);
			if($result && $this->NumRows($result) == 1) {
				$retval = $this->FetchRow($result);
			}
			
			return $retval;
		}
		
		function PrimaryKeyDelete($table, $key_col, $key_val) {
			$retval = false;
			
			$query = "DELETE FROM `{$this->m_Prefix}$table` WHERE`{$key_col}`='".$this->Escape($key_val)."'";
			$result = $this->Query($query);
			if($result) {
				$retval = $this->AffectedRows();
			}
			
			return $retval;
		}
		
		function PrimaryKeyUpdate($table, $key_col, $key_val, array $values, array $functions = array()) {
			$retval = false;
			
			if(count($values) > 0) {
				$query = "UPDATE `{$this->m_Prefix}$table` SET ";
				$first = true;
				foreach($values as $column => $value) {
					if($first) $first = false;
					else $query .= ", ";
					$query .= "`$column`";
					if(is_null($value)) $query .= "=NULL";
					else {
						$val = "'".$this->Escape($value)."'";
						$query .= "=".(isset($functions[$column]) ? $functions[$column]."($val)" : $val);
					}
				}
				$query .= " WHERE `{$key_col}`='".$this->Escape($key_val)."'";
				$result = $this->Query($query);
				if($result) {
					$retval = $this->AffectedRows();
				}
			}
			
			return $retval;
		}
		
		function InsertID() {
			return mysql_insert_id($this->m_Connection);
		}
		
		function AffectedRows() {
			return mysql_affected_rows($this->m_Connection);
		}
		
		function FetchRow($result) {
			return mysql_fetch_array($result);
		}
		
		function NumRows($result) {
			return mysql_num_rows($result);
		}
		
		function Escape($string) {
			return mysql_escape_string($string);
		}
		
		function is_Connected() {
			return is_null($this->m_Connection) ? false : true;
		}
		
		function Close() {
			if($this->is_Connected()) {
				mysql_close($this->m_Connection);
				$this->m_Connection = null;
			}
		}
	}
}

?>