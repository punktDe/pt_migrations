<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Ivan Riera Sanchez <riera-sanchez@punkt.de>, punkt.de GmbH
 *
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/**
 *  Register the Backend Modules for this Extension
 */

if (TYPO3_MODE === 'BE') {
    // Register the Pt Migrations tool
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'PunktDe.' . $_EXTKEY,
        'tools', // Make module a submodule of 'tools'
        'migrations', // Submodule key
        '', // Position
        array( // An array holding the controller-action-combinations that are accessible
            'Migrations' => 'index,runMigration'
        ),
        array(
            'access' => 'user,group',
            'icon' => 'EXT:pt_migrations/ext_icon.svg',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod_installation.xml',
        )
    );
}
