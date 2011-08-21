<?php
/************************************************************
 * E-Storefront by Ben M. Ward is licensed under a
 * Creative Commons Attribution 3.0 Unported License.
 * http://creativecommons.org/licenses/by/3.0/
 * Based on a work at https://github.com/Foran/E-Storefront.
 ***********************************************************/

$installer = new Installer();

switch($global_Permalink) {
	case '/finalstep.html':
		$installer->FinalStep();
		break;
	case '/step1.html':
		$installer->Step1();
		break;
	case '/welcome.html':
	default:
		$installer->WelcomeScreen();
		break;
}

exit;

if(!class_exists('Installer')) {
	class Installer {
		public function __construct() {
			$this->m_CurrentStep = '';
		}
		
		public function WelcomeScreen() {
			$this->RedirectStep();

			$macros = array();
			$macros['content'] = '<h1>Welcome to the Install Wizard for E-Storefront</h1>';
			$macros['content'] .= '<p><a href="/step1.html">Continue to Step 1</a></p>';
			DisplayTemplate('installer.html', $macros);
			exit;
		}
		
		public function Step1() {
			$macros = array();
			$macros['content'] = '<h1>Install Wizard for E-Storefront</h1>';
			$macros['content'] .= '<h2>Step 1</h2>';

			global $global_Database;
			if(@$_REQUEST['do_post'] == '1') {
				if(strlen(trim(@$_REQUEST['hostname'])) > 0 && strlen(gethostbyname(trim(@$_REQUEST['hostname']))) > 0 && strlen(trim(@$_REQUEST['username'])) > 0 && strlen(trim(@$_REQUEST['password'])) > 0 && strlen(trim(@$_REQUEST['database'])) > 0) {
					if($global_Database->Connect(trim(@$_REQUEST['hostname']), trim(@$_REQUEST['username']), trim(@$_REQUEST['password']), trim(@$_REQUEST['database']))) {
						$_SESSION['database']['hostname'] = trim(@$_REQUEST['hostname']);
						$_SESSION['database']['username'] = trim(@$_REQUEST['username']);
						$_SESSION['database']['password'] = trim(@$_REQUEST['password']);
						$_SESSION['database']['database'] = trim(@$_REQUEST['database']);
						$_SESSION['database']['prefix'] = trim(@$_REQUEST['prefix']);
					}
				}
			}
			$this->RedirectStep();

			$macros['content'] .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
			$macros['content'] .= '<input type="hidden" name="do_post" value="1" />';
			$macros['content'] .= '<table>';
			$macros['content'] .= '<tr><th colspan="2">Database Information</th></tr>';
			$macros['content'] .= '<tr><th>Hostname</th><td><input type="text" name="hostname" value="'.htmlentities(trim(@$_REQUEST['hostname'])).'" /></td></tr>';
			$macros['content'] .= '<tr><th>Username</th><td><input type="text" name="username" value="'.htmlentities(trim(@$_REQUEST['username'])).'" /></td></tr>';
			$macros['content'] .= '<tr><th>Password</th><td><input type="text" name="password" value="'.htmlentities(trim(@$_REQUEST['password'])).'" /></td></tr>';
			$macros['content'] .= '<tr><th>Database</th><td><input type="text" name="database" value="'.htmlentities(trim(@$_REQUEST['database'])).'" /></td></tr>';
			$macros['content'] .= '<tr><th>Prefix</th><td><input type="text" name="prefix" value="'.htmlentities(trim(@$_REQUEST['prefix']) == '' ? 'esf_' : trim(@$_REQUEST['prefix'])).'" /></td></tr>';
			$macros['content'] .= '<tr><td colspan="2"><input type="submit" value="Next" /></td></tr>';
			$macros['content'] .= '</table>';
			$macros['content'] .= '</form>';
			DisplayTemplate('installer.html', $macros);
			exit;
		}
		
		public function Step2() {
			$macros = array();
			$macros['content'] = '<h1>Install Wizard for E-Storefront</h1>';
			$macros['content'] .= '<h2>Step 2</h2>';

			global $global_Database;
			if($this->GetStep() == 'step2') {
				$config = '<?xml version="1.0" charset="utf-8" ?>'."\r\n";
				$config .= '<Configuration Version="1.0">';
				$config .= '<Database Hostname="'.htmlentities($_SESSION['database']['hostname']).'"';
				$config .= ' Username="'.htmlentities($_SESSION['database']['username']).'"';
				$config .= ' Password="'.htmlentities($_SESSION['database']['password']).'"';
				$config .= ' Database="'.htmlentities($_SESSION['database']['database']).'"';
				$config .= ' Prefix="'.htmlentities($_SESSION['database']['prefix']).'" />';
				$config .= '</Configuration>';
				if(!file_exists('Config/Main.xml')) @file_put_contents('Config/Main.xml', $config);
			}
			$this->RedirectStep();

			$macros['content'] .= '<p>Please write the following to Config/Main.xml</p>';
			$macros['content'] .= '<textarea>'.htmlentities($config).'</textarea>';
			
			DisplayTemplate('installer.html', $macros);
			exit;
		}

		public function FinalStep() {
			$this->RedirectStep();
			if(!@unlink(__FILE__)) {
				$macros = array();
				$macros['content'] = '<h1>Error</h1><p>Failed to remove '.__FILE__.', please delete this file to enable this site</p>';
				DisplayTemplate('installer.html', $macros);
				exit;
			}
		}
		
		private $m_CurrentStep;
		/**
		 * @return string CurrentStep
		 */
		public function GetStep() {
			global $global_Permalink;

			if($this->m_CurrentStep == '') {
				$this->m_CurrentStep = 'welcome';
				if($global_Permalink == '/step1.html') {
					$this->m_CurrentStep = 'step1';
				}
				if(isset($_SESSION['database']['hostname']) && isset($_SESSION['database']['username']) && isset($_SESSION['database']['password']) && isset($_SESSION['database']['database']) && isset($_SESSION['database']['prefix'])) {
					$this->m_CurrentStep = 'step2';
				}
				else if(file_exists('Config/Main.xml')) {
					$xml = @simplexml_load_file('Config/Main.xml');
					if(isset($xml->Database) && isset($xml->Database['Hostname']) && isset($xml->Database['Username']) && isset($xml->Database['Password']) && isset($xml->Database['Database'])) {
						if(!isset($_SESSION['database']['hostname'])) $_SESSION['database']['hostname'] = trim($xml->Database['Hostname']);
						if(!isset($_SESSION['database']['username'])) $_SESSION['database']['username'] = trim($xml->Database['Username']);
						if(!isset($_SESSION['database']['password'])) $_SESSION['database']['password'] = trim($xml->Database['Password']);
						if(!isset($_SESSION['database']['database'])) $_SESSION['database']['database'] = trim($xml->Database['Database']);
						if(!isset($_SESSION['database']['prefix'])) $_SESSION['database']['prefix'] = isset($xml->Database['Database']) ? trim($xml->Database['Database']) : '';
						global $global_Database;
						if($global_Database->Connect(trim(@$_REQUEST['hostname']), trim(@$_REQUEST['username']), trim(@$_REQUEST['password']), trim(@$_REQUEST['database']))) {
							$this->m_CurrentStep = 'step3';
						}	
					}
				}
			}
			return $this->m_CurrentStep;
		}
		
		/**
		 * Redirects to the correct step
		 */
		public function RedirectStep()
		{
			global $global_Permalink;
			
			$destination = '';
			switch($this->GetStep()) {
				case 'welcome':
					$destination = '/welcome.html';
					break;
				case 'step1':
					$destination = '/step1.html';
					break;
				case 'step2':
					$destination = '/step2.html';
					break;
				case 'step3':
					$destination = '/step3.html';
					break;
				default:
					break;
			}
			
			if($destination != '' && $destination != $global_Permalink) {
				header('HTTP 301 Permenent Redirect');
				header('Location: '.$destination);
				exit;
			}
		}
	}
}
?>