// File: amd/src/datepicker.js

define([], function() {

    /**
     * Adds a calendar button next to the select group.
     *
     * @param {string} baseid   - es. "id_field_date_3"
     * @param {int}    mindate  - Unix timestamp of the minimum date/time
     * @param {int}    maxdate  - Unix timestamp of the maximum date/time
     */
    function addCalendarButton(baseid, mindate, maxdate) {

        const selectDay   = document.getElementById(baseid + '_day');
        const selectMonth = document.getElementById(baseid + '_month');
        const selectYear  = document.getElementById(baseid + '_year');

        if (!selectDay || !selectMonth || !selectYear) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-link btn-sm icon-no-margin ms-1';
        button.setAttribute('aria-label', 'Apri calendario');
        button.innerHTML = '<i class="icon fa-regular fa-calendar fa-fw" aria-hidden="true"></i>';

        // Inserts the button inside the span element that contains the year select.
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
            openDatepicker(selectDay, selectMonth, selectYear, mindate, maxdate, button);
        });
    }

    /**
     * Opens a small inline calendar below the button.
     *
     * @param {HTMLElement} selectDay   - select for the day
     * @param {HTMLElement} selectMonth - select for the month
     * @param {HTMLElement} selectYear  - select for the year
     * @param {int}         mindate     - unix timestamp of the minimum date
     * @param {int}         maxdate     - unix timestamp of the maximum date
     * @param {HTMLElement} button      - the button that opened the picker
     */
    function openDatepicker(selectDay, selectMonth, selectYear, mindate, maxdate, button) {

        const existingPicker = document.getElementById('surveypro-datepicker-popup');
        if (existingPicker) {
            existingPicker.remove();
            return;
        }

        const currentDay   = parseInt(selectDay.value)   || 1;
        const currentMonth = parseInt(selectMonth.value) || 1;
        const currentYear  = parseInt(selectYear.value)  || new Date().getFullYear();

        const popup = document.createElement('div');
        popup.id = 'surveypro-datepicker-popup';
        popup.style.cssText = 'position:fixed; z-index:9999; background:#fff; ' +
                              'border:1px solid #ccc; border-radius:4px; padding:8px; ' +
                              'box-shadow:0 2px 8px rgba(0,0,0,0.2);';

        // Block the propagation of ALL clicks within the popup.
        popup.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        renderCalendar(popup, currentYear, currentMonth, currentDay, mindate, maxdate,
            function(day, month, year) {
                setSelectValue(selectDay,   day);
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
     * Render the monthly grid inside a container.
     *
     * @param {HTMLElement} container   - DOM element that will contain the calendar
     * @param {int}         year        - year to display
     * @param {int}         month       - month to display (1–12)
     * @param {int}         selectedDay - currently selected day
     * @param {int}         mindate     - unix timestamp of the minimum date
     * @param {int}         maxdate     - unix timestamp of the maximum date
     * @param {Function}    onSelect    - callback(day, month, year) alla selezione
     */
    function renderCalendar(container, year, month, selectedDay, mindate, maxdate, onSelect) {
        container.innerHTML = '';

        const minDateObj = new Date(mindate * 1000);
        const maxDateObj = new Date(maxdate * 1000);

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

        const prevMonth = month === 1 ? 12 : month - 1;
        const prevYear = month === 1 ? year - 1 : year;
        const nextMonth = month === 12 ? 1 : month + 1;
        const nextYear = month === 12 ? year + 1 : year;

        const prevFirstDay = new Date(prevYear, prevMonth - 1, 1);
        const minFirstDay  = new Date(minDateObj.getFullYear(), minDateObj.getMonth(), 1);
        const nextFirstDay = new Date(nextYear, nextMonth - 1, 1);
        const maxFirstDay  = new Date(maxDateObj.getFullYear(), maxDateObj.getMonth(), 1);

        const prevDisabled = prevFirstDay < minFirstDay;
        const nextDisabled = nextFirstDay > maxFirstDay;

        prevBtn.disabled = prevDisabled;
        nextBtn.disabled = nextDisabled;

        title.textContent = new Date(year, month - 1, 1)
            .toLocaleDateString(document.documentElement.lang || 'en', {month: 'long', year: 'numeric'});

        prevBtn.addEventListener('click', function() {
            renderCalendar(container, prevYear, prevMonth, 0, mindate, maxdate, onSelect);
        });
        nextBtn.addEventListener('click', function() {
            renderCalendar(container, nextYear, nextMonth, 0, mindate, maxdate, onSelect);
        });

        header.appendChild(prevBtn);
        header.appendChild(title);
        header.appendChild(nextBtn);
        container.appendChild(header);

        const grid = document.createElement('div');
        grid.style.cssText = 'display:grid; grid-template-columns:repeat(7,32px); gap:2px; text-align:center;';

        const lang = document.documentElement.lang || 'en';
        const dayNames = [];
        for (let d = 0; d < 7; d++) {
            // 2023-01-02 è un lunedì - partiamo da lunedì
            dayNames.push(new Date(2023, 0, 2 + d)
                .toLocaleDateString(lang, {weekday: 'narrow'}));
        }
        dayNames.forEach(function(d) {
            const cell = document.createElement('div');
            cell.textContent = d;
            cell.style.cssText = 'font-weight:bold; font-size:0.8em; padding:2px;';
            grid.appendChild(cell);
        });

        const firstDay = new Date(year, month - 1, 1).getDay();
        const offset = (firstDay === 0) ? 6 : firstDay - 1;
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
            const minDay = new Date(minDateObj.getFullYear(), minDateObj.getMonth(), minDateObj.getDate());
            const maxDay = new Date(maxDateObj.getFullYear(), maxDateObj.getMonth(), maxDateObj.getDate());

            if (thisDate < minDay || thisDate > maxDay) {
                cell.disabled = true;
                cell.style.color = '#ccc';
                cell.style.cursor = 'not-allowed';
            } else if (day === selectedDay) {
                cell.style.background = '#0f6cbf';
                cell.style.color = '#fff';
                cell.addEventListener('click', function() {
                    onSelect(day, month, year);
                });
            } else {
                cell.style.background = '#f8f9fa';
                cell.addEventListener('click', function() {
                    onSelect(day, month, year);
                });
            }

            grid.appendChild(cell);
        }

        container.appendChild(grid);
    }

    /**
     * Set the value of a dropdown by selecting the corresponding option.
     *
     * @param {HTMLElement} select - select to update
     * @param {int}         value  - valore to set
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
         * Initialize the date picker for a surveypro date field.
         *
         * @param {Object} params
         * @param {string} params.baseid  - base id for the group of select
         * @param {int}    params.mindate - unix timestamp of the minimim date
         * @param {int}    params.maxdate - unix timestamp of the maximum date
         */
        init: function(params) {
            addCalendarButton(params.baseid, params.mindate, params.maxdate);
        }
    };
});
