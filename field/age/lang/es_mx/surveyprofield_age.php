<?php
// This file is part of Moodle - http://moodle.org/
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
 * Strings for component 'surveyprofield_age', language 'es_mx'
 *
 * @package    surveyprofield_age
 * @subpackage age
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['and'] = 'y';
$string['customdefault'] = 'Personalizado';
$string['defaultoption'] = 'Por defecto';
$string['defaultoption_help'] = 'Esta es la edad que el usuario remoto encontrará contestada por defecto. El valor por defecto para este tipo de pregunta es obligatorio.';
$string['ierr_lowerequaltoupper'] = 'Los límites inferior y superior deben de ser diferentes';
$string['ierr_lowergreaterthanupper'] = 'El límite inferior debe de ser menor que el límite superior';
$string['ierr_outofrangedefault'] = 'El valor por defecto no cae dentro del rango especificado';
$string['invitemonth'] = 'Elegir un mes';
$string['inviteyear'] = 'Elegir un año';
$string['lowerbound'] = 'Límite inferior';
$string['lowerbound_help'] = 'La menor edad que el usuario tiene ermitido ingresar';
$string['maximumage'] = 'Edad máxima';
$string['maximumage_desc'] = 'La edad máxima que este software permitiría ingresar';
$string['months'] = 'meses';
$string['pluginname'] = 'Edad';
$string['restriction_lower'] = 'La contestación se supone que es mayor de {$a}';
$string['restriction_lowerupper'] = 'La contestación se supone que está entre {$a->lowerbound} y {$a->upperbound}';
$string['restriction_upper'] = 'La contestación se supone que es menor o igual a {$a}';
$string['uerr_agenotset'] = 'Por favor elija una edad o seleccione la casilla "{$a}"';
$string['uerr_agenotsetrequired'] = 'La edad no está definida correctamente';
$string['uerr_greaterthanmaximum'] = 'El valor proporcionado es mayor que el máximo permitido';
$string['uerr_lowerthanminimum'] = 'El valor proporcionado es menor que el máximo permitido';
$string['uerr_outofinternalrange'] = 'El valor proporcionado no cae dentro del rango especificado';
$string['upperbound'] = 'Límite superior';
$string['upperbound_help'] = 'La mayor edad que el usuario tiene ermitido ingresar';
$string['userfriendlypluginname'] = 'Edad [aa/mm]';
