/**
 * @file
 */
(function(Backbone, Drupal, $, _)
{
    let $overlayDiv = $('.training-calendar-overlay');

    let $trainingCalendarApp = $('#training-calendar-main');
    let $trainingCalendarDiv = $('.tc-calendar', $trainingCalendarApp);
    let $trainingCalendarModal = $('.tc-modals', $trainingCalendarApp);

    /* The full calenda event that was clicked to activate the edit view */
    let eventBeingEdited;

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
                    console.log('Clicked Event('+calEvent.id+'): ' + calEvent.title);
                    eventBeingEdited = calEvent;

                    let viewOptions = {};

                    let training = Drupal.trainingCalendar.Utilities.ModelManager.getTrainingById(calEvent.id);
                    if(training){
                        viewOptions.model = training;
                    }

                    let TrainingEditModalView = new Drupal.trainingCalendar.TrainingEdit(viewOptions);
                    $trainingCalendarModal.html(TrainingEditModalView.render().el);

                    //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
                    //alert('View: ' + view.name);
                    //$(this).css('border-color', 'red');
                }
            });
        },

        /**
         * event needs to be aupdated from model first
         * @todo: fix this and use this in favor of refetchEventsForCurrentView
         */
        updateEditedEvent: function()
        {
            //console.warn(eventBeingEdited);
            //$trainingCalendarDiv.fullCalendar('updateEvent', eventBeingEdited);
        },

        refetchEventsForCurrentView: function()
        {
            $trainingCalendarDiv.fullCalendar('refetchEvents');
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

            console.info("Requesting trainings for calendar ["+fetchParams.start_date.format()+" : "+fetchParams.end_date.format()+"]");

            Drupal.trainingCalendar.Utilities.ModelManager.fetchTrainings(fetchParams).then(function(data)
            {
                console.info("Got trainings for period: ", data.length);
                let events = createCalendarEventsFromModels(data);
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

    //---------------------------------------------------------------------------------------------------PRIVATE METHODS

    /**
     * @todo: move this to collection
     * @param {Array} data - The full collection
     * @return {Array}
     */
    let createCalendarEventsFromModels = function(data)
    {
        let answer = [];

        _.each(data, function(training) {
            //answer.push(createCalendarEventFromModel(training));
            answer.push(training.getCalendarEventData());
        });

        return answer;
    };


})(Backbone, Drupal, jQuery, _);
