<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle. If not, see <https://www.gnu.org/licenses/>;.

/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     report_saedashboard
 * @copyright   2022 Yergen <samuel.yergen@hotmail.ch>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class weekdays {

    // Return every day date of a week
    public static function get_week_days(){
        $myDate = date("d-m-Y");
        $weekdays[0] = date("d-m-Y", strtotime('monday this week', strtotime($myDate)))."\n";
        $weekdays[1] = date("d-m-Y", strtotime('tuesday this week', strtotime($myDate)))."\n";
        $weekdays[2] = date("d-m-Y", strtotime('wednesday this week', strtotime($myDate)))."\n";
        $weekdays[3] = date("d-m-Y", strtotime('thursday this week', strtotime($myDate)))."\n";
        $weekdays[4] = date("d-m-Y", strtotime('friday this week', strtotime($myDate)))."\n";
        $weekdays[5] = date("d-m-Y", strtotime('saturday this week', strtotime($myDate)))."\n";
        $weekdays[6] = date("d-m-Y", strtotime('sunday this week', strtotime($myDate)))."\n";
        return $weekdays ;
    }

    // Return every hour of a day
    public static function get_hour_day(){
        $lower = 0;
        $upper = 23;
        $step = 1;
        $format = NULL ;

        if ($format === NULL) {
            $format = 'g:ia'; // 9:30pm
        }
        $times = array();
        $temptime = 0;
        foreach(range($lower, $upper, $step) as $increment) {
            $increment = number_format($increment, 1);
            list($hour, $minutes) = explode('.', $increment);
            $date = new DateTime($hour . ':' . $minutes * .6);
            $times[$temptime] = $date->format($format);
            $temptime++;
        }
        return $times ;
    }

    // Use to sort an array
    // See resources.php for more details
    public static function comparatorresource($object1, $object2) {
            return $object1->count > $object2->count;
        }
}

