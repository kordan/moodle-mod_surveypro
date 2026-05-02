// file: amd/src/datepicker.js

define([], function() {

    /**
     * Aggiunge il pulsante calendario accanto al gruppo di select.
     *
     * @param {string} baseid   - es. "id_field_datetime_3"
     * @param {int}    mindate  - timestamp Unix della data/ora minima
     * @param {int}    maxdate  - timestamp Unix della data/ora massima
     * @param {int}    step     - passo dei minuti (es. 5, 10, 15...)
     */
    function addCalendarButton(baseid, mindate, maxdate, step) {
        step = parseInt(step); // fix: forza intero

        const selectDay    = document.getElementById(baseid + '_day');
        const selectMonth  = document.getElementById(baseid + '_month');
        const selectYear   = document.getElementById(baseid + '_year');
        const selectHour   = document.getElementById(baseid + '_hour');
        const selectMinute = document.getElementById(baseid + '_minute');

        if (!selectDay || !selectMonth || !selectYear || !selectHour || !selectMinute) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-link btn-sm icon-no-margin ms-1';
        button.setAttribute('aria-label', 'Date picker');
        button.setAttribute('title', 'Date picker');
        button.innerHTML = '<i class="icon fa-regular fa-calendar fa-fw" aria-hidden="true"></i>';

        const minuteSpan = selectMinute.closest('span');
        if (minuteSpan) {
            minuteSpan.style.display = 'inline-flex';
            minuteSpan.style.alignItems = 'center';
            minuteSpan.appendChild(button);
        } else {
            selectMinute.insertAdjacentElement('afterend', button);
        }

        button.addEventListener('click', function(e) {
            e.stopPropagation();
            openDatepicker(selectDay, selectMonth, selectYear, selectHour, selectMinute,
                           mindate, maxdate, step, button);
        });
    }

    /**
     * Apre il popup con calendario e selettore ore/minuti.
     *
     * @param {HTMLElement} selectDay    - select del giorno
     * @param {HTMLElement} selectMonth  - select del mese
     * @param {HTMLElement} selectYear   - select dell'anno
     * @param {HTMLElement} selectHour   - select dell'ora
     * @param {HTMLElement} selectMinute - select dei minuti
     * @param {int}         mindate      - timestamp Unix della data/ora minima
     * @param {int}         maxdate      - timestamp Unix della data/ora massima
     * @param {int}         step         - passo dei minuti
     * @param {HTMLElement} button       - pulsante che ha aperto il picker
     */
    function openDatepicker(selectDay, selectMonth, selectYear, selectHour, selectMinute,
                            mindate, maxdate, step, button) {

        const existingPicker = document.getElementById('surveypro-datepicker-popup');
        if (existingPicker) {
            existingPicker.remove();
            return;
        }

        const currentDay    = parseInt(selectDay.value)    || 1;
        const currentMonth  = parseInt(selectMonth.value)  || 1;
        const currentYear   = parseInt(selectYear.value)   || new Date().getFullYear();
        const currentHour   = parseInt(selectHour.value)   || 0;
        const currentMinute = parseInt(selectMinute.value) || 0;

        const popup = document.createElement('div');
        popup.id = 'surveypro-datepicker-popup';
        popup.style.cssText = 'position:fixed; z-index:9999; background:#fff; ' +
                              'border:1px solid #ccc; border-radius:4px; padding:8px; ' +
                              'box-shadow:0 2px 8px rgba(0,0,0,0.2);';

        popup.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        renderCalendar(popup, currentYear, currentMonth, currentDay,
                       currentHour, currentMinute, mindate, maxdate, step,
            function(day, month, year, hour, minute) {
                setSelectValue(selectDay,    day);
                setSelectValue(selectMonth,  month);
                setSelectValue(selectYear,   year);
                setSelectValue(selectHour,   hour);
                setSelectValue(selectMinute, minute);
                popup.remove();
                document.removeEventListener('click', closePicker);
            }
        );

        document.body.appendChild(popup);
        const rect = button.getBoundingClientRect();
        popup.style.top  = (rect.bottom + 4) + 'px';
        popup.style.left = rect.left + 'px';

        /**
         * Chiude il datepicker al click fuori dal popup.
         *
         * @param {MouseEvent} e - evento click
         */
        function closePicker(e) {
            if (!popup.contains(e.target) && e.target !== button) {
                popup.remove();
                document.removeEventListener('click', closePicker);
            }
        }

        setTimeout(function() {
            document.addEventListener('click', closePicker);
        }, 0);
    }

    /**
     * Renderizza il calendario e il selettore ore/minuti nel contenitore.
     *
     * @param {HTMLElement} container    - elemento DOM che conterrà il calendario
     * @param {int}         year         - anno da visualizzare
     * @param {int}         month        - mese da visualizzare (1..12)
     * @param {int}         selectedDay  - giorno attualmente selezionato
     * @param {int}         selectedHour   - ora attualmente selezionata
     * @param {int}         selectedMinute - minuto attualmente selezionato
     * @param {int}         mindate      - timestamp Unix della data/ora minima
     * @param {int}         maxdate      - timestamp Unix della data/ora massima
     * @param {int}         step         - passo dei minuti
     * @param {Function}    onSelect     - callback(day, month, year, hour, minute)
     */
    function renderCalendar(container, year, month, selectedDay,
                            selectedHour, selectedMinute, mindate, maxdate, step, onSelect) {
        container.innerHTML = '';

        const minDateObj = new Date(mindate * 1000);
        const maxDateObj = new Date(maxdate * 1000);

        // --- Intestazione con navigazione mese ---
        const header = document.createElement('div');
        header.style.cssText = 'display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;';

        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.textContent = '‹';
        prevBtn.className = 'btn btn-sm btn-secondary';

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.textContent = '›';
        nextBtn.className = 'btn btn-sm btn-secondary';

        const title = document.createElement('strong');

        const prevMonth = month === 1  ? 12 : month - 1;
        const prevYear  = month === 1  ? year - 1 : year;
        const nextMonth = month === 12 ? 1  : month + 1;
        const nextYear  = month === 12 ? year + 1 : year;

        const prevFirstDay = new Date(prevYear, prevMonth - 1, 1);
        const minFirstDay  = new Date(minDateObj.getFullYear(), minDateObj.getMonth(), 1);
        const nextFirstDay = new Date(nextYear, nextMonth - 1, 1);
        const maxFirstDay  = new Date(maxDateObj.getFullYear(), maxDateObj.getMonth(), 1);

        prevBtn.disabled = prevFirstDay < minFirstDay;
        nextBtn.disabled = nextFirstDay > maxFirstDay;

        title.textContent = new Date(year, month - 1, 1)
            .toLocaleDateString(document.documentElement.lang || 'it', {month: 'long', year: 'numeric'});

        prevBtn.addEventListener('click', function() {
            renderCalendar(container, prevYear, prevMonth, 0,
                           selectedHour, selectedMinute, mindate, maxdate, step, onSelect);
        });
        nextBtn.addEventListener('click', function() {
            renderCalendar(container, nextYear, nextMonth, 0,
                           selectedHour, selectedMinute, mindate, maxdate, step, onSelect);
        });

        header.appendChild(prevBtn);
        header.appendChild(title);
        header.appendChild(nextBtn);
        container.appendChild(header);

        // --- Griglia giorni ---
        const grid = document.createElement('div');
        grid.style.cssText = 'display:grid; grid-template-columns:repeat(7,32px); gap:2px; text-align:center;';

        ['L', 'M', 'M', 'G', 'V', 'S', 'D'].forEach(function(d) {
            const cell = document.createElement('div');
            cell.textContent = d;
            cell.style.cssText = 'font-weight:bold; font-size:0.8em; padding:2px;';
            grid.appendChild(cell);
        });

        const firstDay    = new Date(year, month - 1, 1).getDay();
        const offset      = (firstDay === 0) ? 6 : firstDay - 1;
        const daysInMonth = new Date(year, month, 0).getDate();

        for (let i = 0; i < offset; i++) {
            grid.appendChild(document.createElement('div'));
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('button');
            cell.type = 'button';
            cell.textContent = day;
            cell.style.cssText = 'width:30px; height:30px; border-radius:50%; border:none; cursor:pointer; font-size:0.85em;';

            const thisDate = new Date(year, month - 1, day);
            const minDay   = new Date(minDateObj.getFullYear(), minDateObj.getMonth(), minDateObj.getDate());
            const maxDay   = new Date(maxDateObj.getFullYear(), maxDateObj.getMonth(), maxDateObj.getDate());

            if (thisDate < minDay || thisDate > maxDay) {
                cell.disabled = true;
                cell.style.color  = '#ccc';
                cell.style.cursor = 'not-allowed';
            } else if (day === selectedDay) {
                cell.style.background = '#0f6cbf';
                cell.style.color      = '#fff';
                cell.addEventListener('click', function() {
                    const timeDiv = container.querySelector('.surveypro-timeselector');
                    const currentHourInPopup   = timeDiv
                        ? parseInt(timeDiv.querySelector('select:first-of-type').value)
                        : selectedHour;
                    const currentMinuteInPopup = timeDiv
                        ? parseInt(timeDiv.querySelector('select:last-of-type').value)
                        : selectedMinute;

                    renderCalendar(container, year, month, day,
                                   currentHourInPopup, currentMinuteInPopup, mindate, maxdate, step, onSelect);
                });
            } else {
                cell.style.background = '#f8f9fa';
                cell.addEventListener('click', function() {
                    // Leggi ora e minuto correnti dal selettore nel popup.
                    const timeDiv = container.querySelector('.surveypro-timeselector');
                    const currentHourInPopup   = timeDiv
                        ? parseInt(timeDiv.querySelector('select:first-of-type').value)
                        : selectedHour;
                    const currentMinuteInPopup = timeDiv
                        ? parseInt(timeDiv.querySelector('select:last-of-type').value)
                        : selectedMinute;

                    renderCalendar(container, year, month, day,
                                   currentHourInPopup, currentMinuteInPopup, mindate, maxdate, step, onSelect);
                });
            }
            grid.appendChild(cell);
        }

        container.appendChild(grid);

        // --- Selettore ore e minuti ---
        renderTimeSelector(container, selectedDay, month, year,
                           selectedHour, selectedMinute, mindate, maxdate, step, onSelect);
    }

    /**
     * Renderizza il selettore di ore e minuti sotto la griglia.
     *
     * @param {HTMLElement} container      - elemento DOM contenitore
     * @param {int}         day            - giorno selezionato
     * @param {int}         month          - mese selezionato
     * @param {int}         year           - anno selezionato
     * @param {int}         selectedHour   - ora attualmente selezionata
     * @param {int}         selectedMinute - minuto attualmente selezionato
     * @param {int}         mindate        - timestamp Unix della data/ora minima
     * @param {int}         maxdate        - timestamp Unix della data/ora massima
     * @param {int}         step           - passo dei minuti
     * @param {Function}    onSelect       - callback(day, month, year, hour, minute)
     */
    function renderTimeSelector(container, day, month, year,
                                selectedHour, selectedMinute, mindate, maxdate, step, onSelect) {

        // Rimuovi eventuale selettore ore/minuti precedente.
        const existing = container.querySelector('.surveypro-timeselector');
        if (existing) {
            existing.remove();
        }

        const minDateObj = new Date(mindate * 1000);
        const maxDateObj = new Date(maxdate * 1000);

        const timeDiv = document.createElement('div');
        timeDiv.className = 'surveypro-timeselector';
        timeDiv.style.cssText = 'margin-top:8px; border-top:1px solid #eee; padding-top:8px; ' +
                                'display:flex; align-items:center; justify-content:center; gap:4px;';

        // --- Select ore ---
        const hourSelect = document.createElement('select');
        hourSelect.className = 'form-select form-select-sm';
        hourSelect.style.width = 'auto';

        for (let h = 0; h < 24; h++) {
            // Calcola se l'ora è nel range.
            const thisDateTime = new Date(year, month - 1, day, h, 0, 0);
            const minDateTime  = new Date(minDateObj.getFullYear(), minDateObj.getMonth(),
                                          minDateObj.getDate(), minDateObj.getHours(), 0, 0);
            const maxDateTime  = new Date(maxDateObj.getFullYear(), maxDateObj.getMonth(),
                                          maxDateObj.getDate(), maxDateObj.getHours(), 59, 59);

            const opt = document.createElement('option');
            opt.value = h;
            opt.textContent = ('0' + h).slice(-2);
            if (thisDateTime < minDateTime || thisDateTime > maxDateTime) {
                opt.disabled = true;
            }
            if (h === selectedHour) {
                opt.selected = true;
            }
            hourSelect.appendChild(opt);
        }

        // --- Separatore : ---
        const colon = document.createElement('span');
        colon.textContent = ':';
        colon.style.fontWeight = 'bold';

        // --- Select minuti ---
        const minuteSelect = document.createElement('select');
        minuteSelect.className = 'form-select form-select-sm';
        minuteSelect.style.width = 'auto';

        for (let m = 0; m <= 59; m += step) {
            const opt = document.createElement('option');
            opt.value = m;
            opt.textContent = ('0' + m).slice(-2);
            if (m === selectedMinute) {
                opt.selected = true;
            }
            minuteSelect.appendChild(opt);
        }

        // --- Pulsante Conferma ---
        const confirmBtn = document.createElement('button');
        confirmBtn.type = 'button';
        confirmBtn.className = 'btn btn-primary btn-sm ms-2';
        confirmBtn.textContent = '✓';
        confirmBtn.addEventListener('click', function() {
            onSelect(day, month, year,
                     parseInt(hourSelect.value),
                     parseInt(minuteSelect.value));
        });

        timeDiv.appendChild(hourSelect);
        timeDiv.appendChild(colon);
        timeDiv.appendChild(minuteSelect);
        timeDiv.appendChild(confirmBtn);
        container.appendChild(timeDiv);
    }

    /**
     * Imposta il valore di un select cercando l'option corrispondente.
     *
     * @param {HTMLElement} select - elemento select da aggiornare
     * @param {int}         value  - valore da selezionare
     */
    function setSelectValue(select, value) {
        const intVal = parseInt(value);
        for (let i = 0; i < select.options.length; i++) {
            if (parseInt(select.options[i].value) === intVal) {
                select.selectedIndex = i;
                break;
            }
        }
    }

    return {
        /**
         * Inizializza il datepicker per un campo datetime di surveypro.
         *
         * @param {Object} params
         * @param {string} params.baseid  - base id del gruppo di select
         * @param {int}    params.mindate - timestamp Unix della data/ora minima
         * @param {int}    params.maxdate - timestamp Unix della data/ora massima
         * @param {int}    params.step    - passo dei minuti
         */
        init: function(params) {
            addCalendarButton(params.baseid, params.mindate, params.maxdate, params.step);
        }
    };
});
