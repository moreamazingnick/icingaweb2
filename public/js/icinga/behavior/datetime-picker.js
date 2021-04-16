/* Icinga Web 2 | (c) 2021 Icinga GmbH | GPLv2+ */

/**
 * DatetimePicker - Behavior for inputs that should show a date and time picker
 */
;(function(Icinga, $) {

    'use strict';

    try {
        var Flatpickr = require('icinga/ipl/vendor/flatpickr');
        var notjQuery = require('icinga/ipl/notjQuery');
    } catch (e) {
        console.warn('Unable to provide datetime picker. Libraries not available:', e);
        return;
    }

    Icinga.Behaviors = Icinga.Behaviors || {};

    /**
     * Behavior for datetime pickers.
     *
     * @param icinga {Icinga} The current Icinga Object
     */
    var DatetimePicker = function(icinga) {
        Icinga.EventListener.call(this, icinga);
        this.icinga = icinga;

        /**
         * The formats the server expects
         *
         * In a syntax flatpickr understands. Based on https://flatpickr.js.org/formatting/
         *
         * @type {string}
         */
        this.server_full_format = 'Y-m-d\\TH:i:S';
        this.server_date_format = 'Y-m-d';
        this.server_time_format = 'H:i:S';

        /**
         * The flatpickr instances created
         *
         * @type {Map<Flatpickr, string>}
         * @private
         */
        this._pickers = new Map();

        this.on('rendered', this.onRendered, this);
        this.on('close-column', this.onCloseContainer, this);
        this.on('close-modal', this.onCloseContainer, this);
    };

    DatetimePicker.prototype = new Icinga.EventListener();

    /**
     * Add flatpickr widget on selected inputs
     *
     * @param event {Event}
     */
    DatetimePicker.prototype.onRendered = function(event) {
        var _this = event.data.self;
        var containerId = event.target.dataset.icingaContainerId;
        var inputs = event.target.querySelectorAll('input[data-use-datetime-picker]');

        // Cleanup left-over pickers from the previous content
        _this.cleanupPickers(containerId);

        $.each(inputs, function () {
            var server_format = _this.server_full_format;
            if (this.type === 'date') {
                server_format = _this.server_date_format;
            } else if (this.type === 'time') {
                server_format = _this.server_time_format;
            }

            var enableTime = server_format !== _this.server_date_format;
            var disableDate = server_format === _this.server_time_format;
            var dateTimeFormatter = _this.createFormatter(! disableDate, enableTime);
            var options = {
                locale: _this.loadFlatpickrLocale(),
                appendTo: this.form.parentNode,
                altInput: true,
                enableTime: enableTime,
                noCalendar: disableDate,
                dateFormat: server_format,
                formatDate: function (date, format, locale) {
                    return format === this.dateFormat
                        ? Flatpickr.formatDate(date, format, locale)
                        : dateTimeFormatter.format(date);
                }
            };

            for (name in this.dataset) {
                if (name.length > 9 && name.substr(0, 9) === 'flatpickr') {
                    var value = this.dataset[name];
                    if (value === '') {
                        value = true;
                    }

                    options[name.charAt(9).toLowerCase() + name.substr(10)] = value;
                }
            }

            var fp = Flatpickr(this, options);
            fp.calendarContainer.classList.add('icinga-datetime-picker');
            this.parentNode.insertBefore(_this.renderIcon(), fp.altInput.nextSibling);

            _this._pickers.set(fp, containerId);
        });
    };

    /**
     * Cleanup all flatpickr instances in the closed container
     *
     * @param event {Event}
     */
    DatetimePicker.prototype.onCloseContainer = function (event) {
        var _this = event.data.self;
        var containerId = event.target.dataset.icingaContainerId;

        _this.cleanupPickers(containerId);
    };

    /**
     * Destroy all flatpickr instances in the container with the given id
     *
     * @param containerId {String}
     */
    DatetimePicker.prototype.cleanupPickers = function (containerId) {
        this._pickers.forEach(function (cId, fp) {
            if (cId === containerId) {
                this._pickers.delete(fp);
                fp.destroy();
            }
        }, this);
    };

    DatetimePicker.prototype.createFormatter = function (withDate, withTime) {
        var options = {};
        if (withDate) {
            options.year = 'numeric';
            options.month = 'numeric';
            options.day = 'numeric';
        }
        if (withTime) {
            options.hour = 'numeric';
            options.minute = 'numeric';
            options.timeZoneName = 'short';
            options.timeZone = this.icinga.config.timezone;
        }

        return new Intl.DateTimeFormat([this.icinga.config.locale, 'en'], options);
    };

    DatetimePicker.prototype.loadFlatpickrLocale = function () {
        switch (this.icinga.config.locale) {
            case 'ar':
                return require('icinga/ipl/vendor/flatpickr/l10n/ar').Arabic;
            case 'de':
                return require('icinga/ipl/vendor/flatpickr/l10n/de').German;
            case 'es':
                return require('icinga/ipl/vendor/flatpickr/l10n/es').Spanish;
            case 'fi':
                return require('icinga/ipl/vendor/flatpickr/l10n/fi').Finnish;
            case 'it':
                return require('icinga/ipl/vendor/flatpickr/l10n/it').Italian;
            case 'ja':
                return require('icinga/ipl/vendor/flatpickr/l10n/ja').Japanese;
            case 'pt':
                return require('icinga/ipl/vendor/flatpickr/l10n/pt').Portuguese;
            case 'ru':
                return require('icinga/ipl/vendor/flatpickr/l10n/ru').Russian;
            case 'uk':
                return require('icinga/ipl/vendor/flatpickr/l10n/uk').Ukrainian;
            default:
                return 'default';
        }
    };

    DatetimePicker.prototype.renderIcon = function () {
        return notjQuery.render('<i class="icon fa fa-calendar" role="image"></i>');
    };

    Icinga.Behaviors.DatetimePicker = DatetimePicker;

})(Icinga, jQuery);
