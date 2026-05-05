// File: amd/src/datepicker.js

define([], function() {

    /**
     * Adds a date picker button next to the month dropdown.
     *
     * @param {string} baseid          - es. "id_field_recurrence_3"
     * @param {int}    lowerboundday   - minimum day
     * @param {int}    upperboundday   - maximum day
     * @param {int}    lowerboundmonth - minimum month
     * @param {int}    upperboundmonth - maximum month
     * @param {int}    wraparound      - 1 if the period spans the new year
     */
    function addCalendarButton(baseid, lowerboundday, upperboundday,
                               lowerboundmonth, upperboundmonth, wraparound) {

        const selectDay = document.getElementById(baseid + '_day');
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
     * Opens the pop-up window with the monthly calendar.
     *
     * @param {HTMLElement} selectDay       - select of the day
     * @param {HTMLElement} selectMonth     - select of the month
     * @param {int}         lowerboundday   - minimum day
     * @param {int}         upperboundday   - maximum day
     * @param {int}         lowerboundmonth - minimum month
     * @param {int}         upperboundmonth - maximum month
     * @param {int}         wraparound      - 1 if the period spans the new year
     * @param {HTMLElement} button          - the button that opened the picker
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

        const currentDay = parseInt(selectDay.value) || lowerboundday;
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
                setSelectValue(selectDay, day);
                setSelectValue(selectMonth, month);
                popup.remove();
                document.removeEventListener('click', closePicker);
            }
        );

        document.body.appendChild(popup);
        const rect = button.getBoundingClientRect();
        popup.style.top = (rect.bottom + 4) + 'px';
        popup.style.left = rect.left + 'px';

        /**
         * Closes the date picker when you click outside the popup.
         *
         * @param {MouseEvent} e - click event
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
     * Generate a list of valid months based on the parameters.
     *
     * @param {int} lowerboundmonth - minimum month
     * @param {int} upperboundmonth - maximum month
     * @param {int} wraparound      - 1 if the period spans the new year
     * @returns {Array}             - array of integers containing the valid months
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
     * Render the monthly grid without the year.
     *
     * @param {HTMLElement} container       - container DOM element
     * @param {int}         month           - month to display (1–12)
     * @param {int}         selectedDay     - currently selected day
     * @param {int}         lowerboundday   - minimum day
     * @param {int}         upperboundday   - maximum day
     * @param {int}         lowerboundmonth - minimum month
     * @param {int}         upperboundmonth - maximum month
     * @param {int}         wraparound      - 1 if the period spans the new year
     * @param {Function}    onSelect        - callback(day, month)
     */
    function renderCalendar(container, month, selectedDay,
                            lowerboundday, upperboundday,
                            lowerboundmonth, upperboundmonth,
                            wraparound, onSelect) {
        container.innerHTML = '';

        const validMonths = getValidMonths(lowerboundmonth, upperboundmonth, wraparound);
        const currentIndex = validMonths.indexOf(month);

        // Header with month navigation.
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
            .toLocaleDateString(document.documentElement.lang || 'en', {month: 'long'});

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

        // Days grid.
        const grid = document.createElement('div');
        grid.style.cssText = 'display:grid; grid-template-columns:repeat(7,32px); ' +
                             'gap:2px; text-align:center;';

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

        // Let's use a non-leap year as the reference year for February.
        const refYear = 2001;
        const firstDay = new Date(refYear, month - 1, 1).getDay();
        const offset = (firstDay === 0) ? 6 : firstDay - 1;
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

            // A day is valid if:
            // - the month is not the first valid month, or day >= lowerboundday
            // - the month is not the last valid one, or day <= upperboundday
            const isFirstMonth = (month === lowerboundmonth);
            const isLastMonth = (month === upperboundmonth);
            const validDay = (!isFirstMonth || day >= lowerboundday) &&
                             (!isLastMonth || day <= upperboundday);

            if (!validDay) {
                cell.disabled = true;
                cell.style.color = '#ccc';
                cell.style.cursor = 'not-allowed';
            } else if (day === selectedDay) {
                cell.style.background = '#0f6cbf';
                cell.style.color = '#fff';
                cell.addEventListener('click', function() {
 onSelect(day, month);
});
            } else {
                cell.style.background = '#f8f9fa';
                cell.addEventListener('click', function() {
 onSelect(day, month);
});
            }

            grid.appendChild(cell);
        }

        container.appendChild(grid);
    }

    /**
     * Set the value of a dropdown by selecting the corresponding option.
     *
     * @param {HTMLElement} select - select element to update
     * @param {int}         value  - value to select
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
         * Initialize the date picker for a recurrence field.
         *
         * @param {Object} params
         * @param {string} params.baseid          - base id of the group of select
         * @param {int}    params.lowerboundday   - minimum day
         * @param {int}    params.upperboundday   - maximum day
         * @param {int}    params.lowerboundmonth - minimum month
         * @param {int}    params.upperboundmonth - maximum month
         * @param {int}    params.wraparound      - 1 if it spans the new year
         */
        init: function(params) {
            addCalendarButton(params.baseid,
                              params.lowerboundday, params.upperboundday,
                              params.lowerboundmonth, params.upperboundmonth,
                              params.wraparound);
        }
    };
});
