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
		 * @param Primary_Key $id
		 */
		function __construct($id = null);
		function Reset();
		/**
		 * @param Primary_Key $id
		 * @return bool
		 */
		function Load($id);
		/**
		 * @return bool
		 */
		function Save();
		/**
		 * @param bool $cascade
		 * @return bool
		 */
		function Delete($cascade = false);
	}
}
?>