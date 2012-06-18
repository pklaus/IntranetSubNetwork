<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link https://github.com/pklaus/IntranetSubNetwork
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @package Piwik_IntranetSubNetwork
 */

class Piwik_IntranetSubNetwork_Controller extends Piwik_Controller 
{	
	/**
	 * IntranetSubNetwork
	 */
	function getIntranetSubNetwork($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, __FUNCTION__, "IntranetSubNetwork.getIntranetSubNetwork" );
	
		$this->setPeriodVariablesView($view);
		$column = 'nb_visits';
		$percCol = 'nb_visits_percentage';
		$percColName = 'General_ColumnPercentageVisits';
		if($view->period == 'day')
			$column = 'nb_uniq_visitors';
		$view->setColumnsToDisplay( array('label',$percCol,$column) );
		$view->setColumnTranslation('label', Piwik_Translate('IntranetSubNetwork_ColumnIntranetSubNetwork'));
		$view->setColumnTranslation($percCol, str_replace('% ', '%&nbsp;', Piwik_Translate($percColName)));
		$view->setSortedColumn( $percCol );
		$view->setLimit( 5 );
		return $this->renderView($view, $fetch);
	}
	
}

