<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link https://github.com/pklaus/IntranetSubNetwork
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function getIntranetSubNetwork( $idSite, $period, $date, $segment = false )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
		$dataTable = $archive->getDataTable('IntranetSubNetwork_networkNameExt');
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getSubnetName'));
		$dataTable->queueFilter('ReplaceColumnNames');
		//$dataTable->queueFilter('ReplaceSummaryRowLabel');
		return $dataTable;
	}
}

