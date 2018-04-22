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
 * Strings for component 'surveyprofield_recurrence', language 'es_mx'
 *
 * @package   surveyprofield_recurrence
 * @subpackage recurrence
 * @copyright  2013 onwards German Valero <gvalero@unam.mx >
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['currentrecurrencedefault'] = 'Fecha actual';
$string['customdefault'] = 'Personalizado';
$string['defaultoption'] = 'Por defecto';
$string['defaultoption_help'] = 'Esta es la recurrencia que el usuario remoto encontrará contestada por defecto. El valor por defecto para este tipo de pregunta es obligatorio. Si se elige "Recurrencia actual" como valor por defecto, los límites no se supone que apliquen.';
$string['downloadformat'] = 'Formato de descarga';
$string['downloadformat_help'] = 'Elija el formato de la contestación como aparece una vez que sean descargados los intentos de los usuarios';
$string['ierr_lowerequaltoupper'] = 'Los límites inferior y superior deben de ser diferentes';
$string['ierr_outofexternalrangedefault'] = 'El valor por defecto no cae dentro del rango especificado.  (vea ayuda "{$a}" )';
$string['ierr_outofrangedefault'] = 'El valor por defecto no cae dentro del rango especificado';
$string['inviteday'] = 'Elegir un día';
$string['invitemonth'] = 'Elegir un mes';
$string['inviteyear'] = 'Elegir un año';
$string['lowerbound'] = 'Límite inferior';
$string['lowerbound_help'] = 'La menor recurrencia que tiene permitida ingresar el usuario';
$string['pluginname'] = 'Recurrencia';
$string['restriction_lower'] = 'La contestación se supone que es mayor o igual a {$a}';
$string['restriction_lowerupper'] = 'La contestación se supone que encaje entre {$a->lowerbound} y {$a->upperbound}';
$string['restriction_upper'] = 'La contestaciónse supone que es menor o igual a {$a}';
$string['restriction_upperlower'] = 'Se supone que la contestación sea menor o igual a {$a->lowerbound} o mayor o igual a {$a->upperbound}';
$string['strftime01'] = '%d %B';
$string['strftime02'] = '%d %b';
$string['strftime03'] = '%d/%m';
$string['uerr_incorrectrecurrence'] = 'El valor proporcionado no existe';
$string['uerr_outofexternalrange'] = 'Se supone que el valor proporcionado sea menor o igual a{$a->lowerbound} o mayor o igual a {$a->upperbound}';
$string['uerr_outofinternalrange'] = 'El valor proporcionado no cae dentro del rango especificado';
$string['uerr_recurrencenotset'] = 'Por favor elija una recurrencia o elija la casilla de "{$a}"';
$string['uerr_recurrencenotsetrequired'] = 'La recurrencia no está definida correctamente';
$string['upperbound'] = 'Límite superior';
$string['upperbound_help'] = 'La mayor recurrencia que tiene permitido ingresar el usuario.<br /><br />Los valores máximo y mínimo definen un rango.<br />Si el "valor mínimo" es menor que el "valor máximo" el usuario es forzado a ingresar un valor que caiga dentro del rango.<br />Si el "valor mínimo" es mayor que "valor máximo" el ingreso del usuario es forzado afuera del rango. En ejemplo, el ingreso del usuario se supone que sea menor o igual al valor mínimo O que sea mayor o igual al valor máximo.';
$string['userfriendlypluginname'] = 'Recurrencia [dd/mm]';
