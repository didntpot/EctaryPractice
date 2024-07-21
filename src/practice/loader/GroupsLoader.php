<?php


namespace practice\loader;

use practice\manager\GroupManager;

class GroupsLoader
{
    public static function initPermission()
    {
        GroupManager::initGroupCache();
        GroupManager::initPermissionInterface();
    }
}