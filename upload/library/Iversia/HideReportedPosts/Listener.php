<?php

class Iversia_HideReportedPosts_Listener
{

    /**
     * Called when instantiating a class. This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param $class
     * @param array $extend
     */
    public static function load_class($class, array &$extend)
    {
        $extend[] = str_replace('XenForo_', 'Iversia_HideReportedPosts_Extend_', $class);
    }
}