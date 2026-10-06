<?php

defined('ABSPATH') or die('Access denied.');

class WPDataFoldersFactory
{
    public static function createTypeBased($type)
    {
        if ($type === 'table') {
            return new WPDataTablesFolders();
        } elseif ($type === 'chart') {
            return new WPDataChartsFolders();
        } elseif ($type === 'report') {
            return new WPDataReportsFolders();
        } else {
            return "";
        }
    }

    public static function createPageBased($page)
    {
        if ($page === 'wpdatatables-administration') {
            return new WPDataTablesFolders();
        } elseif ($page === 'wpdatatables-charts') {
            return new WPDataChartsFolders();
        } elseif ($page === 'wpdatareports') {
            return new WPDataReportsFolders();
        } else {
            return "";
        }
    }

}
