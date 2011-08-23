<?php
/************************************************************
 * E-Storefront by Ben M. Ward is licensed under a
 * Creative Commons Attribution 3.0 Unported License.
 * http://creativecommons.org/licenses/by/3.0/
 * Based on a work at https://github.com/Foran/E-Storefront.
 ***********************************************************/

require_once('Interfaces/DataType.php');

if(!class_exists('Core_BaseDataType') && interface_exists('iDataType')) {
	abstract class Core_BaseDataType implements iDataType {
		protected $m_data_Members = array();
		protected $m_data_Member_Map = array();
		private $m_Primary_Key = false;
		protected $m_Table = null;
		
		/**
		 * @param Primary_Key $id
		 */
		function __construct($id = null) {
			if($id != null) $this->Load($id);
			else $this->Reset();
		}
		
		protected function get_Primary_Key() {
			if($this->m_Primary_Key === false) {
				foreach($this->m_data_Members as $member => $value) {
					if(in_array($member, array_keys($this->m_data_Member_Map)) && in_array('primary_key', array_keys($this->m_data_Member_Map[$member]))) {
						$this->m_Primary_Key = $member;
						break;
					}
				}
			}
			
			$retval = $this->m_Primary_Key;
			
			return $retval;
		}
		
		function __get(string $name) {
			$retval = null;
			if(!in_array($name, array_keys($this->m_data_Members))) {
				trigger_error('Undefined property: '.$name, E_USER_ERROR);
			}
			else if(in_array($name, array_keys($this->m_data_Member_Map)) && !in_array('get', array_keys($this->m_data_Member_Map[$name]))) {
				trigger_error('Property \"'.$name.'\" has no read access', E_USER_ERROR);
			}
			else {
				$retval = $this->m_data_Members[$name];
			}
			
			return $retval;
		}
		
		function __set(string $name, mixed $value) {
			if(!in_array($name, array_keys($this->m_data_Members))) {
				trigger_error('Undefined property: '.$name, E_USER_ERROR);
			}
			else if(in_array($name, array_keys($this->m_data_Member_Map)) && !in_array('set', array_keys($this->m_data_Member_Map[$name]))) {
				trigger_error('Property \"'.$name.'\" has no write access', E_USER_ERROR);
			}
			else {
				$valid = true;
				if(in_array('validate', array_keys($this->m_data_Member_Map[$name]))) $valid = $this->m_data_Member_Map[$name]['validate']($value);
				if($valid) $this->m_data_Members[$name] = $value;
			}
		}
		
		function Reset() {
			foreach($this->m_data_Members as $member => $value) {
				$this->m_data_Members[$member] = (in_array($member, array_keys($this->m_data_Member_Map)) && !in_array('default', array_keys($this->m_data_Member_Map[$member]))) ? $this->m_data_Member_Map[$member]['default'] : null;
			}
		}
		
		/**
		 * @param Primary_Key $id
		 * @return bool
		 */
		function Load($id) {
			$retval = false;
			global $global_Database;
			
			$this->Reset();
			
			if(is_array($row = $global_Database->PrimaryKeySelect($this->m_Table, $this->get_Primary_Key(), $id))) {
				foreach($this->m_data_Members as $member => $value) {
					if(in_array($member, array_keys($this->m_data_Member_Map)) && !in_array('skip_load_from_database', array_keys($this->m_data_Member_Map[$member]))) {
						$custom = in_array($member, array_keys($this->m_data_Member_Map)) && in_array('load_from_database', array_keys($this->m_data_Member_Map[$member]));
						eval("\$this->$member = \$custom ? \$this->m_data_Member_Map[\$member]['load_from_database']() : \$row[\$member]");
					}
				}
				$retval = true;
			}
			
			return $retval;
		}
		
		/**
		 * @return bool
		 */
		function Save() {
			return false;
		}
		
		/**
		 * @param bool $cascade
		 * @return bool
		 */
		function Delete($cascade = false) {
			return false;
		}
	}
}
?>