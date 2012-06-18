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

		$column = 'nb_visits';
		$percCol = 'nb_visits_percentage';
		$percColName = 'General_ColumnPercentageVisits';

		$visitsSums = $archive->getNumeric($column);
		//$visitsSum = Piwik_VisitsSummary_API::getInstance()->getVisits($idSite, $period, $date);
		//print_r($visitsSums);
		// check whether given tables are arrays
		if($dataTable instanceof Piwik_DataTable_Array) {
			$tableArray = $dataTable->getArray();
			$visitSumsArray = $visitsSums->getArray();
		} else {
			$tableArray = Array($dataTable);
			$visitSumsArray = Array($visitsSums);
		}
		// walk through the results and calculate the percentage
		foreach($tableArray as $key => $table) {
			foreach($visitSumsArray AS $k => $visits) {
				if($k == $key) {
					if(is_object($visits))
						$visitsSumTotal = (float)$visits->getFirstRow()->getColumn(0);
					else
						$visitsSumTotal = (float)$visits;
				}
			}

			$table->filter('ColumnCallbackAddColumnPercentage', array($percCol, Piwik_Archive::INDEX_NB_VISITS, $visitsSumTotal, 1));
			// we don't want <0% or >100%:
			$table->filter('RangeCheck', array($percCol));
		}
		return $dataTable;
	}
}

