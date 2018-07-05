/**
 * @file
 */
(function (Drupal, $, _) {
    let $overlayDiv = $('.training-calendar-overlay');
    let $trainingCalendarDiv = $('#training-calendar');
    /**
     * Utility class for views and interface
     *
     * @type {{}}
     */
    Drupal.trainingCalendar.Utilities.ViewManager = {

        /**
         * Initialize
         * @return {Promise<any>}
         */
        init: function()
        {
            return new Promise(function(resolve)
            {
                let self = Drupal.trainingCalendar.Utilities.ViewManager;
                //init
                self.setupCalendar();
                //
                resolve("ViewManager initialized.");
            });
        },

        updateCalendarEvents: function()
        {
            $trainingCalendarDiv.fullCalendar('refetchEvents');
        },

        setupCalendar: function()
        {
            $trainingCalendarDiv.fullCalendar({
                weekends: true,
                defaultView: 'month',
                showNonCurrentDates: true,
                weekNumbers: true,
                events: Drupal.trainingCalendar.Utilities.ModelManager.getCalendarEvents,
            });
        },

        overlayHide: function()
        {
            $overlayDiv.delay(500).fadeOut(500);
        },

        overlayShow: function()
        {
            $overlayDiv.delay(500).fadeIn(500);
        }
    };
})(Drupal, jQuery, _);
