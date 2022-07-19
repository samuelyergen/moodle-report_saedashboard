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

require_once(__DIR__.'/weekdays.php');
require_once('queries/queries.php');

class courseaccesses{

    // Return the name of a course
    public static function get_course_name($idcourse){
        global $DB;
        $getcoursename = queries::query_course_name($idcourse);
        return $DB->get_records_sql($getcoursename, null);
    }

    // Return the creation date of a course
    public static function get_creation_date($idcourse){
        global $DB ;
        $getdatecreation = queries::query_date_creation($idcourse);
        $resultdatecreation = $DB->get_records_sql($getdatecreation, null);
        $datecreation = null ;
        $unixdatecreation = null ;
        foreach($resultdatecreation as $key=>$value){
            $datecreation = date('Y-m-d', $value->timecreated );
            $unixdatecreation = $value->timecreated;
        }
        return [$datecreation, $unixdatecreation];
}

    // Return the number of week between the creation of the course and today
    public static function get_week_from_creation($date){
        $today = date('Y-m-d');
        //Create a DateTime object for the first date.
        $firstDate = new DateTime($date);
        //Create a DateTime object for the second date.
        $secondDate = new DateTime($today);
        //Get the difference between the two dates in days.
        $differenceInDays = $firstDate->diff($secondDate)->days;
        //Divide the days by 7
        $differenceInWeeks = $differenceInDays / 7;
        //Round down with floor and return the difference in weeks.
        return floor($differenceInWeeks);
    }

    // Count the accesses of a course for the week
    public static function get_weekly_accesses($idcourse){
        global $DB ;
        $days = weekdays::get_week_days();
        $accessarray = [];
        foreach ($days as $key => $value){
            $unixdatetoday = strtotime($value);
            $unixdatetomorrow = strtotime($value . ' +1 day');
            $queryweeks = queries::query_course_accesses($idcourse, $unixdatetoday, $unixdatetomorrow);
            $accessarray[$key] = $DB->count_records_sql($queryweeks, $params = null) ;
        }
        return $accessarray ;
    }

    // Compute the mean accesses of the semsester and the mean during exam period
    public static function get_means($idcourse, $unixdate, $weekdiff){
        global $DB ;
        $thisday = date('Y-m-d');
        $currentyear = date("Y");
        $semesterbreak = '01-02-'.$currentyear ;
        if($thisday > $semesterbreak){
            $examdatebeginning = strtotime('20-06-'.$currentyear) ;
            $examdateend = strtotime('05-07-'.$currentyear) ;
            $querymeanexam = queries::query_course_accesses($idcourse, $examdatebeginning, $examdateend);
            $querymeansemester =  queries::query_course_accesses($idcourse, $unixdate, $examdatebeginning);
        }else{
            $examdatebeginning = strtotime('10-01-'.$currentyear) ;
            $examdateend = strtotime('24-01-'.$currentyear) ;
            $querymeanexam = queries::query_course_accesses($idcourse, $examdatebeginning, $examdateend);
            $querymeansemester =  queries::query_course_accesses($idcourse, $unixdate, $examdatebeginning);
        }
        $accessexam = $DB->count_records_sql($querymeanexam, null) ;
        $meanaccessexam = $accessexam/2 ;
        $resultcountaccesssemester = $DB->count_records_sql($querymeansemester, null) ;
        if($weekdiff > 0){
            $accessmean = ($resultcountaccesssemester / $weekdiff) ;
        }else{
            $accessmean = $resultcountaccesssemester;
        }

        return [$accessmean, $meanaccessexam];
    }
}

