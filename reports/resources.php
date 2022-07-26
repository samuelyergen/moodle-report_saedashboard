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
 * Functions for resources charts are defined here.
 *
 * @package     report_saedashboard
 * @copyright   2022 Yergen <samuel.yergen@hotmail.ch>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/weekdays.php');
require_once('queries/queries.php');

// This class is also used to work with the activities of a course
// See index.php for more details
class resources{

    // Use a query to get the name of every resource of a course
    public static function get_resources_names($idcourse, $tablearray){
        global $DB ;
        $resourcessearch = [];
        $tempnamessearch = 0 ;
        foreach ($tablearray as $value){
            $getresources = queries::query_resource_name($idcourse, $value);
            $resourcessearch[$tempnamessearch] = $DB->get_records_sql($getresources, null);
            $tempnamessearch++;
        }
        return $resourcessearch ;
    }

    // Construct an array of object with info like id, name and type (table) of a resource
    public static function construct_object_with_type($namesarray, $tablearray){
        $tempobjectarray = 0 ;
        $resourcesobjectarray = [];
        foreach ($namesarray as $key=>$value){
            foreach($value as $k=>$v){
                $resourcesnamesobject = new stdClass();
                $resourcesnamesobject->id = $k ;
                $resourcesnamesobject->name = $v->name ;
                $resourcesnamesobject->table = $tablearray[$key] ;
                $resourcesobjectarray[$tempobjectarray] = $resourcesnamesobject ;
                $tempobjectarray++;
            }
        }
        return $resourcesobjectarray ;
    }

    // Construct an array with every resource's name of a course
    // The array is used to build chart
    public static function construct_labels($namesarray){
        $templabelarray = 0 ;
        $resourcesnamesforchart = [];
        foreach ($namesarray as $value){
            foreach($value as $v){
                $resourcesnamesforchart[$templabelarray] = $v->name ;
                $templabelarray++;
            }
        }
        return $resourcesnamesforchart ;
    }

    // Construct an array of objet and add the count of accesses of resources
    public static function construct_count_array($objectarray, $idcourse, $beginsemester, $endsemester){
        global $DB ;
        $countarray  = [];
        for($i=0; $i<count($objectarray);$i++){
            $idresource = $objectarray[$i]->id ;
            $tableresource = $objectarray[$i]->table ;
            $countsemesterresources = queries::query_resources_activities($idcourse, $idresource, $tableresource, $beginsemester, $endsemester);
            $countsemesterresourcearray[$i] = $DB->count_records_sql($countsemesterresources, null) ;
            // Construct object with id, name and related table of the resource/activity
            $resourcewithcount = $objectarray[$i];
            $resourcewithcount->count = $countsemesterresourcearray[$i];
            // Construct an array with every resource object of the course
            $countarray[$i] = $resourcewithcount ; //tempcr
        }
        return $countarray ;
    }

    // Count accesses during semester for each resource
    public static function get_semester_count($objectarray, $idcourse){
        $thisday = date('Y-m-d');
        $currentyear = date("Y");
        $semesterbreak = '01-02-'.$currentyear ;
        if($thisday > $semesterbreak){
            $beginsemester = strtotime('02-02-'.$currentyear );
            $endsemester = strtotime('15-09-'.$currentyear );
            $resourcewithcountarray = self::construct_count_array($objectarray, $idcourse, $beginsemester, $endsemester);
        }else {
            $beginsemester = strtotime('01-09-' . $currentyear);
            $endsemester = strtotime('31-01-' . $currentyear);
            $resourcewithcountarray = self::construct_count_array($objectarray, $idcourse, $beginsemester, $endsemester);
        }
        return $resourcewithcountarray;
    }

    // Construct an array of values to build the chart
    public static function construct_serie($seriearray){
        $serieforchart = [];
        for($i=0;$i<count($seriearray);$i++){
            $serieforchart[$i] = $seriearray[$i]->count ;
        }
        return $serieforchart ;
    }

    // Count accesses during the current week for each resource
    public static function get_weekly_count($objectarray, $idcourse){
        global $DB ;
        $countweekresourcearray = [];
        for($i=0; $i<count($objectarray);$i++){
            $wdays = weekdays::get_week_days();
            $unixdatemondayr = strtotime($wdays[0]);
            $unixdatesundayr = strtotime($wdays[count($wdays)-1].' 23:59:59');
            $idresource = $objectarray[$i]->id ;
            $tableresource = $objectarray[$i]->table ;
            $countweekresources = queries::query_resources_activities($idcourse, $idresource, $tableresource, $unixdatemondayr, $unixdatesundayr);
            $countweekresourcearray[$i] = $DB->count_records_sql($countweekresources, null) ;
        }
        return $countweekresourcearray ;
    }

    // Sort the array and give the top 3 accessed resources
    public static function get_top3($arraywithcount){
        $toparray = [];
        usort($arraywithcount, 'weekdays::comparatorresource');
        if(count($arraywithcount) >= 1){
            $firstresource = new stdClass() ;
            $firstresource->name = $arraywithcount[count($arraywithcount)-1]->name;
            $firstresource->count = $arraywithcount[count($arraywithcount)-1]->count;
            $toparray[0] = $firstresource ;
        }
        if(count($arraywithcount) >= 2){
            $secondresource = new stdClass();
            $secondresource->name = $arraywithcount[count($arraywithcount)-2]->name;
            $secondresource->count = $arraywithcount[count($arraywithcount)-2]->count;
            $toparray[1] = $secondresource ;
        }
        if(count($arraywithcount) >= 3){
            $thirdresource = new stdClass() ;
            $thirdresource->name = $arraywithcount[count($arraywithcount)-3]->name;
            $thirdresource->count = $arraywithcount[count($arraywithcount)-3]->count;
            $toparray[2] = $thirdresource ;
        }
        return $toparray;
    }

    // Get the latest modified resource
    public static function get_last_modified($namesarray)
    {
        $latestresourcemodified = [];
        foreach ($namesarray as $value) {
            foreach ($value as $v) {
                if (count($latestresourcemodified) == 0) {
                    $latestresourcemodified[0] = $v->name;
                    $latestresourcemodified[1] = $v->timemodified;
                }

                if ($v->timemodified > $latestresourcemodified[1]) {
                    $latestresourcemodified[0] = $v->name;
                    $latestresourcemodified[1] = $v->timemodified;
                }
            }
        }
        return $latestresourcemodified ;

    }

}