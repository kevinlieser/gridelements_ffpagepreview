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

class DrawItem extends \GridElementsTeam\Gridelements\Hooks\DrawItem {
	
	var $imageWidth = '120';
	var $imageHeight = '100';
	
	/**
     * Renders the grid layout table after the HTML content for the single elements has been rendered
     *
     * @param array $layoutSetup : The setup of the layout that is selected for the grid we are going to render
     * @param array $row : The current data row for the container item
     * @param array $head : The data for the column headers of the grid we are going to render
     * @param array $gridContent : The content data of the grid we are going to render
     *
     * @return string
     */
    public function renderGridLayoutTable($layoutSetup, $row, $head, $gridContent)
    {
		
		$grid = parent::renderGridLayoutTable($layoutSetup, $row, $head, $gridContent);
		
		// Read TypoScript Settings
		$this->readSettings($row['pid']);
		
		// Init Fluid Template
		$templatePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:gridelements_ffpagepreview/Resources/Private/Templates/Table.html');
		$view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename($templatePath);
		
		// Generate Content
		if(!empty($row['pi_flexform']) && !empty($layoutSetup['pi_flexform_ds'])) {
			$parseXMLValues = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($row['pi_flexform']);
			if($parseXMLValues['data']['sDEF']['lDEF']) {
				$parseXMLValues = $parseXMLValues['data']['sDEF']['lDEF'];
				$parseXMLLayout = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($layoutSetup['pi_flexform_ds']);
				if($parseXMLLayout['ROOT']['el']) {
					$parseXMLLayout = $parseXMLLayout['ROOT']['el'];
					$previewActive = 0;
					$dataArray = array();
					$dataArrayImages = array();
					foreach($parseXMLValues as $key => $value) {
						if($parseXMLLayout[$key]['TCEforms']['label'] && !empty($value['vDEF'])) {
							$previewActive = 1;
							array_push($dataArray, array('layout' => $parseXMLLayout[$key], 'value' => $value));
							/*
							if($parseXMLLayout[$key]['TCEforms']['config']['internal_type'] == 'file') {
								array_push($dataArrayImages, array('layout' => $parseXMLLayout[$key], 'value' => $value));
							} else {
								array_push($dataArray, array('layout' => $parseXMLLayout[$key], 'value' => $value));
							}
							*/
						}
					}
					if($previewActive == 1) {
						$view->assign('data', $dataArray);
						$view->assign('dataImages', $dataArrayImages);
						$view->assign('imageWidth', $this->imageWidth);
						$view->assign('imageHeight', $this->imageHeight);
						$grid .= $view->render();
					}
				}
			}
		}
		
		return $grid;
		
	}
	
	private function readSettings($pid) {
		$typoscript = $this->loadTS($pid);
		if(isset($typoscript['plugin.']['tx_gridelementsffpagepreview.'])) {
			$settings = $typoscript['plugin.']['tx_gridelementsffpagepreview.'];
			if(isset($settings['settings.']['imageWidth'])) { $this->imageWidth = $settings['settings.']['imageWidth']; }
			if(isset($settings['settings.']['imageHeight'])) { $this->imageHeight = $settings['settings.']['imageHeight']; }
		}
	}
	
	public static function loadTS($pageUid=NULL) {
		$pageUid = ($pageUid && \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($pageUid)) ? $pageUid : \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');
		$sysPageObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$TSObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($sysPageObj->getRootLine($pageUid));
		$TSObj->generateConfig();
		return $TSObj->setup;
	}
	
	
}