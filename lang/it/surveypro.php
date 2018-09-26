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
 * Italian strings for surveypro
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Surveypro';
$string['modulename_help'] = 'Surveypro consente la realizzazione di indagini personalizzate così come di indagini classiche quali ATTLS, COLLES and CRITICAL INCIDENTS. E\' possibile anche riutilizzare parti di indagini già costruite per integrarle in altre.';
$string['modulename_link'] = 'mod/surveypro/view';
$string['modulenameplural'] = 'surveypro';
$string['pluginname'] = 'Surveypro';
$string['pluginadministration'] = 'Gestione surveypro';

$string['surveyproname'] = 'Surveypro name';
$string['surveyproname_help'] = 'Choose the name of this surveypro.';
$string['surveypro'] = 'surveypro';

$string['tablayoutname'] = 'Scheda';
    $string['tabitemspage1'] = 'Anteprima';
    $string['tabitemspage2'] = 'Elementi';
    $string['tabitemspage3'] = 'Dettaglio elemento';
    $string['tabitemspage4'] = 'Verifica relazioni';
$string['tabsubmissionsname'] = 'Indagine';
    $string['tabsubmissionspage1'] = 'Stato';
    $string['tabsubmissionspage2'] = 'Dati raccolti';
    $string['tabsubmissionspage3'] = 'Nuovo inserimento';
    $string['tabsubmissionspage4'] = 'Modifica';
    $string['tabsubmissionspage5'] = 'Sola lettura';
    $string['tabsubmissionspage6'] = 'Ricerca';
    $string['tabsubmissionspage7'] = 'Rapporti';
    $string['tabsubmissionspage8'] = 'Importazione';
    $string['tabsubmissionspage9'] = 'Esportazione';
$string['tabutemplatename'] = 'Template utente';
    $string['tabutemplatepage1'] = 'Gestione';
    $string['tabutemplatepage2'] = 'Salva';
    $string['tabutemplatepage3'] = 'Importa';
    $string['tabutemplatepage4'] = 'Applica';
$string['tabmtemplatename'] = 'Template sistema';
    $string['tabmtemplatepage1'] = 'Genera';
    $string['tabmtemplatepage2'] = 'Applica';

$string['addnewsubmission'] = 'Nuova risposta';
$string['answerisnoanswer'] = 'Risposta non fornita';
$string['applymastertemplates'] = '<a href="{$a}">Applica un master template</a>';
$string['applyusertemplates'] = '<a href="{$a}">Applica un template utente</a>';
$string['attemptinfo'] = 'Informazioni sull\'indagine e le risposte acquisite';
$string['basic_editthanks'] = 'La modifica apportata è stata registrata! Grazie';
$string['basic_submitthanks'] = 'La tua risposta è inviata correttamente. Grazie per aver partecipato all\'indagine.';
$string['count_allitems'] = 'Questionario costituito da {$a} elementi.';
$string['count_hiddenitems'] = '({$a} nascosti)';
$string['coverpage_welcome'] = 'Benvenuto in: {$a}';
$string['customnumber_help'] = 'Definisce un numero personalizzato per l\'elemento. Può essere un numero intero come "1" o una qualunque altra scelta come, per esempio: 1a, A, 1.1.a, #1, A, A.1... Si consideri la coerenza della numerazione è lasciata alla tua responsabilità. Per questo, si faccia sempre una doppia verifica qualora si scegliesse di modificare l\'ordine delle domande.';
$string['customnumber'] = 'Numero dell\'elemento';
$string['downloadpdf'] = 'Scarica in pdf';
$string['emptyanswer'] = 'Risposta vuota';
$string['extranote_help'] = 'Breve nota aggiuntiva relativa a dettagli necessari per rispondere a questa domanda.';
$string['extranote'] = 'Nota personalizzata';
$string['gotolist'] = 'Mostra la lista';
$string['hidden_help'] = 'Nascondi questa domanda qualora sia ancora in fase di definizione o, comunque, non ancora pronta per comparire regolarmente nel questionario.';
$string['hidden'] = 'Nascosta';
$string['hideinstructions_help'] = 'Nasconde o mostra le istruzioni di compilazione. Queste indirizzano l\'utente a fornire la risposta attesa riducendo il tempo necesario alla compilazione del questionario.';
$string['hideinstructions'] = 'Nascondi le istruzioni di compilazione';
$string['importusertemplates'] = '<a href="{$a}">Importa un template utente</a>';
$string['indent_help'] = 'Il rientro dell\'elemento ovvero il margine sinistro che avrà rispetto alla pagina del questionario.';
$string['indent'] = 'Rientro';
$string['insearchform_help'] = 'Includi questo elemento fra i campi di ricerca?';
$string['insearchform'] = 'Includi nella ricerca';
$string['manageusertemplates'] = '<a href="{$a}">Gestisci i template utente</a>';
$string['maxentries'] = 'Numero massimo di risposte consentite';
$string['nextformpage'] = 'Pagina successiva >>';
$string['noanswer'] = 'Nessuna risposta';
$string['nomoresubmissionsallowed'] = 'Il massimo numero di {$a} invii è già stato raggiunto.<br />Non sono consentiti ulteriori invii.';
$string['note'] = 'Nota:';
$string['opened'] = 'Data di apertura';
$string['parentcontent_help'] = 'Risposta che l\'utente deve fornire all\'elemento padre affinché questo elemento divenga acessibile.';
$string['parentcontent'] = 'Risposta abilitante';
$string['parentelement_help'] = 'Elemento la cui risposta, in fase di compilazione del questionario, determina l\'accesso a all\'elemento corrente';
$string['parentelement'] = 'Elemento padre';
$string['parentformat'] = 'Definisci il formato del "{$a->fieldname}" come dai seguenti esempi: {$a->examples}';
$string['position_help'] = 'Colloca il testo della domanda intorno all\'elemento di acquisizione del dato. La collocazione può essere a sinistra dell\'elemento di input, in una riga dedicata immediatamente sopra all\'elemento di input oppure, sempre sopra all\'elemento di input, ma ricoprendo l\'intera larghezza della pagina.';
$string['position'] = 'Collocazione della domanda';
$string['previewmode'] = 'Sei in \'{$a}\'. I bottoni per il salvataggio dei dati non saranno visualizzati';
$string['previousformpage'] = '<< Pagina precedente';
$string['readonlyaccess'] = 'Accesso in sola lettura';
$string['required_help'] = 'Rende questa domanda obbligatoria o opzionale per l\'utente?';
$string['required'] = 'Obbligatorio';
$string['reserved_help'] = 'Rendi questo elemento disponibile solo agli utenti con specifico permesso di compilazione o, in alternativa, rendi questo elemento accessibile a chiunque.';
$string['reserved'] = 'Riservato';
$string['runreport'] = '<a href="{$a->href}">Esegui il report {$a->reportname}</a>';
$string['savemastertemplates'] = '<a href="{$a}">Salva un master template</a>';
$string['saveusertemplates'] = '<a href="{$a}">Salva il template utente</a>';
$string['statusclosed'] = 'chiusa';
$string['statusinprogress'] = 'in corso';
$string['submissions_all_1_1'] = '1 risposta inviata da 1 utente';
$string['submissions_all_1_many'] = '1 risposta inviata da {$a->usercount} utenti';
$string['submissions_all_many_1'] = '{$a->submissions} risposte inviate da 1 utente';
$string['submissions_all_many_many'] = '{$a->submissions} risposte inviate da {$a->usercount} utenti';
$string['submissions_detail_1_1'] = '1 risposta \'{$a->status}\' inviata da 1 utente';
$string['submissions_detail_1_many'] = '1 risposta \'{$a->status}\' inviata da {$a->usercount} utenti';
$string['submissions_detail_many_1'] = '{$a->submissions} risposte \'{$a->status}\' inviate da 1 utente';
$string['submissions_detail_many_many'] = '{$a->submissions} risposte \'{$a->status}\' inviate da {$a->usercount} utenti';
$string['submissions_welcome'] = 'Risposte acquisite';
$string['timecreated'] = 'Creato';
$string['timemodified'] = 'Modificato';
$string['variable_help'] = 'Il nome della variabile associata all\'informazione richiesta.';
$string['variable'] = 'Variabile';
$string['willclose'] = 'Data di chiusura';
$string['yoursubmissions'] = 'Le tue risposte \'{$a->status}\': {$a->responsescount}';

