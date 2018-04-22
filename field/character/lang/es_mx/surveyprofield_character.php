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
 * Strings for component 'surveyprofield_character', language 'es_mx'
 *
 * @package   surveyprofield_character
 * @subpackage character
 * @copyright  2013 onwards German Valero <gvalero@unam.mx >
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['custompattern'] = 'personalizado';
$string['defaultvalue'] = 'Valor por defecto';
$string['defaultvalue_help'] = 'Este es el valor que el usuario remoto encontrará contestado por defecto';
$string['free'] = 'patrón libre';
$string['ierr_defaultbadlength'] = 'El valor por defecto no es de {$a} caracteres de longitud porque está declarado implícitamente en el patrón';
$string['ierr_defaultisnotemail'] = 'El valor por defecto no coincide con el patrón del Email';
$string['ierr_defaultisnoturl'] = 'El valor por defecto no parece ser una URL válida';
$string['ierr_extracharfound'] = 'Los caracteres {$a} no están permitidos. Por favor, use solamente "A", "a", "*" y "0"';
$string['ierr_minexceeds'] = 'La longitud mínima debe de ser positiva';
$string['ierr_mingtmax'] = 'La longitud mínima debe de ser menor que la longitud máxima';
$string['ierr_nopatternmatch'] = 'El valor por defecto no coincide con el patrón requerido';
$string['ierr_patternisempty'] = 'falta el patrón';
$string['ierr_toolongdefault'] = 'El valor por defecto debe de ser menor o igual a la longitud máxima permitida';
$string['ierr_tooshortdefault'] = 'El valor por defecto debe de ser más largo o igual a la longitud mínima permitida';
$string['length'] = 'Ancho del campo en caracteres';
$string['length_help'] = 'El ancho del campo en caracteres';
$string['mail'] = 'dirección Email';
$string['maxlength'] = 'Longitud máxima (en caracteres)';
$string['maxlength_help'] = 'El número máximo de caracteres permitidos para la contestación a esta pregunta';
$string['minlength'] = 'Longitud mínima (en caracteres)';
$string['minlength_help'] = 'El número mínimo de caracteres permitidos para la contestación a esta pregunta';
$string['pattern'] = 'Patrón de texto';
$string['pattern_help'] = 'Si se supone que la contestación ajuste a un patrón específico, defínala aquí usando <ul><li>"A" para caracteres en MAYÚSCULAS; </li><li>"a" para caracteres en minúsculas; </li><li>"0" para números; </li><li>"*" para incluir MAYÚSCULAS, minúsculas, números y algun otro caracter como por ejemplo: ,_%."$!\' o espacios.</li></ul>';
$string['pluginname'] = 'Texto corto';
$string['restrictions_custom'] = 'El texto se supone que coincide con el siguiente patrón: "{$a}"';
$string['restrictions_email'] = 'Aquí se espera un Email';
$string['restrictions_exact'] = 'El texto se supone que es de exactamente {$a} caracteres de longitud';
$string['restrictions_max'] = 'El texto se supone que es menor o igual a {$a} caracteres';
$string['restrictions_min'] = 'La respuesta se supone que es más larga o  igual a {$a} caracteres';
$string['restrictions_minmax'] = 'El rango de la longitud del texto se supone que esté entre {$a->minlength} y {$a->maxlength} caracteres';
$string['restrictions_url'] = 'Aquí se espera una URL';
$string['uerr_invalidemail'] = 'El texto no es un Email válido';
$string['uerr_invalidurl'] = 'El texto no es una URL válida';
$string['uerr_nopatternmatch'] = 'El texto no coincide con el patrón requerido';
$string['uerr_texttoolong'] = 'El texto es demasiado largo';
$string['uerr_texttooshort'] = 'El texto es demasiado corto';
$string['url'] = 'URL de página web';
$string['userfriendlypluginname'] = 'Texto (corto)';
