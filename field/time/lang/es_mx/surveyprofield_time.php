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
 * Strings for component 'surveyprofield_time', language 'es_mx'
 *
 * @package   surveyprofield_time
 * @subpackage time
 * @copyright  2013 onwards German Valero <gvalero@unam.mx >
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['currenttimedefault'] = 'Hora actual';
$string['customdefault'] = 'Personalizado';
$string['defaultoption'] = 'Por defecto';
$string['defaultoption_help'] = 'Esta es la hora que el usuario remoto encontrará contestada por defecto. El valor por defecto para este tipo de pregunta es obligatorio. Si se elige "Hora actual" como valor por defecto, los límites no se supone que apliquen.';
$string['downloadformat'] = 'Formato de descarga';
$string['downloadformat_help'] = 'Elija el formato de la contestación como aparece una vez que sean descargados los intentos de los usuarios';
$string['fifteenminutes'] = 'quince minutos';
$string['fiveminutes'] = 'cinco minutos';
$string['ierr_lowerequaltoupper'] = 'Los límites inferior y superior deben de ser diferentes';
$string['ierr_outofexternalrangedefault'] = 'El valor por defecto no cae dentro del rango especificado  (vea ayuda "{$a}")';
$string['ierr_outofrangedefault'] = 'El valor por defecto no cae dentro del rango especificado';
$string['invitehour'] = 'Elegir una hora';
$string['inviteminute'] = 'Elegir un minuto';
$string['lowerbound'] = 'Límite inferior';
$string['lowerbound_help'] = 'El menor tiempo que el usuario tiene permitido ingresar';
$string['oneminute'] = 'Un minuto';
$string['pluginname'] = 'Tiempo';
$string['restriction_lower'] = 'La contestación se supone que es mayor o igual que {$a}';
$string['restriction_lowerupper'] = 'Se supone que la contestación esté entre {$a->lowerbound} y {$a->upperbound}';
$string['restriction_upper'] = 'Se supone que la contestación sea menor o igual a {$a}';
$string['restriction_upperlower'] = 'Se supone que la contestación sea mayor o igual a {$a->lowerbound} o menor o igual a {$a->upperbound}';
$string['step'] = 'Paso';
$string['step_help'] = 'Paso del menú desplegable de minuto tal como aparece en el formato del intento';
$string['strftime01'] = '%H:%M';
$string['strftime02'] = '%I:%M %p';
$string['tenminutes'] = 'diez minutos';
$string['thirtyminutes'] = 'treinta minutos';
$string['twentyminutes'] = 'veinte minutos';
$string['uerr_greaterthanmaximum'] = 'El valor proporcionado es mayor que {$a}';
$string['uerr_lowerthanminimum'] = 'El valor proporcionado es menor que {$a}';
$string['uerr_outofexternalrange'] = 'Se supone que el valor proporcionado sea menor o igual a {$a->lowerbound} o mayor o igual a {$a->upperbound}';
$string['uerr_outofinternalrange'] = 'El valor proporcionado no cae dentro del rango especificado';
$string['uerr_timenotset'] = 'Por favor elija un tiempo o seleccione la casilla de "{$a}"';
$string['uerr_timenotsetrequired'] = 'El tiempo no está definido correctamente';
$string['upperbound'] = 'Límite superior';
$string['upperbound_help'] = 'El mayor tiempo que se le permite ingresar al usuario.<br /><br />Los límites superior e inferior Upper and lower bound define a rangdefinen un rango.<br />Si el "límite inferior" es menor que el "límite superior" el usuario es forzado a ingresar un valor que caiga dentro del rango.<br />Si el "límite inferior" es mayor que el "límite superior" el ingreso del usuario es forzado por fuera del rango. Por ejemplo, si el ingreso del usuario se supone que sea mayor  o igual que el límite inferior O menor o igual que el límite superior.<br /><br />Ejemplo: Al configurar "límite inferior" a 22 y "límite superior" a 1, el ingreso del usuario se supone que caerá en las lapso de tres horas del rango entre las 22 de la noche y la 1 de la mañana.';
$string['userfriendlypluginname'] = 'Tiempo';
