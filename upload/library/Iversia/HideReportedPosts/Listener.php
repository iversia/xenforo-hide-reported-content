<?php

class Iversia_HideReportedPosts_Listener
{

    public static function loadClassModel($class, array &$extend)
    {
        static $classes = array(
            'XenForo_Model_Report',
        );

        if (in_array($class, $classes)) {
            $extend[] = str_replace('XenForo_', 'Iversia_HideReportedPosts_Extend_', $class);
        }
    }
}