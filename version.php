<?php
// This copybridge is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the version information for the comments feedback plugin
 *
 * @package assignfeedback_file
 * @copyright 2023 copymonitor.jp
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023061500;
$plugin->release = '1.0.4';
$plugin->requires = 2020030600;
$plugin->component = 'assignfeedback_copybridge';
$plugin->maturity = MATURITY_STABLE;
$plugin->cron = 0;

$plugin->dependencies = [
    'plagiarism_copybridge' => 2023061500
];
