<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link https://github.com/pklaus/IntranetSubNetwork
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * 
 * @package Piwik_IntranetSubNetwork
 */
	
/**
 * 
 * @package Piwik_IntranetSubNetwork
 */
class Piwik_IntranetSubNetwork extends Piwik_Plugin
{
	public function getInformation()
	{
		$info = array(
			'name' => 'IntranetSubNetwork',
			'description' => 'Assigns network names to the visitors according to their IP.',
			'author' => 'Philipp Klaus (orig: Alan)',
			'homepage' => 'https://github.com/pklaus/IntranetSubNetwork',
			'version' => '0.3',
			'TrackerPlugin' => true, // this plugin must be loaded during the stats logging
		);
		
		return $info;
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'Tracker.newVisitorInformation' => 'logIntranetSubNetworkInfo',
			'WidgetsList.add' => 'addWidget',
			'Menu.add' => 'addMenu',
		);
		return $hooks;
	}
	
	function install()
	{
		// add column hostname / hostname ext in the visit table
		$query = "ALTER IGNORE TABLE `".Piwik_Common::prefixTable('log_visit')."` ADD `location_IntranetSubNetwork` VARCHAR( 100 ) NULL";
		
		// if the column already exist do not throw error. Could be installed twice...
		try {
			Piwik_Exec($query);
		}
		catch(Exception $e){}
	}
	
	function uninstall()
	{
		// add column hostname / hostname ext in the visit table
		$query = "ALTER TABLE `".Piwik_Common::prefixTable('log_visit')."` DROP `location_IntranetSubNetwork`";
		Piwik_Exec($query);
	}
	
	function addWidget()
	{
		Piwik_AddWidget('General_Visitors', 'IntranetSubNetwork_WidgetIntranetSubNetwork', 'IntranetSubNetwork', 'getIntranetSubNetwork');
	}
	
	function addMenu()
	{
		Piwik_RenameMenuEntry(	'General_Visitors', 'UserCountry_SubmenuLocations', 
								'General_Visitors', 'IntranetSubNetwork_SubmenuLocationsIntranetSubNetwork');
	}
	
	function postLoad()
	{
		Piwik_AddAction('template_headerUserCountry', array('Piwik_IntranetSubNetwork','headerUserCountry'));
		Piwik_AddAction('template_footerUserCountry', array('Piwik_IntranetSubNetwork','footerUserCountry'));
	}

	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		$dataTableToSum = array( 'IntranetSubNetwork_hostnameExt' );
		$archiveProcessing->archiveDataTable($dataTableToSum);
	}

	/**
	 * Archive the IntranetSubNetwork count
	 */
	function archiveDay($notification)
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$recordName = 'IntranetSubNetwork_hostnameExt';
		$labelSQL = "location_IntranetSubNetwork";
		$interestByIntranetSubNetwork = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		$tableIntranetSubNetwork = $archiveProcessing->getDataTableFromArray($interestByIntranetSubNetwork);
		$archiveProcessing->insertBlobRecord($recordName, $tableIntranetSubNetwork->getSerialized());
		destroy($tableIntranetSubNetwork);
	}
	
	/**
	 * Logs the IntranetSubNetwork in the log_visit table
	 */
	public function logIntranetSubNetworkInfo($notification)
	{
		$visitorInfo =& $notification->getNotificationObject();
		
		
		$hostname = inet_ntop($visitorInfo['location_ip']);
		/**
		 *********************************************************************************************
		 *********************************************************************************************
		 ****************** adopt the following lines according to your subnets **********************
		 *********************************************************************************************
		 *********************************************************************************************
		**/ 
// Some default subnets:
if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('0.0.0.0/0')))      { $hostname ='Global IPv4';  } // all IPv4 addresses
if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('::/0')))           { $hostname = 'Global IPv6'; } // IPv6 addresses
if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('::ffff:0:0/96')))  { $hostname = 'Global IPv4'; } // IPv4 mapped IPv6 addresses
// You may include your custom subnets:
//if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('141.2.0.0/16')))       { $hostname ='University Frankfurt'; }
//if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('192.0.2.0/24')))       { $hostname ='TEST-NET'; }
//if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('198.51.100.0/24')))    { $hostname ='TEST-NET-2'; } 
//if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('2001:db8::/33', 
//                                                             '2001:db8:8000::/33'))) { $hostname ='Doc-IPv6'; }

		/**
		 *********************************************************************************************
		 *********************************************************************************************
		 ******************* end adopt here to your subnets  *****************************************
		 *********************************************************************************************
		 *********************************************************************************************
		**/ 

		$hostnameExtension = $hostname;
		
		// add the IntranetSubNetwork value in the table log_visit
		$visitorInfo['location_IntranetSubNetwork'] = $hostnameExtension;
		$visitorInfo['location_IntranetSubNetwork'] = substr($visitorInfo['location_IntranetSubNetwork'], 0, 100);

		// improve the country using the IntranetSubNetwork extension if valid
		$hostnameDomain = substr($hostnameExtension, 1 + strrpos($hostnameExtension, '.'));
		if(in_array($hostnameDomain, Piwik_Common::getCountriesList()))
		{
			$visitorInfo['location_country'] = $hostnameDomain;
		}
	}
	
	/**
	 * Returns the hostname extension (site.co.jp in fvae.VARG.ceaga.site.co.jp)
	 * given the full hostname looked up from the IP
	 * 
	 * @param string $hostname
	 * 
	 * @return string
	 */
	private function getCleanHostname($hostname)
	{
		$extToExclude = array(
			'com', 'net', 'org', 'co'
		);
		
		$off = strrpos($hostname, '.');
		$ext = substr($hostname, $off);
	
		if(empty($off) || is_numeric($ext) || strlen($hostname) < 5)
		{
			return 'Ip';
		}
		else
		{
			$e = explode('.', $hostname);
			$s = sizeof($e);
			
			// if extension not correct
			if(isset($e[$s-2]) && in_array($e[$s-2], $extToExclude))
			{
				return $e[$s-3].".".$e[$s-2].".".$e[$s-1];
			}
			else
			{
				return $e[$s-2].".".$e[$s-1];
			}
		}
	}
	
	/**
	 * Returns the hostname given the string IP in the format ip2long
	 * php.net/ip2long
	 * 
	 * @param string $ip
	 * 
	 * @return string hostname
	 */
	private function getHost($ip)
	{
		return trim(strtolower(@gethostbyaddr(long2ip($ip))));
	}

	public function headerUserCountry($notification)
	{
		$out =& $notification->getNotificationObject();
		$out = '<div id="leftcolumn">';
	}
	
	public function footerUserCountry($notification)
	{
		$out =& $notification->getNotificationObject();
		$out = '</div>
			<div id="rightcolumn">
			<h2>IntranetSubNetworks</h2>';
		$out .= Piwik_FrontController::getInstance()->fetchDispatch('IntranetSubNetwork','getIntranetSubNetwork');
		$out .= '</div>';
	}
}
