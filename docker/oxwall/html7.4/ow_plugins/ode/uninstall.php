<?php

$prefs = BOL_PreferenceService::getInstance();

$prefs->deleteSection('ode_deep_url');
$prefs->deletePreference('ode_deep_datalet_list');
$prefs->deletePreference('ode_deep_client');
$prefs->deletePreference('od_provider');
$prefs->deletePreference('ode_organization');
$prefs->deletePreference('ode_dataset_list');