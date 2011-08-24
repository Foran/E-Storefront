<?php
require_once('Interfaces/Database.php');
require_once('Interfaces/Searchable.php');
require_once('Classes/Utility/Database.php');
require_once('Classes/Core/BaseDataType.php');

if(interface_exists('iDatabase') && interface_exists('iSearchable') && class_exists('Utility_Database') && class_exists('Core_BaseDataType') && !class_exists('Core_Page')) {
	class Core_Page extends Core_BaseDataType implements iDatabase {
		/**
		 * @param Primary_Key $Page_ID
		 */
		function __construct($Page_ID = null) {
			$this->m_Table = 'Pages';
			$this->m_data_Members = array(	'Page_ID' => null,
											'Name' => null,
											'Content' => null,
											'Status' => null,
											'Title' => null,
											'Keywords' => null,
											'Description' => null,
											'Template' => null,
											'Created' => null,
											'Last_Updated' => null);
			$this->m_data_Member_Map = array(	'Page_ID' => array('primary_key', 'get'),
											'Name' => array('get', 'set'),
											'Content' => array('get', 'set'),
											'Status' => array('get', 'set'),
											'Title' => array('get', 'set'),
											'Keywords' => array('get', 'set'),
											'Description' => array('get', 'set'),
											'Template' => array('get', 'set'),
											'Created' => array('created', 'get'),
											'Last_Updated' => array('last_updated', 'get'));
			parent::__construct($Page_ID);
		}
	}
}

?>