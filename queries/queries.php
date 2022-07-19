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
 * Queries used in functions are defined here.
 *
 * @package     report_saedashboard
 * @copyright   2022 Yergen <samuel.yergen@hotmail.ch>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class queries{

    // Query used to get resources/activities
    // See resources.php for more details
    public static function query_resources_activities($idcourse, $idresource, $tableresource, $begindate, $enddate){
        return "SELECT COUNT(DISTINCT(l.id))
                FROM mdl_logstore_saelog l, mdl_user u, mdl_role r, mdl_role_assignments ra
                WHERE l.userid = u.id
                AND u.id = ra.userid
                AND ra.roleid = r.id 
                AND l.courseid = $idcourse
                AND l.objectid = $idresource
                AND l.action = 'viewed'
                AND l.objecttable = '$tableresource'
                AND l.timecreated BETWEEN $begindate AND $enddate
                AND r.shortname = 'student'
";
    }


    // Query used to get course accesses
    // See courseaccesses.php for more details
    public static function query_course_accesses($idcourse, $begindate, $enddate){
        return "SELECT COUNT(DISTINCT(l.id))
            FROM mdl_logstore_saelog l, mdl_user u, mdl_role r, mdl_role_assignments ra
            WHERE l.userid = u.id
            AND u.id = ra.userid
            AND ra.roleid = r.id
            AND l.action = 'viewed'
            AND l.target = 'course'
            AND l.courseid = $idcourse
            AND l.timecreated BETWEEN  $begindate AND $enddate
            AND r.shortname = 'student'
";
    }

    // Query used to get course name
    // See courseaccesses.php for more details
    public static function query_course_name($idcourse){
        return "SELECT id, fullname
                   FROM mdl_course
                   WHERE id = $idcourse";
    }

    // Query used to get resources names
    // See resources.php for more details
    public static function query_resource_name($idcourse, $table){
        return "SELECT id, name, timemodified
                FROM mdl_$table                     
                WHERE course = $idcourse
";
    }

    // Query used to get course creation date
    // See courseaccesses.php for more details
    public static function query_date_creation($idcourse){
        return "SELECT timecreated
                    FROM mdl_course
                    WHERE id = $idcourse";
    }
}