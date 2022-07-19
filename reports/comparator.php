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


class comparator{

    // Get the accesses per day to two resources
    public static function compare_resources($select1, $select2, $day, $ararray, $idcourse){
        global $DB;
        $idday = $day;
        $idday-=1 ;

        $comparekey1 = array_search($select1, array_column($ararray, 'name'));
        $comparekey2 = array_search($select2, array_column($ararray, 'name'));

        $compareobject1 = new stdClass();
        $compareobject1->table = $ararray[$comparekey1]->table ;
        $compareobject1->id = $ararray[$comparekey1]->id ;
        $compareobject2 = new stdClass();
        $compareobject2->table = $ararray[$comparekey2]->table ;
        $compareobject2->id = $ararray[$comparekey2]->id ;

        $comparearray[0] = $compareobject1 ;
        $comparearray[1] = $compareobject2 ;

        $resource1 = [];
        $resource2 = [];
        $countcomparator = [];
        $times = weekdays::get_hour_day();
        $wd = weekdays::get_week_days();
        $tempcomparator = 0 ;
        for($i=0;$i<count($comparearray);$i++){
            foreach($times as $v){
                $unixbeginning = strtotime($wd[$idday].' '.$v);
                $unixend = strtotime($wd[$idday].' '.$v. ' +1 hour');
                $tableneeded = $comparearray[$i]->table;
                $idobject = $comparearray[$i]->id;
                $querycount = queries::query_resources_activities($idcourse, $idobject, $tableneeded, $unixbeginning, $unixend);
                $countcomparator[$tempcomparator] = $DB->count_records_sql($querycount, null) ;
                $tempcomparator++ ;
            }
        }

        $temptest = 0 ;
        for($i=0;$i<count($countcomparator);$i++){
            if($i <= 23){
                $resource1[$i]=$countcomparator[$i];
            }else{
                $resource2[$temptest]=$countcomparator[$i];
                $temptest++;
            }
        }
        return [$resource1, $resource2];
    }
}
