<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['GridElementsTeam\\Gridelements\\Hooks\\DrawItem'] = array(
    'className' => 'Ka\\GridelementsFfpagepreview\\Xclass\\DrawItem',
);
