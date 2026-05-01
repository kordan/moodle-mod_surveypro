// file: amd/src/datepicker.js

define([], function() {

    /**
     * Aggiunge il pulsante datepicker accanto al select del mese.
     *
     * @param {string} baseid          - es. "id_field_recurrence_3"
     * @param {int}    lowerboundday   - giorno minimo
     * @param {int}    upperboundday   - giorno massimo
     * @param {int}    lowerboundmonth - mese minimo
     * @param {int}    upperboundmonth - mese massimo
     * @param {int}    wraparound      - 1 se l'intervallo attraversa l'anno nuovo
     */
    function addCalendarButton(baseid, lowerboundday, upperboundday,
                               lowerboundmonth, upperboundmonth, wraparound) {

        const selectDay   = document.getElementById(baseid + '_day');
        const selectMonth = document.getElementById(baseid + '_month');

        if (!selectDay || !selectMonth) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-link btn-sm icon-no-margin ms-1';
        button.setAttribute('aria-label', 'Date picker');
        button.setAttribute('title', 'Date picker');
        button.innerHTML = '<i class="icon fa-regular fa-calendar fa-fw" aria-hidden="true"></i>';

        const monthSpan = selectMonth.closest('span');
        if (monthSpan) {
            monthSpan.style.display = 'inline-flex';
            monthSpan.style.alignItems = 'center';
            monthSpan.appendChild(button);
        } else {
            selectMonth.insertAdjacentElement('afterend', button);
        }

        button.addEventListener('click', function(e) {
            e.stopPropagation();
            openDatepicker(selectDay, selectMonth,
                           lowerboundday, upperboundday,
                           lowerboundmonth, upperboundmonth,
                           wraparound, button);
        });
    }

    /**
     * Apre il popup con la griglia del mese.
     *
     * @param {HTMLElement} selectDay       - select del giorno
     * @param {HTMLElement} selectMonth     - select del mese
     * @param {int}         lowerboundday   - giorno minimo
     * @param {int}         upperboundday   - giorno massimo
     * @param {int}         lowerboundmonth - mese minimo
     * @param {int}         upperboundmonth - mese massimo
     * @param {int}         wraparound      - 1 se l'intervallo attraversa l'anno nuovo
     * @param {HTMLElement} button          - pulsante che ha aperto il picker
     */
    function openDatepicker(selectDay, selectMonth,
                            lowerboundday, upperboundday,
                            lowerboundmonth, upperboundmonth,
                            wraparound, button) {

        const existingPicker = document.getElementById('surveypro-recurrencepicker-popup');
        if (existingPicker) {
            existingPicker.remove();
            return;
        }

        const currentDay   = parseInt(selectDay.value)   || lowerboundday;
        const currentMonth = parseInt(selectMonth.value) || lowerboundmonth;

        const popup = document.createElement('div');
        popup.id = 'surveypro-recurrencepicker-popup';
        popup.style.cssText = 'position:fixed; z-index:9999; background:#fff; ' +
                              'border:1px solid #ccc; border-radius:4px; padding:8px; ' +
                              'box-shadow:0 2px 8px rgba(0,0,0,0.2);';

        popup.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        renderCalendar(popup, currentMonth, currentDay,
                       lowerboundday, upperboundday,
                       lowerboundmonth, upperboundmonth,
                       wraparound,
            function(day, month) {
                setSelectValue(selectDay,   day);
                setSelectValue(selectMonth, month);
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
     * Costruisce la lista dei mesi validi in base ai parametri.
     *
     * @param {int} lowerboundmonth - mese minimo
     * @param {int} upperboundmonth - mese massimo
     * @param {int} wraparound      - 1 se l'intervallo attraversa l'anno nuovo
     * @returns {Array}             - array di interi con i mesi validi
     */
    function getValidMonths(lowerboundmonth, upperboundmonth, wraparound) {
        const months = [];
        if (wraparound) {
            for (let i = lowerboundmonth; i <= 12; i++) {
                months.push(i);
            }
            for (let i = 1; i <= upperboundmonth; i++) {
                months.push(i);
            }
        } else {
            for (let i = lowerboundmonth; i <= upperboundmonth; i++) {
                months.push(i);
            }
        }
        return months;
    }

    /**
     * Renderizza la griglia del mese senza anno.
     *
     * @param {HTMLElement} container       - elemento DOM contenitore
     * @param {int}         month           - mese da visualizzare (1..12)
     * @param {int}         selectedDay     - giorno attualmente selezionato
     * @param {int}         lowerboundday   - giorno minimo
     * @param {int}         upperboundday   - giorno massimo
     * @param {int}         lowerboundmonth - mese minimo
     * @param {int}         upperboundmonth - mese massimo
     * @param {int}         wraparound      - 1 se l'intervallo attraversa l'anno nuovo
     * @param {Function}    onSelect        - callback(day, month)
     */
    function renderCalendar(container, month, selectedDay,
                            lowerboundday, upperboundday,
                            lowerboundmonth, upperboundmonth,
                            wraparound, onSelect) {
        container.innerHTML = '';

        const validMonths = getValidMonths(lowerboundmonth, upperboundmonth, wraparound);
        const currentIndex = validMonths.indexOf(month);

        // --- Intestazione con navigazione mese ---
        const header = document.createElement('div');
        header.style.cssText = 'display:flex; justify-content:space-between; ' +
                               'align-items:center; margin-bottom:6px;';

        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.textContent = '‹';
        prevBtn.className = 'btn btn-sm btn-secondary';
        prevBtn.disabled = (currentIndex <= 0);

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.textContent = '›';
        nextBtn.className = 'btn btn-sm btn-secondary';
        nextBtn.disabled = (currentIndex >= validMonths.length - 1);

        const title = document.createElement('strong');
        title.textContent = new Date(2000, month - 1, 1)
            .toLocaleDateString(document.documentElement.lang || 'it', {month: 'long'});

        prevBtn.addEventListener('click', function() {
            const prevMonth = validMonths[currentIndex - 1];
            renderCalendar(container, prevMonth, 0,
                           lowerboundday, upperboundday,
                           lowerboundmonth, upperboundmonth,
                           wraparound, onSelect);
        });
        nextBtn.addEventListener('click', function() {
            const nextMonth = validMonths[currentIndex + 1];
            renderCalendar(container, nextMonth, 0,
                           lowerboundday, upperboundday,
                           lowerboundmonth, upperboundmonth,
                           wraparound, onSelect);
        });

        header.appendChild(prevBtn);
        header.appendChild(title);
        header.appendChild(nextBtn);
        container.appendChild(header);

        // --- Griglia giorni ---
        const grid = document.createElement('div');
        grid.style.cssText = 'display:grid; grid-template-columns:repeat(7,32px); ' +
                             'gap:2px; text-align:center;';

        ['L', 'M', 'M', 'G', 'V', 'S', 'D'].forEach(function(d) {
            const cell = document.createElement('div');
            cell.textContent = d;
            cell.style.cssText = 'font-weight:bold; font-size:0.8em; padding:2px;';
            grid.appendChild(cell);
        });

        // Usiamo un anno di riferimento non bisestile per febbraio.
        const refYear  = 2001;
        const firstDay = new Date(refYear, month - 1, 1).getDay();
        const offset   = (firstDay === 0) ? 6 : firstDay - 1;
        const daysInMonth = new Date(refYear, month, 0).getDate();

        for (let i = 0; i < offset; i++) {
            grid.appendChild(document.createElement('div'));
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('button');
            cell.type = 'button';
            cell.textContent = day;
            cell.style.cssText = 'width:30px; height:30px; border-radius:50%; border:none; ' +
                                 'cursor:pointer; font-size:0.85em;';

            // Un giorno è valido se:
            // - il mese non è il primo valido oppure day >= lowerboundday
            // - il mese non è l'ultimo valido oppure day <= upperboundday
            const isFirstMonth = (month === lowerboundmonth);
            const isLastMonth  = (month === upperboundmonth);
            const validDay = (!isFirstMonth || day >= lowerboundday) &&
                             (!isLastMonth  || day <= upperboundday);

            if (!validDay) {
                cell.disabled = true;
                cell.style.color  = '#ccc';
                cell.style.cursor = 'not-allowed';
            } else if (day === selectedDay) {
                cell.style.background = '#0f6cbf';
                cell.style.color      = '#fff';
                cell.addEventListener('click', function() { onSelect(day, month); });
            } else {
                cell.style.background = '#f8f9fa';
                cell.addEventListener('click', function() { onSelect(day, month); });
            }

            grid.appendChild(cell);
        }

        container.appendChild(grid);
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
         * Inizializza il datepicker per un campo recurrence di surveypro.
         *
         * @param {Object} params
         * @param {string} params.baseid          - base id del gruppo di select
         * @param {int}    params.lowerboundday   - giorno minimo
         * @param {int}    params.upperboundday   - giorno massimo
         * @param {int}    params.lowerboundmonth - mese minimo
         * @param {int}    params.upperboundmonth - mese massimo
         * @param {int}    params.wraparound      - 1 se attraversa l'anno nuovo
         */
        init: function(params) {
            addCalendarButton(params.baseid,
                              params.lowerboundday, params.upperboundday,
                              params.lowerboundmonth, params.upperboundmonth,
                              params.wraparound);
        }
    };
});
