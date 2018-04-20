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
 * Strings for component 'surveyprofield_numeric', language 'es_mx'
 *
 * @package   surveyprofield_numeric
 * @subpackage numeric
 * @copyright  2013 onwards German Valero <gvalero@unam.mx >
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowed'] = 'permitido';
$string['decimalautofix'] = 'los decimales excedentes o faltantes se descartarán o rellenarán con ceros';
$string['decimals'] = 'Posiciones decimales';
$string['decimals_help'] = 'El número de posiciones decimales del número solicitado';
$string['declaredecimalseparator'] = 'el separador decimal se supone que es \'{$a}\'';
$string['defaultvalue'] = 'Por defecto';
$string['defaultvalue_help'] = 'Este es el valor que el usuario remoto encontrará contestado por defecto. Vacío para dejar sin asignar el valor por defecto';
$string['ierr_default_notinteger'] = 'El valor por defecto no es un número entero';
$string['ierr_default_outofrange'] = 'El valor por defecto no cae dentro del rango especificado';
$string['ierr_defaultsignnotallowed'] = 'El valor por defecto se supone que es sin-signo';
$string['ierr_lowerequaltoupper'] = 'Los límites inferior y superior deben de ser diferentes';
$string['ierr_lowergreaterthanupper'] = 'El límite inferior debe de ser menor que el límite superior';
$string['ierr_lowernegative'] = 'El límite inferior se supone que no tenga signo';
$string['ierr_notanumber'] = 'Esto no es un número';
$string['ierr_outofrangedefault'] = 'El valor por defecto no cae dentro del rango especificado';
$string['ierr_uppernegative'] = 'El límite superior se supone que es sin signo';
$string['lowerbound'] = 'Valor mínimo';
$string['lowerbound_help'] = 'El valor mínimo permitido. Vacío para dejar sin asignar el mínimo.';
$string['number'] = 'Número';
$string['pluginname'] = 'Numérico';
$string['restriction_hasdecimals'] = 'tiene {$a} posiciones decimales requeridas';
$string['restriction_hassign'] = 'puede ser negativo';
$string['restriction_isinteger'] = 'se supone que es un número entero';
$string['restriction_lower'] = 'La contestación se supone que es mayor o igual a {$a}';
$string['restriction_lowerupper'] = 'La contestación se supone que encaje entre {$a->lowerbound} y {$a->upperbound}';
$string['restriction_upper'] = 'La contestación se supone que es menor o igual a {$a}';
$string['signed'] = 'Valor con signo';
$string['signed_help'] = '¿Se supone que el número esperado tenga signo?';
$string['uerr_greaterthanmaximum'] = 'El valor proporcionado es mayor que el máximo permitido';
$string['uerr_lowerthanminimum'] = 'El valor proporcionado es menor que el mínimo permitido';
$string['uerr_negative'] = 'El valor proporcionado se supone que sea sin signo';
$string['uerr_notanumber'] = 'El valor proporcionado no es un número';
$string['uerr_notinteger'] = 'El valor proporcionado se supone que sea un número entero';
$string['uerr_outofinternalrange'] = 'El valor proporcionado no cae dentro del rango especificado';
$string['upperbound'] = 'Valor máximo';
$string['upperbound_help'] = 'El mayor número que tiene permitido ingresar el usuario.';
$string['userfriendlypluginname'] = 'Numérico';
