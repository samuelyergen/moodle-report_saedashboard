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


require(__DIR__.'/../../config.php');
require_once(__DIR__.'/reports/resources.php');
require_once(__DIR__.'/reports/courseaccesses.php');
require_once(__DIR__.'/reports/comparator.php');
require_once(__DIR__.'/reports/weekdays.php');



//////////////////////// PAGE CONFIGURATION
global $PAGE ;
global $OUTPUT ;
$context = context_system::instance();
$courseid = optional_param('course', null, PARAM_INT);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/report/saedashboard/index.php', array('course' => $courseid)));
$PAGE->set_heading('SAE Dashboard');
//$PAGE->set_pagelayout('standard');
echo $OUTPUT->header();


//// RESOURCES
$resourcestables = ['folder', 'resource', 'page', 'url', 'book'];
$resources = resources::get_resources_names($courseid, $resourcestables);
$resourcesobject = resources::construct_object_with_type($resources, $resourcestables);
$nbresources = count($resourcesobject);
$resourceslabels = resources::construct_labels($resources);
$resourcescountsemester = resources::get_semester_count($resourcesobject, $courseid);
$semesterserie = resources::construct_serie($resourcescountsemester);
$resourcescountweek = resources::get_weekly_count($resourcesobject, $courseid);
$week = weekdays::get_week_days();
$hours = weekdays::get_hour_day();
$top3resources = resources::get_top3($resourcescountsemester);
$lastresourcemodified = resources::get_last_modified($resources);

// ACTIVITIES
$actvttables = ["chat", "assign", "survey", "feedback", "forum", "glossary", "workshop","lti","choice","quiz"];
$activities = resources::get_resources_names($courseid, $actvttables);
$actvtobject = resources::construct_object_with_type($activities, $actvttables);
$nbactivities = count($actvtobject);
$actvtlabels = resources::construct_labels($activities);
$actvtcountsemester = resources::get_semester_count($actvtobject, $courseid);
$semesteractvtserie = resources::construct_serie($actvtcountsemester);
$actvtcountweek = resources::get_weekly_count($actvtobject, $courseid);
$top3actvt = resources::get_top3($actvtcountsemester);
$lastactvtmodified = resources::get_last_modified($activities);

// Construct an array with every activities and resources of a course
$resourcesandactvt = [];
$counterwithresource = count($resourcesobject);
for($i=0;$i<count($resourcesobject);$i++){
    $resourcesandactvt[$i] = $resourcesobject[$i];
}

for($i=0;$i<count($actvtobject);$i++){
    $resourcesandactvt[$i+$counterwithresource] = $actvtobject[$i];
}

// COURSE ACCESSES
$coursename = courseaccesses::get_course_name($courseid);
$creationdates = courseaccesses::get_creation_date($courseid);
$weeknb = courseaccesses::get_week_from_creation($creationdates[0]);
$weekaccess = courseaccesses::get_weekly_accesses($courseid);
$means = courseaccesses::get_means($courseid, $creationdates[1], $weeknb);


///////////// CHART DECLARATION
$semesterresources = new \core\chart_series('nombre d accès aux ressources',  $semesterserie);
$semesterresourceschart = new \core\chart_line();
$semesterresourceschart->set_title('Accès aux ressources semestriel');
$semesterresourceschart->add_series($semesterresources);
$semesterresourceschart->set_labels($resourceslabels);

$rw = new \core\chart_series('nombre d accès aux ressources', $resourcescountweek);
$weekresourceschart = new \core\chart_line();
$weekresourceschart->set_title('Accès aux ressources '.$week[0].' au '.$week[6]);
$weekresourceschart->add_series($rw);
$weekresourceschart->set_labels($resourceslabels);

$semesteractvt = new \core\chart_series('nombre d accès aux activités', $semesteractvtserie);
$semesteractivitychart = new \core\chart_line();
$semesteractivitychart->set_title('Accès aux activités semestre');
$semesteractivitychart->add_series($semesteractvt);
$semesteractivitychart->set_labels($actvtlabels);

$weekactvt = new \core\chart_series('nombre d accès aux activités', $actvtcountweek);
$weekactivitychart = new \core\chart_line();
$weekactivitychart->set_title('Accès aux activités '.$week[0].' au '.$week[6]);
$weekactivitychart->add_series($weekactvt);
$weekactivitychart->set_labels($actvtlabels);

$accesscoursemean = new \core\chart_series('moyenne des accès par semaine', [$means[0]]);
$accessduringexammean = new \core\chart_series('moyenne des accès en période d examen', [$means[1]]);
$courseaccessmeanchart = new \core\chart_bar();
$courseaccessmeanchart->set_title('access');
$courseaccessmeanchart->add_series($accesscoursemean);
$courseaccessmeanchart->add_series($accessduringexammean);

$accessfinal = new \core\chart_series('nombre d accès au cours', $weekaccess);
$courseaccesschart = new \core\chart_line();
$courseaccesschart->set_title('Accès au cours '.$week[0].' au '.$week[6]);
$courseaccesschart->add_series($accessfinal);
$courseaccesschart->set_labels($week);

/////// RENDER
echo '</br>';
echo '</br>';
echo '</br>';
echo '<div style="display: flex ; width: 100%">';
echo '<div style="width: 100%;">'.'Cours : '. $coursename[$courseid]->fullname .'</div>';
echo '<div style="width: 100%;">'.'Nombre de ressources disponibles : '. $nbresources .'</div>';
echo '<div style="width: 100%;">'.'Nombre d activités disponibles : '. $nbactivities .'</div>';
echo '<div style="width: 100%;">'.'Dernière resource créée ou modifiée : '. $lastresourcemodified[0] .'</div>';
echo '<div style="width: 100%;">'.'Dernière activité créée ou modifiée : '. $lastactvtmodified[0] .'</div>';
echo '</div>';
echo '</br>';

echo '<div style="display: flex ; width: 100% ; justify-content: center;">';
echo '<div style="padding-right: 100px; padding-top: 50px">';
echo '<h5>'.'Top 3 accès aux ressources'.'</h5>';
echo '<table>';
echo'<tr>';
echo '<td>'.'1'.'</td>';
if(count($top3resources) >= 1){
    echo '<td style="padding-right: 20px; padding-left: 20px">'.$top3resources[0]->name.'</td>';
    echo '<td>'.$top3resources[0]->count.' accès'.'</td>';
}
echo'</tr>';
echo'<tr>';
echo '<td>'.'2'.'</td>';
if(count($top3resources) >= 2){
    echo '<td style="padding-right: 20px; padding-left: 20px">'.$top3resources[1]->name.'</td>';
    echo '<td>'.$top3resources[1]->count.' accès'.'</td>';
}

echo'</tr>';
echo'<tr>';
echo '<td>'.'3'.'</td>';
if(count($top3resources) >= 3){
    echo '<td style="padding-right: 20px; padding-left: 20px">'.$top3resources[2]->name.'</td>';
    echo '<td>'.$top3resources[2]->count.' accès'.'</td>';
}
echo'</tr>';
echo '</table>';
echo '</div>';


echo '<div style="padding-left: 100px; padding-top: 50px">';
echo '<h5>'.'Top 3 accès aux activités'.'</h5>';
echo '<table>';
echo'<tr>';
echo '<td>'.'1'.'</td>';
if(count($top3actvt) >= 1){
    echo '<td style="padding-right: 20px; padding-left: 20px">'.$top3actvt[0]->name.'</td>' ;
    echo '<td>'.$top3actvt[0]->count.' accès'.'</td>' ;
}
echo'</tr>';
echo'<tr>';
echo '<td>'.'2'.'</td>';
if(count($top3actvt) >= 2){
    echo '<td style="padding-right: 20px; padding-left: 20px">'.$top3actvt[1]->name.'</td>';
    echo '<td>'.$top3actvt[1]->count.' accès'.'</td>' ;
}
echo'</tr>';
echo'<tr>';
echo '<td>'.'3'.'</td>';
if(count($top3actvt) >= 3){
    echo '<td style="padding-right: 20px; padding-left: 20px">'.$top3actvt[2]->name.'</td>';
    echo '<td>'.$top3actvt[2]->count.' accès'.'</td>' ;
}
echo'</tr>';
echo '</table>';
echo '</div>';
echo '</div>';
echo '</br>';
echo '</br>';


echo '</br>';
echo '</br>';
echo '<h4>'.'Accès aux ressources'.'</h4>';
echo '<div style="display: flex ; width: 100%">';
echo '<div style="width: 100%;">'.$OUTPUT->render($weekresourceschart).'</div>';
echo '<div style="width: 100%;">'.$OUTPUT->render($semesterresourceschart).'</div>';
echo '</div>';

echo '</br>';
echo '</br>';
echo '<h4>'.'Accès aux activités'.'</h4>';
echo '<div style="display: flex ; width: 100%">';
echo '<div style="width: 100%;">'.$OUTPUT->render($weekactivitychart).'</div>';
echo '<div style="width: 100%;">'.$OUTPUT->render($semesteractivitychart).'</div>';
echo '</div>';
echo '</br>';
echo '</br>';

echo '</br>';
echo '</br>';
echo '<h4>'.'Accès au cours'.'</h4>';
echo '<div style="display: flex ; width: 100%">';
echo '<div style="width: 100%;">'.$OUTPUT->render($courseaccesschart).'</div>';
echo '<div style="width: 100%;">'.$OUTPUT->render($courseaccessmeanchart).'</div>';
echo '</div>';
echo '</br>';
echo '</br>';


echo '<h4 style="padding-top: 80px">'.'Comparer les accès aux ressources/activités'.'</h4>';
echo '<div style="display: flex;  justify-content: center; padding-top: 80px">';
echo '<div >';
echo '<form action="index.php?course='.$courseid.' " method="post">';
echo '<select name="resource1">';
echo '<option value="">'. 'Choisissez une ressource/activité' . '</option>';
for($i=0;$i<count($resourcesandactvt);$i++){
    echo '<option value="'.$resourcesandactvt[$i]->name.'">'. $resourcesandactvt[$i]->name . '</option>';
}
echo '</select>';
echo '<select name="resource2">';
echo '<option value="">'. 'Choisissez une ressource/activité' . '</option>';
for($i=0;$i<count($resourcesandactvt);$i++){
    echo '<option value="'.$resourcesandactvt[$i]->name.'">'. $resourcesandactvt[$i]->name . '</option>';
}
echo '</select>';
echo '</select>';
echo '<select name="days">';
echo '<option value="">'. 'Choisissez un jour' . '</option>';
for($i=0;$i<count($week);$i++){
    $j = $i+1 ;
    echo '<option value="'.$j.'">'. $week[$i] . '</option>';
}
echo '</select>';
echo '<input type="submit" name="Submit"  />';
echo '</form>';
if(empty($_POST['resource1']) && empty($_POST['resource2'])  && empty($_POST['days'])){
    echo '<p style="padding-top: 80px;">'.'<b>'.'Choisissez deux ressources/activités à comparer'.'</b>'.'</p>';
}
echo '</div>';
echo '</div>';

if(!empty($_POST['resource1']) && !empty($_POST['resource2']) && !empty($_POST['days'])){

    $compare = comparator::compare_resources($_POST['resource1'],$_POST['resource2'],$_POST['days'], $resourcesandactvt, $courseid);
    $comparator1 = new \core\chart_series('accès '. $_POST['resource1'], $compare[0]);
    $comparator2 = new \core\chart_series('accès '.$_POST['resource2'], $compare[1]);
    $comparatorchart = new \core\chart_line();
    $comparatorchart->set_title('Comparaison ressource/activité');
    $comparatorchart->add_series($comparator1);
    $comparatorchart->add_series($comparator2);
    $comparatorchart->set_labels($hours);

    echo '<div style="justify-content: center">';
    echo $OUTPUT->render($comparatorchart) ;
    echo '</div>';
}

echo $OUTPUT->footer();