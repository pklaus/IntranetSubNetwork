<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link https://github.com/pklaus/IntranetSubNetwork
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * 
 * @package Piwik_IntranetSubNetwork
 */

function Piwik_getHostSubnetName($in)
{
	if(empty($in))
		return Piwik_Translate('General_Unknown');
	return $in;
}
