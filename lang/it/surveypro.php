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
$string['pluginadministration'] = 'Amministrazione surveypro';

$string['surveyproname'] = 'Surveypro name';
$string['surveyproname_help'] = 'Choose the name of this surveypro.';
$string['surveypro'] = 'surveypro';

$string['tablayoutname'] = 'Scheda';
    $string['tabitemspage1'] = 'Anteprima';
    $string['tabitemspage2'] = 'Elementi';
    $string['tabitemspage3'] = 'Dettaglio elemento';
    $string['tabitemspage4'] = 'Relazioni';
$string['tabsubmissionsname'] = 'Indagine';
    $string['tabsubmissionspage1'] = 'Stato';
    $string['tabsubmissionspage2'] = 'Raccolta dati';
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

$string['abandoned_submission_deleted'] = 'Eliminata risposta abbandonata';
$string['addnewsubmission'] = 'Nuova risposta';
$string['answerisnoanswer'] = 'Risposta rifiutata';
$string['answernotsubmitted'] = 'Risposta omessa';
$string['applymastertemplates'] = '<a href="{$a}">Applica un master template</a>';
$string['applyusertemplates'] = '<a href="{$a}">Applica un template utente</a>';
$string['attemptinfo'] = 'Informazioni sull\'indagine e le risposte acquisite';
$string['availability'] = 'Disponibilità';
$string['basic_editthanks'] = 'La modifica apportata è stata registrata! Grazie';
$string['basic_submitthanks'] = 'La tua risposta è inviata correttamente. Grazie per aver partecipato all\'indagine.';
$string['branching'] = 'Ramificazione';
$string['bulkaction'] = 'Azioni di massa';
$string['completiondetail:entries'] = 'Risposte da inviare: {$a}';
$string['content'] = 'Contentuto';
$string['count_allitems'] = 'Questionario costituito da {$a} elementi.';
$string['count_hiddenitems'] = '({$a} nascosti)';
$string['count_pages'] = 'Distribuito su {$a} pagine.';
$string['customnumber_help'] = 'Definisce un numero personalizzato per l\'elemento. Può essere un numero intero come "1" o una qualunque altra scelta come, per esempio: 1a, A, 1.1.a, #1, A, A.1... Si consideri la coerenza della numerazione è lasciata alla tua responsabilità. Per questo, si faccia sempre una doppia verifica qualora si scegliesse di modificare l\'ordine delle domande.';
$string['customnumber'] = 'Numero dell\'elemento';
$string['deleteallitems'] = 'Elimina tutti gli elementi';
$string['deleteallsubmissions'] = 'Cancella tutte le risposte';
$string['downloadpdf'] = 'Scarica in pdf';
$string['downloadtocsv'] = 'valori separati da virgola';
$string['downloadtotsv'] = 'valori separati da tabulatore';
$string['downloadtoxls'] = 'excel';
$string['downloadtype'] = 'Formato del file';
$string['emptyanswer'] = 'Risposta vuota';
$string['event_all_submissions_exported'] = 'Esportate risposte';
$string['event_all_submissions_viewed'] = 'Visualizzate risposte';
$string['event_all_usertemplates_viewed'] = 'Visualizzati "template utente"';
$string['event_form_previewed'] = 'Visualizzata anteprima survey';
$string['event_item_created'] = 'Creato nuovo item';
$string['event_item_deleted'] = 'Cancellato item';
$string['event_item_hidden'] = 'Nascosto item';
$string['event_item_modified'] = 'Modificato item';
$string['event_item_shown'] = 'Visualizzato item';
$string['event_mailneverstarted_sent'] = 'Sollecitata l\'indagine mai avviata';
$string['event_mailoneshotmp_sent'] = 'Sollecitata l\'indagine su più pagine';
$string['event_mailpauseresume_sent'] = 'Sollecitata l\'indagine in pausa';
$string['event_mastertemplate_applied'] = 'Applicato "template di sistema"';
$string['event_mastertemplate_saved'] = 'Salvato "template di sistema"';
$string['event_submission_created'] = 'Inviata nuova risposta';
$string['event_submission_deleted'] = 'Cancellata risposta';
$string['event_submission_modified'] = 'Modificata risposta';
$string['event_submission_viewed'] = 'Visualizzato risposta';
$string['event_submissioninpdf_downloaded'] = 'Scaricata risposta in pdf';
$string['event_submissions_imported'] = 'Importate risposte';
$string['event_usertemplate_applied'] = 'Applicato "template utente"';
$string['event_usertemplate_deleted'] = 'Cancellato "template utente"';
$string['event_usertemplate_exported'] = 'Esportato "template utente"';
$string['event_usertemplate_imported'] = 'Importato "template utente"';
$string['event_usertemplate_saved'] = 'Salvato "template utente"';
$string['extranote_help'] = 'Breve nota aggiuntiva relativa a dettagli necessari per rispondere a questa domanda.';
$string['extranote'] = 'Nota personalizzata';
$string['gotolist'] = 'Mostra la lista';
$string['hassubmissions_alert_activitycompletion'] = '<br />La modifica degli elementi del sondaggio cambierà anche lo stato di completamento dell\'attività.<br />Sei stato avvisato.';
$string['hassubmissions_alert'] = 'Questo sondaggio è stato già compilato almeno una volta.<br />Si procedera con estrema cautela e si apportino solo modifiche neutre per non compromettere la validità dell\'intero sondaggio.<br /><br />ATTENZIONE: Aggiungendo un nuovo elemento lo stato di ogni risposta già inviata verrà forzato a "in corso".';
$string['hassubmissions_danger'] = '<br />Le risposte "in corso"...<ul><li>sono soggette a cancellazione in 4 ore se la pausa/ripresa non è consentita;</li><li>sono soggette a cancellazione nel numero di ore impostato nelle impostazioni di surveypro se la pausa/ripresa è consentita.</li></ul>';
$string['hidden_help'] = 'Nascondi questa domanda qualora sia ancora in fase di definizione o, comunque, non ancora pronta per comparire regolarmente nel questionario.';
$string['hidden'] = 'Nascosta';
$string['hideinstructions_help'] = 'Nasconde o mostra le istruzioni di compilazione. Queste indirizzano l\'utente a fornire la risposta attesa riducendo il tempo necesario alla compilazione del questionario.';
$string['hideinstructions'] = 'Nascondi le istruzioni di compilazione';
$string['importusertemplates'] = '<a href="{$a}">Importa un template utente</a>';
$string['includedates'] = 'Includi le date di creazione e modifica';
$string['includehidden'] = 'Includi gli elementi nascosti';
$string['includereserved'] = 'Includi gli elementi riservati';
$string['indent_help'] = 'Il rientro dell\'elemento ovvero il margine sinistro che avrà rispetto alla pagina del questionario.';
$string['indent'] = 'Rientro';
$string['insearchform_help'] = 'Includi questo elemento fra i campi di ricerca?';
$string['insearchform'] = 'Includi nella ricerca';
$string['item'] = 'Elemento';
$string['manageusertemplates'] = '<a href="{$a}">Gestisci i template utente</a>';
$string['maxentries'] = 'Numero massimo di risposte consentite';
$string['nextformpage'] = 'Pagina successiva >>';
$string['noanswer'] = 'Nessuna risposta';
$string['noitemsfoundadmin'] = 'Questa indagine non contiene domande. Si usi il comando "{$a}" per aggiungerne qualcuna.';
$string['nomoresubmissionsallowed'] = 'Il massimo numero di {$a} invii è già stato raggiunto.<br />Non sono consentiti ulteriori invii.';
$string['note'] = 'Nota:';
$string['opened'] = 'Data di apertura';
$string['outputstyle'] = 'Stile del file';
$string['parentconstraints'] = 'Opzioni di relazione';
$string['parentcontent_help'] = 'Risposta che l\'utente deve fornire all\'elemento padre affinché questo elemento divenga acessibile.';
$string['parentcontent'] = 'Risposta abilitante';
$string['parentelement_help'] = 'Elemento la cui risposta, in fase di compilazione del questionario, determina l\'accesso a all\'elemento corrente';
$string['parentelement'] = 'Elemento padre';
$string['parentformat'] = 'Definisci il formato del "{$a->fieldname}" come dai seguenti esempi: {$a->examples}';
$string['pause'] = 'Pausa';
$string['pauseresume_help'] = 'Consente di interrompere una compilazione per riprenderla ed inviarla in un secondo momento';
$string['pauseresume'] = 'Consenti Pausa/Ripresa';
$string['plugin'] = 'Elemento';
$string['position_help'] = 'Colloca il testo della domanda intorno all\'elemento di acquisizione del dato. La collocazione può essere a sinistra dell\'elemento di input, in una riga dedicata immediatamente sopra all\'elemento di input oppure, sempre sopra all\'elemento di input, ma ricoprendo l\'intera larghezza della pagina.';
$string['position'] = 'Collocazione della domanda';
$string['previewmode'] = 'Sei in \'{$a}\'. I bottoni per il salvataggio dei dati non saranno visualizzati';
$string['previousformpage'] = '<< Pagina precedente';
$string['raw'] = 'Grezzo (per ulteriori importazioni in surveypro; il "{$a}" scelto potrebbe non essere rispettato;)';
$string['readonlyaccess'] = 'Accesso in sola lettura';
$string['relation_status'] = 'Stato';
$string['reminder_subject'] = 'Promemoria per una indagine su {$a}';
$string['reminderoneshot_content1'] = 'Gentile {$a->fullname}<br />sembra che il tuo contributo all\'indagine "{$a->surveyproname}" sia ancora in sospeso.';
$string['reminderoneshot_content2'] = '<br />Il tuo lavoro parziale, se non portato a termine, sarà cancellato fra meno di due ore.';
$string['reminderoneshot_content3'] = '<br />Per favore, cerca di collegarti al più presto a {$a} per inviare definitivamente il questionario.<br /><br />Il personale di progetto';
$string['reminder_oneshot_task'] = 'Promemoria per indagini interrotte';
$string['reminder_pauseresume_task'] = 'Promemoria per indagini in pausa da troppo tempo';
$string['reminder_neverstarted_task'] = 'Promemoria per indagini mai avviate';
$string['reminderpaused_content1'] = 'Gentile {$a->fullname}<br />sembra che il tuo contributo all\'indagine "{$a->surveyproname}" sia in pausa da molto tempo.';
$string['reminderpaused_content2'] = '<br />C\'è il rischio concreto che il lavoro paziale già inviato sia presto cancellato.';
$string['reminderpaused_content3'] = '<br />Per favore, cerca di collegarti al più presto a {$a} per inviare definitivamente le informazioni richieste.<br /><br />Il personale di progetto';
$string['remindneverstarted_content'] = 'Gentile {$a->fullname}<br />sembra che il tuo contributo all\'indagine "{$a->surveyproname}" non sia mai stato avviato.<br />Per favore, collegati al più presto a {$a->surveyprourl} per avviare la tua collaborazione con l\'indagine che ti vede coinvolto.<br /><br />Il personale di progetto';
$string['required_help'] = 'Rende questa domanda obbligatoria o opzionale per l\'utente?';
$string['required'] = 'Obbligatorio';
$string['reserved_help'] = 'Rendi questo elemento disponibile solo agli utenti con specifico permesso di compilazione o, in alternativa, rendi questo elemento accessibile a chiunque.';
$string['reserved'] = 'Riservato';
$string['runreport'] = '<a href="{$a->href}">Esegui il report {$a->reportname}</a>';
$string['savemastertemplates'] = '<a href="{$a}">Salva un master template</a>';
$string['saveusertemplates'] = '<a href="{$a}">Salva il template utente</a>';
$string['sortindex'] = 'Ordinamento';
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
$string['typeplugin'] = 'Elemento';
$string['userenrolled'] = 'Utente iscritto: 1';
$string['usersenrolled'] = 'Utenti iscritti: {$a}';
$string['variable_help'] = 'Il nome della variabile associata all\'informazione richiesta.';
$string['variable'] = 'Variabile';
$string['verbose'] = 'Gradevole (per la lettura diretta)';
$string['welcome_dataexport'] = 'Usa questa pagina per esportare le risposte acquisite. <br />
E\' consentita l\'esportazione sia in un formato adatto ai software statistici che in un formato facilmente leggibile. Il contenuto esportato dipende dal "{$a}" scelto per ogni elemento (se disponibile).';
$string['welcome_emptysurvey'] = 'Questa indagine non contiene domande. Per costruire una nuova scheda è possibile aggiungere domande una ad una fino a realizzare uno strumento che aderisca al meglio alle proprie necessità<br />oppure importare un master template per ottenere un scheda predefinita.';
$string['welcome_relationvalidation'] = 'Questo report consente di verificare la correttezza dei vincoli impostati per le ramificazioni. Esegue la verifica della validità di ogni relazione padre-figlio evidenziando quelle scorrette, ovvero quelle che, di fatto, inibiscono sistematicamente la visualizzazione dell\'elemento figlio nell\'indagine, con un messaggio nella colonna "{$a}".';
$string['willclose'] = 'Data di chiusura';
$string['yoursubmissions'] = 'Le tue risposte \'{$a->status}\': {$a->responsescount}';

