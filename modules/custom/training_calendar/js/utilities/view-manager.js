/**
 * @file
 */
(function(Drupal, $, _)
{
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
                weekNumberTitle: 'WEEK',
                /*fixedWeekCount: 2,*/
                /*dayCount: 14,*/
                events: Drupal.trainingCalendar.Utilities.ViewManager.getCalendarEvents,
                defaultView: 'month',
                header: {
                    left: 'month newTrainingButton',
                    center: 'title',
                    right: 'today prev,next'
                },
                customButtons: {
                    newTrainingButton: {
                        text: '+Training',
                        click: function()
                        {
                            alert('Adding new training!');
                        }
                    }
                },
                eventClick: function(calEvent, jsEvent, view)
                {
                    alert('Event: ' + calEvent.title);
                    //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
                    //alert('View: ' + view.name);
                    //$(this).css('border-color', 'red');
                }
            });
        },

        /**
         * Main EventData call to populate calendar with data called by FullCalendar
         * @see: https://fullcalendar.io/docs/event-data
         *
         *
         * @param start
         * @param end
         * @param timezone
         * @param callback
         */
        getCalendarEvents: function(start, end, timezone, callback)
        {
            let answer = [];

            let fetchParams = {
                start_date: moment(start),
                end_date: moment(end),
                timezone: timezone,
            };
            console.warn("Getting events for calendar["+fetchParams.start_date.format()+" --- "+fetchParams.end_date.format()+"]("+fetchParams.timezone+")");

            Drupal.trainingCalendar.Utilities.ModelManager.fetchCalendarEvents(fetchParams).then(function(collection)
            {
                console.warn("GOT TRAININGS - updating calendar");
                let events = createCalendarEventsFromCollection(collection);
                callback(events);
            }).catch(function(e)
            {
                console.error(e);
                callback(answer);
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

    /**
     *
     * @param {Drupal.trainingCalendar.TrainingModels} collection
     * @return {Array}
     */
    let createCalendarEventsFromCollection = function(collection)
    {
        let answer = [];
        collection.each(function(training) {
            answer.push(createCalendarEventFromModel(training));
        });

        return answer;
    };

    /**
     *
     * @param {Drupal.trainingCalendar.TrainingModel} training
     * @return {{}}
     */
    let createCalendarEventFromModel = function(training)
    {
        let event = {};
        event.id = training.id;
        event.title = training.get("title");
        event.start = training.get("field_start_date");
        //event.end = training.get("end");
        event.className = ['event', 'generic'];
        event.overlap = false;
        event.allDay = true;
        event.editable = true;

        return event;
    };


})(Drupal, jQuery, _);
