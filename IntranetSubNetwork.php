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
		return array(
			'name' => 'IntranetSubNetwork',
			'description' => 'Assigns network names to the visitors according to their IP.',
			'author' => 'Philipp Klaus (orig: Alain)',
			'homepage' => 'https://github.com/pklaus/IntranetSubNetwork',
			'author_homepage' => 'http://blog.philippklaus.de/2012/04/piwik-plugin-intranetsubnetwork-show-ipv4-vs-ipv6-statistics/',
			'version' => '0.4.2',
			'TrackerPlugin' => true, // this plugin must be loaded during the stats logging
			'translationAvailable' => true,
		);
	}

	public function getReportMetadata($notification)
	{
		$reports = &$notification->getNotificationObject();
		$reports[] = array(
			'category' => Piwik_Translate('General_Visitors'),
			'name' => Piwik_Translate('IntranetSubNetwork_WidgetIntranetSubNetwork'),
			'module' => 'IntranetSubNetwork',
			'action' => 'getIntranetSubNetwork',
			'dimension' => Piwik_Translate('IntranetSubNetwork_ColumnIntranetSubNetwork'),
			'documentation' => Piwik_Translate('IntranetSubNetwork_WidgetIntranetSubNetworkDocumentation', '<br />'),
			'metrics' => array(
					'nb_visits',
					'nb_uniq_visitors',
					'nb_visits_percentage' => Piwik_Translate('General_ColumnPercentageVisits'),
			),
			// There is no processedMetrics for this report
			'processedMetrics' => array(),
			'order' => 50
		);
	}

	public function getSegmentsMetadata($notification)
	{
		$segments =& $notification->getNotificationObject();
		$segments[] = array(
			'type' => 'dimension',
			'category' => 'Visit',
			'name' => Piwik_Translate('IntranetSubNetwork_ColumnIntranetSubNetwork'),
			'segment' => 'subnetwork',
			'acceptedValues' => 'Global IPv4, Global IPv6 etc.',
			'sqlSegment' => 'log_visit.location_IntranetSubNetwork'
		);
	}
	
	function getListHooksRegistered()
	{
		return array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'Tracker.newVisitorInformation' => 'logIntranetSubNetworkInfo',
			'API.getReportMetadata' => 'getReportMetadata',
			'API.getSegmentsMetadata' => 'getSegmentsMetadata',
			'WidgetsList.add' => 'addWidget',
		);
	}
	
	function install()
	{
		// add column location_IntranetSubNetwork in the visit table
		$query = "ALTER IGNORE TABLE `".Piwik_Common::prefixTable('log_visit')."` ADD `location_IntranetSubNetwork` VARCHAR( 100 ) NULL";
		
		// if the column already exist do not throw error. Could be installed twice...
		try {
			Piwik_Exec($query);
		}
		catch(Exception $e){
			if(!Zend_Registry::get('db')->isErrNo($e, '1060'))
				throw $e;
		}
	}
	
	function uninstall()
	{
		// remove column location_IntranetSubNetwork from the visit table
		$query = "ALTER TABLE `".Piwik_Common::prefixTable('log_visit')."` DROP `location_IntranetSubNetwork`";
		Piwik_Exec($query);
	}
	
	function addWidget()
	{
		Piwik_AddWidget('General_Visitors', 'IntranetSubNetwork_WidgetIntranetSubNetwork', 'IntranetSubNetwork', 'getIntranetSubNetwork');
	}
	
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		$dataTableToSum = array( 'IntranetSubNetwork_networkNameExt' );
		$archiveProcessing->archiveDataTable($dataTableToSum);
	}

	/**
	 * Archive the IntranetSubNetwork count
	 */
	function archiveDay($notification)
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$recordName = 'IntranetSubNetwork_networkNameExt';
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
		
		$ip = Piwik_IP::N2P($visitorInfo['location_ip']);
		// by default, we want the network name to be the IP address:
		$networkName = $ip;
		/**
		 *********************************************************************************************
		 ****************** adopt the following lines according to your subnets **********************
		 **/
		// Some default subnets:
		if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('0.0.0.0/0')))     { $networkName = 'Global IPv4'; } // all IPv4 addresses
		if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('::/0')))          { $networkName = 'Global IPv6'; } // IPv6 addresses
		if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('::ffff:0:0/96'))) { $networkName = 'Global IPv4'; } // IPv4 mapped IPv6 addresses
		// You may include your custom subnets:
		//if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('141.2.0.0/16')))	     { $networkName = 'University Frankfurt'; }
		//if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('192.0.2.0/24')))	     { $networkName = 'TEST-NET'; }
		//if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('198.51.100.0/24')))	  { $networkName = 'TEST-NET-2'; } 
		//if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('2001:db8::/33', 
		//	                                                           '2001:db8:8000::/33'))) { $networkName = 'Doc-IPv6'; }
		/**
		 ******************* end adopt here to your subnets	*****************************************
		 *********************************************************************************************
		 **/

		// add the IntranetSubNetwork value in the table log_visit
		$visitorInfo['location_IntranetSubNetwork'] = substr($networkName, 0, 100);
	}
}
