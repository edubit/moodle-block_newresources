<?php
/**
 * Version details
 *
 * @package    block
 * @subpackage newresources
 * @copyright  2013 Edubit.com.br
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2013120402;  // YYYYMMDDHH (year, month, day, 24-hr time)
$plugin->requires = 2013111800; // YYYYMMDDHH (This is the release version for Moodle 2.0)
$plugin->component = 'block_newresources'; // Full name of the plugin (used for diagnostics)
$plugin->cron = 1; // Set min time between cron executions to 5 minutes