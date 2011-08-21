<?php
/************************************************************
 * E-Storefront by Ben M. Ward is licensed under a
 * Creative Commons Attribution 3.0 Unported License.
 * http://creativecommons.org/licenses/by/3.0/
 * Based on a work at https://github.com/Foran/E-Storefront.
 ***********************************************************/

require_once('Interfaces/DataType.php');

if(!class_exists('Core_BaseDataType') && interface_exists('iDataType')) {
	class Core_BaseDataType implements iDataType {
		protected $m_data_Members = array();
		protected $m_data_Member_Map = array();
		
		/**
		 * @param Primary_Key $id
		 */
		function __construct($id = null) {
			$this->Reset();
			if($id != null) $this->Load($id);
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
				$this->m_data_Members[$member] = (in_array($name, array_keys($this->m_data_Member_Map)) && !in_array('default', array_keys($this->m_data_Member_Map[$name]))) ? $this->m_data_Member_Map[$name]['default'] : null;
			}
		}
		
		/**
		 * @param Primary_Key $id
		 * @return bool
		 */
		function Load($id) {
			return false;
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