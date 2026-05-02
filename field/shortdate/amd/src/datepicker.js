// file: amd/src/datepicker.js

define([], function() {

    /**
     * Adds a date picker button next to the year dropdown.
     *
     * @param {string} baseid          - es. "id_field_shortdate_3"
     * @param {int}    lowerboundmonth - minimum month
     * @param {int}    upperboundmonth - maximum month
     * @param {int}    lowerboundyear  - minimum year
     * @param {int}    upperboundyear  - maximum year
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
     * Open the pop-up window with the calendar.
     *
     * @param {HTMLElement} selectMonth     - select for the month
     * @param {HTMLElement} selectYear      - select for the year
     * @param {int}         lowerboundmonth - minimum month
     * @param {int}         upperboundmonth - maximum month
     * @param {int}         lowerboundyear  - minimum year
     * @param {int}         upperboundyear  - maximum year
     * @param {HTMLElement} button          - the button that opened the picker
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
         * Close the date picker when you click outside the popup.
         *
         * @param {MouseEvent} e - event click
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
     * Render the 12-month grid with navigation by year.
     *
     * @param {HTMLElement} container       - DOM container
     * @param {int}         year            - year to display
     * @param {int}         selectedMonth   - currently selected month
     * @param {int}         lowerboundmonth - minimum month
     * @param {int}         upperboundmonth - maximum month
     * @param {int}         lowerboundyear  - minimum year
     * @param {int}         upperboundyear  - maximum year
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

        // Months grid.
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

            // A month is valid if:
            // - is not the minimum year, or is >= lowerboundmonth
            // - it is not in the maximum year, or it is <= upperboundmonth
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
     * Set the value of a dropdown by selecting the corresponding option.
     *
     * @param {HTMLElement} select - select to update
     * @param {int}         value  - value to set
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
         * Initialize the date picker for a shortdate field.
         *
         * @param {Object} params
         * @param {string} params.baseid          - base id of the group of select
         * @param {int}    params.lowerboundmonth - minimum month
         * @param {int}    params.upperboundmonth - maximum month
         * @param {int}    params.lowerboundyear  - minimum year
         * @param {int}    params.upperboundyear  - maximum year
         */
        init: function(params) {
            addDateButton(params.baseid, params.lowerboundmonth, params.upperboundmonth,
                          params.lowerboundyear, params.upperboundyear);
        }
    };
});
