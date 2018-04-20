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
 * Strings for component 'surveyprofield_rate', language 'es_mx'
 *
 * @package   surveyprofield_rate
 * @subpackage rate
 * @copyright  2013 onwards German Valero <gvalero@unam.mx >
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['customdefault'] = 'Personalizado';
$string['defaultoption'] = 'Por defecto';
$string['defaultoption_help'] = 'Este es el valor que el usuario remoto encontrará contestado por defecto. El valor por defecto para este tipo de pregunta es obligatorio, por lo que cuando no sea especificado, será "Elija..." (Choose...).';
$string['differentrates'] = 'Forzar valoraciones diferentes';
$string['differentrates_help'] = 'Forzará al usuario a valorar cada elemento con un valor diferente';
$string['diffratesrequired'] = 'Los puntajes se supone que sean diferentes entre sí';
$string['downloadformat'] = 'Formato de descarga';
$string['downloadformat_help'] = 'Use esta opción para definir el formato del valor regresado por este campo.<br />Eligiendo \'<strong>selección</strong>\' Usted obtiene una lista separada por comas de los valores correspondientes a la selección del usuario remoto.<br />Eligiendo \'<strong>contestación posicional</strong>\' Usted obtiene una contestación hecha por los valores de número de las opciones definidas para este campo. Para cada opción seleccionada por el usuario remoto Usted obtendrá un 1 (o el valor correspondiente si estuviera definido); para cada opción no seleccionada por el usuario remoto Usted obtendrá un 0.<br />Ejemplo: supongamos la pregunta: "¿Usualmente qué tienes para desayunar?" con las opciones de: "leche, mermelada, jamón, huevos, pan, jugo de naranja". Supongamos además que el usuario seleccionó: "jamón" Y "huevos" Y "jugo de naranja".<br />Eligiendo aquí \'selección\', el valor regresado por este ítem será: "jamón, huevos, jugo de naranja".<br />Eligiendo aquí \'respuesta posicional\', el valor regresado por este ítem será: "0, 0, 1, 1, 0, 1" porque las opciones primera y segunda ("leche, jamón") no fueron elegidas, las opciones tercera y cuarta ("jamón, huevos") fueron seleccionadas, la penúltima ("pan") no fue seleccionada y la última ("jugo de naranja") fue elegida por el usuario remoto.';
$string['ierr_defaultsduplicated'] = 'Los valores por defecto deben de ser diferentes cuando se requieren valoraciones diferentes';
$string['ierr_foreigndefaultvalue'] = 'El ítem por defecto "{$a}" no se encontró entre las valoraciones';
$string['ierr_invaliddefaultscount'] = 'El número de valores por defecto debe ser igual al número de opciones';
$string['ierr_labelsduplicated'] = 'Las valoracioens deben de ser diferentes entre sí';
$string['ierr_notenoughrates'] = 'El número de valoraciones no es suficiente para forzar valoracioens diferentes';
$string['ierr_optionsduplicated'] = 'Las  opciones deben ser diferentes entre sí';
$string['ierr_valuesduplicated'] = 'Los valores deben de ser diferentes entre sí';
$string['options'] = 'Opciones';
$string['options_help'] = 'La lista de las opciones para este ítem.';
$string['pluginname'] = 'Valoración';
$string['rates'] = 'Valoraciones';
$string['rates_help'] = 'La lista de valores para valorar las opcioens de esta pregunta. Usted puede elegir escribirlas con el formato valor::etiqueta. La etiqueta será mostrada en la pantalla, el valor será almacenado en el campo de la encuesta. Si Usted solamente especifica una palabra por línea, el valor y la etiqueta serán ambos valuadoa a esa palabra.';
$string['returnlabels'] = 'lista de opciones con etiquetas correspondientes de valoraciones';
$string['returnposition'] = 'contestación posicional';
$string['returnvalues'] = 'lista de opciones con valores correspondientes de valoraciones';
$string['style'] = 'Estilo del elemento';
$string['style_help'] = 'Usted puede elegir si es que quiere o no permitir la valoración de elementos usando menús desplegables o botones de selección. El resultado general será afectado por esta opción.';
$string['uerr_duplicaterate'] = 'No está permitida valoración duplicada';
$string['uerr_optionnotset'] = 'Por favor elija una opción';
$string['usemenu'] = 'menú desplegable';
$string['useradio'] = 'botones de selección';
$string['userfriendlypluginname'] = 'Valoración';
