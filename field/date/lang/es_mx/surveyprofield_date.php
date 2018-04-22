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
 * Strings for component 'field_date', language 'es_mx'
 *
 * @package    surveyprofield_date
 * @subpackage date
 * @copyright  2013 onwards German Valero <gvalero@unam.mx >
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['currentdatedefault'] = 'Fecha actual';
$string['customdefault'] = 'Personalizado';
$string['defaultoption'] = 'Por defecto';
$string['defaultoption_help'] = 'Esta es la fecha que el usuario remoto encontrará contestada por defecto. El valor por defecto para este tipo de pregunta es obligatorio. Si se elige "Fecha actual" como valor por defecto, los límites no se supone que apliquen.';
$string['downloadformat'] = 'Formato de descarga';
$string['downloadformat_help'] = 'Elija el formato de la contestación como aparece una vez que sean descargados los intentos de los usuarios';
$string['ierr_lowerequaltoupper'] = 'Los límites inferior y superior deben de ser diferentes';
$string['ierr_lowergreaterthanupper'] = 'El límite inferior debe de ser menor que el límite superior';
$string['ierr_outofrangedefault'] = 'El valor por defecto no cae dentro del rango especificado';
$string['inviteday'] = 'Elegir un día';
$string['invitemonth'] = 'Elegir un mes';
$string['inviteyear'] = 'Elegir un año';
$string['lowerbound'] = 'Límite inferior';
$string['lowerbound_help'] = 'La menor fecha que se le permite ingresar a un usuario';
$string['pluginname'] = 'Fecha';
$string['restriction_lower'] = 'La contestación se supone que es mayor o igual que {$a}';
$string['restriction_lowerupper'] = 'La contestación se supone que está entre {$a->lowerbound} y {$a->upperbound}';
$string['restriction_upper'] = 'La contestación se supone que es menor o igual que {$a}';
$string['strftime01'] = '%A, %d %B %Y';
$string['strftime02'] = '%A, %d %B \'%y';
$string['strftime03'] = '%a, %d %b %Y';
$string['strftime04'] = '%a, %d %b \'%y';
$string['strftime05'] = '%d %B %Y';
$string['strftime06'] = '%d %B \'%y';
$string['strftime07'] = '%d %b %Y';
$string['strftime08'] = '%d %b \'%y';
$string['strftime09'] = '%d %b \'%y';
$string['strftime10'] = '%d/%m/%y';
$string['uerr_datenotset'] = 'Por favor elija una fecha o seleccione la casilla de "{$a}"';
$string['uerr_datenotsetrequired'] = 'La fecha no está definida correctamente';
$string['uerr_greaterthanmaximum'] = 'El valor proporcionado es mayor que el máximo permitido';
$string['uerr_lowerthanminimum'] = 'El valor proporcionado es menor que el mínimo permitido';
$string['uerr_outofinternalrange'] = 'El valor proporcionado no cae dentro del rango especificado';
$string['upperbound'] = 'Límite superior';
$string['upperbound_help'] = 'La fecha más grande que se le permite ingresar al usuario';
$string['userfriendlypluginname'] = 'Fecha [dd/mm/aaaa]';
