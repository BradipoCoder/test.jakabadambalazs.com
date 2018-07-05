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
                height: "auto",
                weekends: true,
                firstDay: 1,
                showNonCurrentDates: true,
                weekNumbers: true,
                events: Drupal.trainingCalendar.Utilities.ModelManager.getCalendarEvents,
                defaultView: 'month',
                header: {
                    left: 'month training_calendar_view newTrainingButton',
                    center: 'title',
                    right: 'today prev,next'
                },
                customButtons: {
                    newTrainingButton: {
                        text: '+Training',
                        click: function() {
                            alert('Adding new training!');
                        }
                    }
                },
                eventClick: function(calEvent, jsEvent, view) {
                    alert('Event: ' + calEvent.title);
                    //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
                    //alert('View: ' + view.name);
                    //$(this).css('border-color', 'red');
                }
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

    let FC = $.fullCalendar;
    let FCView = FC.View;
    let FCBasicView = FC.BasicView;
    let FCMonthView = FC.MonthView;

    let TrainingCalendarView = FCMonthView.extend({

        _initialize: function() {
            console.log("CUSTOM VIEW INIT!");


            //FCView.prototype.initialize.apply(this, arguments);
        },

    });

    FC.views.training_calendar_view = TrainingCalendarView; // register our class with the view system



})(Drupal, jQuery, _);
