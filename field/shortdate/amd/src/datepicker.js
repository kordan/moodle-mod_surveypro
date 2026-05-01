// file: amd/src/datepicker.js

define([], function() {

    /**
     * Aggiunge il pulsante datepicker accanto al select dell'anno.
     *
     * @param {string} baseid          - es. "id_field_shortdate_3"
     * @param {int}    lowerboundmonth - mese minimo
     * @param {int}    upperboundmonth - mese massimo
     * @param {int}    lowerboundyear  - anno minimo
     * @param {int}    upperboundyear  - anno massimo
     */
    function addDateButton(baseid, lowerboundmonth, upperboundmonth, lowerboundyear, upperboundyear) {

        const selectMonth = document.getElementById(baseid + '_month');
        const selectYear  = document.getElementById(baseid + '_year');

        if (!selectMonth || !selectYear) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-link btn-sm icon-no-margin ms-1';
        button.setAttribute('aria-label', 'Date picker');
        button.setAttribute('title', 'Date picker');
        button.innerHTML = '<i class="icon fa-regular fa-calendar fa-fw" aria-hidden="true"></i>';

        const yearSpan = selectYear.closest('span');
        if (yearSpan) {
            yearSpan.style.display = 'inline-flex';
            yearSpan.style.alignItems = 'center';
            yearSpan.appendChild(button);
        } else {
            selectYear.insertAdjacentElement('afterend', button);
        }

        button.addEventListener('click', function(e) {
            e.stopPropagation();
            openDatepicker(selectMonth, selectYear,
                           lowerboundmonth, upperboundmonth,
                           lowerboundyear, upperboundyear, button);
        });
    }

    /**
     * Apre il popup con la griglia dei mesi.
     *
     * @param {HTMLElement} selectMonth     - select del mese
     * @param {HTMLElement} selectYear      - select dell'anno
     * @param {int}         lowerboundmonth - mese minimo
     * @param {int}         upperboundmonth - mese massimo
     * @param {int}         lowerboundyear  - anno minimo
     * @param {int}         upperboundyear  - anno massimo
     * @param {HTMLElement} button          - pulsante che ha aperto il picker
     */
    function openDatepicker(selectMonth, selectYear,
                            lowerboundmonth, upperboundmonth,
                            lowerboundyear, upperboundyear, button) {

        const existingPicker = document.getElementById('surveypro-shortdatepicker-popup');
        if (existingPicker) {
            existingPicker.remove();
            return;
        }

        const currentMonth = parseInt(selectMonth.value) || lowerboundmonth;
        const currentYear  = parseInt(selectYear.value)  || lowerboundyear;

        const popup = document.createElement('div');
        popup.id = 'surveypro-shortdatepicker-popup';
        popup.style.cssText = 'position:fixed; z-index:9999; background:#fff; ' +
                              'border:1px solid #ccc; border-radius:4px; padding:8px; ' +
                              'box-shadow:0 2px 8px rgba(0,0,0,0.2);';

        popup.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        renderMonthGrid(popup, currentYear, currentMonth,
                        lowerboundmonth, upperboundmonth,
                        lowerboundyear, upperboundyear,
            function(month, year) {
                setSelectValue(selectMonth, month);
                setSelectValue(selectYear,  year);
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
     * Renderizza la griglia dei 12 mesi con navigazione per anno.
     *
     * @param {HTMLElement} container       - elemento DOM contenitore
     * @param {int}         year            - anno da visualizzare
     * @param {int}         selectedMonth   - mese attualmente selezionato
     * @param {int}         lowerboundmonth - mese minimo
     * @param {int}         upperboundmonth - mese massimo
     * @param {int}         lowerboundyear  - anno minimo
     * @param {int}         upperboundyear  - anno massimo
     * @param {Function}    onSelect        - callback(month, year)
     */
    function renderMonthGrid(container, year, selectedMonth,
                             lowerboundmonth, upperboundmonth,
                             lowerboundyear, upperboundyear, onSelect) {
        container.innerHTML = '';

        // --- Intestazione con navigazione anno ---
        const header = document.createElement('div');
        header.style.cssText = 'display:flex; justify-content:space-between; ' +
                               'align-items:center; margin-bottom:8px;';

        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.textContent = '‹';
        prevBtn.className = 'btn btn-sm btn-secondary';
        prevBtn.disabled = (year <= lowerboundyear);

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.textContent = '›';
        nextBtn.className = 'btn btn-sm btn-secondary';
        nextBtn.disabled = (year >= upperboundyear);

        const title = document.createElement('strong');
        title.textContent = year;
        title.style.fontSize = '1.1em';

        prevBtn.addEventListener('click', function() {
            renderMonthGrid(container, year - 1, selectedMonth,
                            lowerboundmonth, upperboundmonth,
                            lowerboundyear, upperboundyear, onSelect);
        });
        nextBtn.addEventListener('click', function() {
            renderMonthGrid(container, year + 1, selectedMonth,
                            lowerboundmonth, upperboundmonth,
                            lowerboundyear, upperboundyear, onSelect);
        });

        header.appendChild(prevBtn);
        header.appendChild(title);
        header.appendChild(nextBtn);
        container.appendChild(header);

        // --- Griglia mesi ---
        const grid = document.createElement('div');
        grid.style.cssText = 'display:grid; grid-template-columns:repeat(3, 1fr); gap:4px;';

        const monthNames = [];
        for (let m = 1; m <= 12; m++) {
            monthNames.push(new Date(2000, m - 1, 1)
                .toLocaleDateString(document.documentElement.lang || 'it', {month: 'short'}));
        }

        for (let m = 1; m <= 12; m++) {
            const cell = document.createElement('button');
            cell.type = 'button';
            cell.textContent = monthNames[m - 1];
            cell.style.cssText = 'padding:6px 4px; border-radius:4px; border:none; ' +
                                 'cursor:pointer; font-size:0.85em;';

            // Un mese è valido se:
            // - non è nell'anno minimo oppure è >= lowerboundmonth
            // - non è nell'anno massimo oppure è <= upperboundmonth
            const validInYear = (year > lowerboundyear || m >= lowerboundmonth) &&
                                 (year < upperboundyear || m <= upperboundmonth);

            if (!validInYear) {
                cell.disabled = true;
                cell.style.color  = '#ccc';
                cell.style.cursor = 'not-allowed';
            } else if (m === selectedMonth) {
                cell.style.background = '#0f6cbf';
                cell.style.color      = '#fff';
                cell.addEventListener('click', function() { onSelect(m, year); });
            } else {
                cell.style.background = '#f8f9fa';
                cell.addEventListener('click', function() { onSelect(m, year); });
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
         * Inizializza il datepicker per un campo shortdate di surveypro.
         *
         * @param {Object} params
         * @param {string} params.baseid          - base id del gruppo di select
         * @param {int}    params.lowerboundmonth - mese minimo
         * @param {int}    params.upperboundmonth - mese massimo
         * @param {int}    params.lowerboundyear  - anno minimo
         * @param {int}    params.upperboundyear  - anno massimo
         */
        init: function(params) {
            addDateButton(params.baseid, params.lowerboundmonth, params.upperboundmonth,
                          params.lowerboundyear, params.upperboundyear);
        }
    };
});
