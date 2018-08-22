<?php

namespace Ka\GridelementsFfpagepreview\Xclass;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;

class DrawItem extends \GridElementsTeam\Gridelements\Hooks\DrawItem
{

	var $imageWidth = '120';
	var $imageHeight = '100';

	/**
	 * Renders the grid layout table after the HTML content for the single elements has been rendered
	 *
	 * @param array $layoutSetup : The setup of the layout that is selected for the grid we are going to render
	 * @param array $row : The current data row for the container item
	 * @param array $head : The data for the column headers of the grid we are going to render
	 * @param array $gridContent : The content data of the grid we are going to render
     * @param PageLayoutView $parentObject
	 *
	 * @return string
	 */
	public function renderGridLayoutTable($layoutSetup, $row, $head, $gridContent, PageLayoutView $parentObject)
	{

		$grid = parent::renderGridLayoutTable($layoutSetup, $row, $head, $gridContent, $parentObject);

		// Read TypoScript Settings
		$this->readSettings($row['pid']);

		// Init Fluid Template
		$templatePath = GeneralUtility::getFileAbsFileName('EXT:gridelements_ffpagepreview/Resources/Private/Templates/Table.html');
		$view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename($templatePath);
		$layoutSetupData = $layoutSetup['pi_flexform_ds'];

		if (substr($layoutSetupData, 0, 9) === "FILE:EXT:") {
			$layoutSetupData = str_replace('FILE:', '', $layoutSetupData);
			$layoutSetupData = GeneralUtility::getFileAbsFileName($layoutSetupData);
			$layoutSetupData = file_get_contents($layoutSetupData);
		}
		if (empty($layoutSetupData) && !empty($layoutSetup['pi_flexform_ds_file'])) {
			$layoutSetupData = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/' . $layoutSetup['pi_flexform_ds_file']);
		}

		// Generate Content
		if (!empty($row['pi_flexform']) && !empty($layoutSetupData)) {
			$parseXMLValues = GeneralUtility::xml2array($row['pi_flexform']);
			if (count($parseXMLValues['data']) != 0) {
				foreach ($parseXMLValues['data'] as $xmlPage) {
					if ($xmlPage['lDEF']) {
						$parseXMLValues = $xmlPage['lDEF'];
						$parseXMLLayout = GeneralUtility::xml2array($layoutSetupData);
						if ($parseXMLLayout['ROOT']['el']) {
							$parseXMLLayout = $parseXMLLayout['ROOT']['el'];
							$previewActive = 0;
							$dataArray = array();
							foreach ($parseXMLValues as $key => $value) {
								if ($parseXMLLayout[$key]['TCEforms']['label'] && !empty($value['vDEF'])) {
									$previewActive = 1;
									array_push($dataArray, array('layout' => $parseXMLLayout[$key], 'value' => $value));
								}
							}
							if ($previewActive == 1) {
								$view->assign('data', $dataArray);
								$view->assign('imageWidth', $this->imageWidth);
								$view->assign('imageHeight', $this->imageHeight);
								$grid .= $view->render();
							}
						} else {
							foreach ($parseXMLLayout['sheets'] as $xmlData) {
								if ($xmlData['ROOT']['el']) {
									$xmlData = $xmlData['ROOT']['el'];
									$previewActive = 0;
									$dataArray = array();
									foreach ($parseXMLValues as $key => $value) {
										if ($xmlData[$key]['TCEforms']['label'] && !empty($value['vDEF'])) {
											$previewActive = 1;
											array_push($dataArray, array('layout' => $xmlData[$key], 'value' => $value));
										}
									}
									if ($previewActive == 1) {
										$view->assign('data', $dataArray);
										$view->assign('imageWidth', $this->imageWidth);
										$view->assign('imageHeight', $this->imageHeight);
										$grid .= $view->render();
									}
								}
							}
						}
					}
				}
			}

		}

		return $grid;

	}

	private function readSettings($pid)
	{
		$typoscript = $this->loadTS($pid);
		if (isset($typoscript['plugin.']['tx_gridelementsffpagepreview.'])) {
			$settings = $typoscript['plugin.']['tx_gridelementsffpagepreview.'];
			if (isset($settings['settings.']['imageWidth'])) {
				$this->imageWidth = $settings['settings.']['imageWidth'];
			}
			if (isset($settings['settings.']['imageHeight'])) {
				$this->imageHeight = $settings['settings.']['imageHeight'];
			}
		}
	}

	public static function loadTS($pageUid = NULL)
	{
		$pageUid = ($pageUid && MathUtility::canBeInterpretedAsInteger($pageUid)) ? $pageUid : GeneralUtility::_GP('id');
		$sysPageObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$TSObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($sysPageObj->getRootLine($pageUid));
		$TSObj->generateConfig();
		return $TSObj->setup;
	}


}