<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: API.php 1335 2009-07-27 02:23:37Z vipsoft $
 * 
 * @package Piwik_IntranetSubNetwork
 */

require_once PIWIK_INCLUDE_PATH . '/plugins/IntranetSubNetwork/functions.php';

/**
 * 
 * @package Piwik_IntranetSubNetwork
 */
class Piwik_IntranetSubNetwork_API 
{
	static private $instance = null;
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	public function getIntranetSubNetwork( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getDataTable('IntranetSubNetwork_hostnameExt');
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS));
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'url', 'Piwik_getHostSubnetUrl'));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getHostSubnetName'));
		$dataTable->queueFilter('ReplaceColumnNames');
		return $dataTable;
	}
}

