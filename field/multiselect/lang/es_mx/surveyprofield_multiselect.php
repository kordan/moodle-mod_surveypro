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
 * Strings for component 'surveyprofield_multiselect', language 'es_mx'
 *
 * @package   surveyprofield_multiselect
 * @subpackage multiselect
 * @copyright  2013 onwards German Valero <gvalero@unam.mx >
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['defaultvalue'] = 'Por defecto';
$string['defaultvalue_help'] = 'Este es el valor que el usuario remoto encontrará contestado por defecto';
$string['downloadformat'] = 'Formato de descarga';
$string['downloadformat_help'] = 'Use esta opción para definir el formato del valor regresado por este campo.<br />Eligiendo \'<strong>selección</strong>\' Usted obtiene una lista separada por comas de los valores correspondientes a la selección del usuario remoto.<br />Eligiendo \'<strong>contestación posicional</strong>\' Usted obtiene una contestación hecha por los valores de número de las opciones definidas para este campo. Para cada opción seleccionada por el usuario remoto Usted obtendrá un 1 (o el valor correspondiente si estuviera definido); para cada opción no seleccionada por el usuario remoto Usted obtendrá un 0.<br />Ejemplo: supongamos la pregunta: "¿Usualmente qué tienes para desayunar?" con las opciones de: "leche, mermelada, jamón, huevos, pan, jugo de naranja". Supongamos además que el usuario seleccionó: "jamón" Y "huevos" Y "jugo de naranja".<br />Eligiendo aquí \'selección\', el valor regresado por este ítem será: "jamón, huevos, jugo de naranja".<br />Eligiendo aquí \'respuesta posicional\', el valor regresado por este ítem será: "0, 0, 1, 1, 0, 1" porque las opciones primera y segunda ("leche, jamón") no fueron elegidas, las opciones tercera y cuarta ("jamón, huevos") fueron seleccionadas, la penúltima ("pan") no fue seleccionada y la última ("jugo de naranja") fue elegida por el usuario remoto.';
$string['heightinrows'] = 'Altura en filas';
$string['heightinrows_help'] = 'El número de filas que mostrará el multiselector';
$string['ierr_defaultsduplicated'] = 'Los valores por defecto deben de ser diferentes entre sí';
$string['ierr_foreigndefaultvalue'] = 'El ítem por defecto "{$a}" no se encontró entre las opciones';
$string['ierr_labelsduplicated'] = 'Las opciones deben de ser diferentes entre sí';
$string['ierr_minimumrequired'] = 'El número mínimo de ítems a seleccionar debe ser menor que {$a} (número de opciones)';
$string['ierr_optionswithseparator'] = 'Las opciones no pueden contener "{$a}"';
$string['ierr_valuesduplicated'] = 'Los valores deben de ser diferentes entre sí';
$string['minimumrequired'] = 'Mínimo de ítems requeridos';
$string['minimumrequired_help'] = 'El número máximo de ítems que el usuario es forzado a elegir en su contestación';
$string['noanswerdefault'] = '"sin contestación" como valor por defecto';
$string['noanswerdefault_help'] = 'Use esta opción para incluir "Sin contestación" entre los valores por defecto';
$string['option'] = 'Opción';
$string['options'] = 'Opciones';
$string['options_help'] = 'La lista de las opciones para este ítem. Usted tiene permitido escribirlas como: valor::etiqueta para definir ambos valor y etiqueta. La etiqueta será mostrada en el menú desplegable, el valor será almacenado en el campo. Si Usted solamente especifica una palabra por línea (sin separador), ambos el valor y la etiqueta serán valorados para esa palabra.';
$string['parentformat'] = '[una<br />etiqueta<br />por<br />línea]';
$string['pluginname'] = 'Selección múltiple';
$string['restrictions_minimumrequired_more'] = 'Al menos {$a} ítems tienen que ser seleccionados';
$string['restrictions_minimumrequired_one'] = 'Al menos 1 ítem debe de ser seleccionado';
$string['returnlabels'] = 'etiqueta de ítems seleccionados';
$string['returnposition'] = 'contestación posicional';
$string['returnvalues'] = 'valor de ítems seleccionados';
$string['uerr_lowerthanminimum_more'] = 'Por favor seleccione al menos {$a} opciones';
$string['uerr_lowerthanminimum_one'] = 'Por favor seleccione al menos una opción';
$string['userfriendlypluginname'] = 'Selección múltiple';
