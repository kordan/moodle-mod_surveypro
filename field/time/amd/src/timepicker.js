// file: amd/src/timepicker.js

define(['core/str'], function(Str) {

    /**
     * Adds a time picker button next to the minute dropdown.
     *
     * @param {Object} params
     * @param {string} params.baseid           - es. "id_field_time_3"
     * @param {int}    params.lowerboundhour   - minimum hour
     * @param {int}    params.upperboundhour   - maximum hour
     * @param {int}    params.lowerboundminute - minimum minute
     * @param {int}    params.upperboundminute - maximum minute
     * @param {int}    params.step             - minutes step
     * @param {int}    params.wraparound       - 1 if the time span crosses midnight
     */
    function addTimeButton(params) {
        const selectHour   = document.getElementById(params.baseid + '_hour');
        const selectMinute = document.getElementById(params.baseid + '_minute');

        if (!selectHour || !selectMinute) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-link btn-sm icon-no-margin ms-1';
        button.setAttribute('aria-label', 'Time picker');
        button.setAttribute('title', 'Time picker');
        button.innerHTML = '<i class="icon fa-regular fa-clock fa-fw" aria-hidden="true"></i>';

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
            openTimepicker(selectHour, selectMinute, params, button);
        });
    }

    /**
     * Opens the popup with the circular clock.
     *
     * @param {HTMLElement} selectHour   - elect for hours
     * @param {HTMLElement} selectMinute - elect for minutes
     * @param {Object}      params       - parameters of the timepicker
     * @param {HTMLElement} button       - the button that opened the picker
     */
    function openTimepicker(selectHour, selectMinute, params, button) {

        const existingPicker = document.getElementById('surveypro-timepicker-popup');
        if (existingPicker) {
            existingPicker.remove();
            return;
        }

        const currentHour   = parseInt(selectHour.value)   || params.lowerboundhour;
        const currentMinute = parseInt(selectMinute.value) || params.lowerboundminute;

        const popup = document.createElement('div');
        popup.id = 'surveypro-timepicker-popup';
        popup.style.cssText = 'position:fixed; z-index:9999; background:#fff; ' +
                              'border:1px solid #ccc; border-radius:8px; padding:12px; ' +
                              'box-shadow:0 2px 8px rgba(0,0,0,0.2); text-align:center; width:220px;';

        popup.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Start by selecting the time.
        renderClockHour(popup, currentHour, currentMinute, params,
            function(hour, minute) {
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
         * Closes the date picker when you click outside the popup.
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
     * Generates the list of valid hours based on the parameters.
     *
     * @param {Object} params - parameters of the timepicker
     * @returns {Array}       - array of integers with valid hours
     */
    function getValidHours(params) {
        const hours = [];
        if (params.wraparound) {
            for (let i = params.lowerboundhour; i <= 24; i++) {
                hours.push(i);
            }
            for (let i = 1; i <= params.upperboundhour; i++) {
                hours.push(i);
            }
        } else {
            for (let i = params.lowerboundhour; i <= params.upperboundhour; i++) {
                hours.push(i);
            }
        }
        return hours;
    }

    /**
     * Check whether a time is valid based on the parameters.
     *
     * @param {int}    hour   - hour to check
     * @param {Object} params - parameters of the timepicker
     * @returns {boolean}
     */
    function isHourValid(hour, params) {
        return getValidHours(params).indexOf(hour) !== -1;
    }

    /**
     * Check if a minute is valid for a given hour.
     *
     * @param {int}    hour   - hour to check
     * @param {int}    minute - minute to check
     * @param {Object} params - parameters of the timepicker
     * @returns {boolean}
     */
    function isMinuteValid(hour, minute, params) {
        if (params.lowerboundhour === params.upperboundhour) {
            return minute >= params.lowerboundminute && minute <= params.upperboundminute;
        }
        if (hour === params.lowerboundhour) {
            return minute >= params.lowerboundminute;
        }
        if (hour === params.upperboundhour) {
            return minute <= params.upperboundminute;
        }
        return true;
    }

    /**
     * Render the clock for selecting the time.
     *
     * @param {HTMLElement} container      - container DOM element
     * @param {int}         selectedHour   - currently selected hour
     * @param {int}         selectedMinute - currently selected minute
     * @param {Object}      params         - parameters of the timepicker
     * @param {Function}    onSelect       - callback(hour, minute)
     */
    function renderClockHour(container, selectedHour, selectedMinute, params, onSelect) {
        container.innerHTML = '';

        const title = document.createElement('div');
        title.style.cssText = 'font-weight:bold; margin-bottom:8px; font-size:0.9em;';
        Str.get_string('choosethehour', 'surveyprofield_time').then(function(s) {
            title.textContent = s;
        }).catch(function() {
            title.textContent = 'Choose an hour';
        });
        container.appendChild(title);

        // Display ora:minuti correnti.
        const display = document.createElement('div');
        display.style.cssText = 'font-size:1.6em; font-weight:bold; margin-bottom:8px; ' +
                                'letter-spacing:2px; color:#0f6cbf;';
        display.textContent = ('0' + selectedHour).slice(-2) + ':' + ('0' + selectedMinute).slice(-2);
        container.appendChild(display);

        const svg = renderClockFace(selectedHour, null, params, function(hour) {
            renderClockMinute(container, hour, selectedMinute, params, onSelect);
        }, 'hour');

        container.appendChild(svg);
    }

    /**
     * Render the clock for selecting the minutes.
     *
     * @param {HTMLElement} container      - container DOM element
     * @param {int}         selectedHour   - currently selected hour
     * @param {int}         selectedMinute - currently selected minute
     * @param {Object}      params         - parameters of the timepicker
     * @param {Function}    onSelect       - callback(hour, minute)
     */
    function renderClockMinute(container, selectedHour, selectedMinute, params, onSelect) {
        container.innerHTML = '';

        const title = document.createElement('div');
        title.style.cssText = 'font-weight:bold; margin-bottom:8px; font-size:0.9em;';
        Str.get_string('choosetheminute', 'surveyprofield_time').then(function(s) {
            title.textContent = s;
        }).catch(function() {
            title.textContent = 'Choose a minute';
        });
        container.appendChild(title);

        const display = document.createElement('div');
        display.style.cssText = 'font-size:1.6em; font-weight:bold; margin-bottom:8px; ' +
                                'letter-spacing:2px; color:#0f6cbf;';
        display.textContent = ('0' + selectedHour).slice(-2) + ':' + ('0' + selectedMinute).slice(-2);
        container.appendChild(display);

        const svg = renderClockFace(selectedMinute, selectedHour, params, function(minute) {
            onSelect(selectedHour, minute);
        }, 'minute');

        container.appendChild(svg);

        // Back button to return to the time selection screen.
        const backBtn = document.createElement('button');
        backBtn.type = 'button';
        backBtn.className = 'btn btn-sm btn-secondary mt-2';
        Str.get_string('backtohour', 'surveyprofield_time').then(function(s) {
            backBtn.textContent = '‹ ' + s;
        }).catch(function() {
            backBtn.textContent = 'Back to hour';
        });
        backBtn.addEventListener('click', function() {
            renderClockHour(container, selectedHour, selectedMinute, params, onSelect);
        });
        container.appendChild(backBtn);
    }

    /**
     * Render the clock's SVG face.
     *
     * @param {int}      selected - currently selected value (hour or minute)
     * @param {int|null} hour     - current time (null if we are selecting the time)
     * @param {Object}   params   - parameters of the timepicker
     * @param {Function} onPick   - callback(value) al click su un numero
     * @param {string}   mode     - 'hour' o 'minute'
     * @returns {SVGElement}
     */
    function renderClockFace(selected, hour, params, onPick, mode) {
        const size   = 180;
        const cx     = size / 2;
        const cy     = size / 2;
        const radius = 70;

        const svgNS = 'http://www.w3.org/2000/svg';
        const svg   = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('width',  size);
        svg.setAttribute('height', size);
        svg.setAttribute('viewBox', '0 0 ' + size + ' ' + size);

        // Background circle.
        const bg = document.createElementNS(svgNS, 'circle');
        bg.setAttribute('cx', cx);
        bg.setAttribute('cy', cy);
        bg.setAttribute('r',  cx - 4);
        bg.setAttribute('fill', '#f8f9fa');
        bg.setAttribute('stroke', '#dee2e6');
        bg.setAttribute('stroke-width', '1');
        svg.appendChild(bg);

        // Numbers to place on the dial.
        let items = [];
        if (mode === 'hour') {
            items = getValidHours(params);
        } else {
            for (let m = 0; m <= 59; m += params.step) {
                items.push(m);
            }
        }

        const total = items.length;
        items.forEach(function(value, index) {
            const angle = (index / total) * 2 * Math.PI - Math.PI / 2;
            const x = cx + radius * Math.cos(angle);
            const y = cy + radius * Math.sin(angle);

            const valid = (mode === 'hour')
                ? isHourValid(value, params)
                : isMinuteValid(hour, value, params);

            // Background circle for the selected number.
            if (value === selected) {
                const selCircle = document.createElementNS(svgNS, 'circle');
                selCircle.setAttribute('cx', x);
                selCircle.setAttribute('cy', y);
                selCircle.setAttribute('r',  13);
                selCircle.setAttribute('fill', '#0f6cbf');
                svg.appendChild(selCircle);
            }

            // Call the selected number.
            if (value === selected) {
                const line = document.createElementNS(svgNS, 'line');
                line.setAttribute('x1', cx);
                line.setAttribute('y1', cy);
                line.setAttribute('x2', x);
                line.setAttribute('y2', y);
                line.setAttribute('stroke', '#0f6cbf');
                line.setAttribute('stroke-width', '2');
                svg.appendChild(line);
            }

            // Text for the number.
            const text = document.createElementNS(svgNS, 'text');
            text.setAttribute('x', x);
            text.setAttribute('y', y);
            text.setAttribute('text-anchor', 'middle');
            text.setAttribute('dominant-baseline', 'central');
            text.setAttribute('font-size', '11');
            text.setAttribute('fill', value === selected ? '#fff' : (valid ? '#333' : '#ccc'));
            text.setAttribute('cursor', valid ? 'pointer' : 'not-allowed');
            text.textContent = ('0' + value).slice(-2);
            svg.appendChild(text);

            // Transparent clickable area above the text.
            if (valid) {
                const hit = document.createElementNS(svgNS, 'circle');
                hit.setAttribute('cx', x);
                hit.setAttribute('cy', y);
                hit.setAttribute('r',  13);
                hit.setAttribute('fill', 'transparent');
                hit.setAttribute('cursor', 'pointer');
                hit.addEventListener('click', function() {
                    onPick(value);
                });
                svg.appendChild(hit);
            }
        });

        // Central dot.
        const dot = document.createElementNS(svgNS, 'circle');
        dot.setAttribute('cx', cx);
        dot.setAttribute('cy', cy);
        dot.setAttribute('r',  3);
        dot.setAttribute('fill', '#0f6cbf');
        svg.appendChild(dot);

        return svg;
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
         * Initialize the timepicker for a SurveyPro time field.
         *
         * @param {Object} params
         * @param {string} params.baseid           - base id of the group of select
         * @param {int}    params.lowerboundhour   - minimun hour
         * @param {int}    params.upperboundhour   - maximum hour
         * @param {int}    params.lowerboundminute - minimun minute
         * @param {int}    params.upperboundminute - maximum minute
         * @param {int}    params.step             - minutes step
         * @param {int}    params.wraparound       - 1 if the time span crosses midnight
         */
        init: function(params) {
            addTimeButton(params);
        }
    };
});
