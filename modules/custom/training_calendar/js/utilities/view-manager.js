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
                defaultView: 'month',
                header: {
                    left: 'month newTrainingButton',
                    center: 'title',
                    right: 'today prev,next'
                },
                customButtons: {
                    newTrainingButton: {
                        text: '+Training',
                        click: fullCalendarCreateNewEvent
                    }
                },
                events: fullCalendarGetEvents,
                eventClick: fullCalendarEventClick,
                eventDrop: fullCalendarEventDrop,
                eventRender: fullCalendarEventRender,
                dayClick: fullCalendarDayClick,
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
    let fullCalendarEventRender = function(event, element)
    {
        //element.addClass("custom-ev-class");
        let $eventContent = $('.fc-content', element);

        let activityImageUrl = Drupal.url("modules/custom/training_calendar/images/activity_type/"+event.field_activity_type +".png");

        //DISTANCE
        $eventContent.html("");
        $eventContent.append("<div class='fc-activity-type'><img src='"+activityImageUrl+"'/></div>");
        $eventContent.append("<div class='fc-title'>" + event.title + "</div>");
        $eventContent.append("<div class='fc-distance'>" + event.distance_km + "Km</div>");
    };

    let fullCalendarEventDrop = function(calEvent, delta, revertFunc, jsEvent, ui, view)
    {
        console.log('Dropped Event(' + calEvent.id + '): ' + calEvent.title + ' to date: ' + calEvent.start.format());
        let training = Drupal.trainingCalendar.Utilities.ModelManager.getTrainingById(calEvent.id);
        if(!training) {
            console.error('No model found for calendar event!');
            revertFunc.call();
            return;
        }

        eventBeingEdited = calEvent;
        training.set("field_start_date", calEvent.start);
        console.log('Model date modified: ', training.get("field_start_date").format());

        let saveOptions = {
            success: function(model, response, options)
            {
                console.info('Calendar event was saved with new date!');
            },
            error: function(model, response, options)
            {
                console.error('Error saving calendar event!');
                revertFunc.call();
            }
        };
        training.save(null, saveOptions);
    };

    let fullCalendarEventClick = function(calEvent, jsEvent, view)
    {
        console.log('Clicked Event(' + calEvent.id + '): ' + calEvent.title);
        let training = Drupal.trainingCalendar.Utilities.ModelManager.getTrainingById(calEvent.id);
        if(!training) {
            console.error('No model found for calendar event!');
            return;
        }

        eventBeingEdited = calEvent;

        let viewOptions = {
            model: training,
        };


        let TrainingEditModalView = new Drupal.trainingCalendar.TrainingEdit(viewOptions);
        $trainingCalendarModal.html(TrainingEditModalView.render().el);
    };

    let fullCalendarCreateNewEvent = function()
    {
        console.log('Creating New Event');
        let trainingData = {
            title: '',
            field_start_date: moment(),
        };

        let training = Drupal.trainingCalendar.Utilities.ModelManager.getNewTrainingModel(trainingData);

        let viewOptions = {
            model: training,
        };

        let TrainingEditModalView = new Drupal.trainingCalendar.TrainingEdit(viewOptions);
        $trainingCalendarModal.html(TrainingEditModalView.render().el);
    };

    let fullCalendarDayClick = function(clickedDate, jsEvent, view)
    {
        console.log('Clicked on day: ' + clickedDate.format());

        let trainingData = {
            title: '',
            field_start_date: clickedDate,
        };

        let training = Drupal.trainingCalendar.Utilities.ModelManager.getNewTrainingModel(trainingData);

        let viewOptions = {
            model: training,
        };

        let TrainingEditModalView = new Drupal.trainingCalendar.TrainingEdit(viewOptions);
        $trainingCalendarModal.html(TrainingEditModalView.render().el);
    };

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
    let fullCalendarGetEvents = function(start, end, timezone, callback)
    {
        let answer = [];

        let fetchParams = {
            start_date: moment(start),
            end_date: moment(end),
            timezone: timezone,
        };

        console.info("Requesting trainings for calendar [" + fetchParams.start_date.format() + " : " + fetchParams.end_date.format() + "]");

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
    };

    /**
     * @todo: move this to collection
     *
     * @param {Array} data - The full collection
     * @return {Array}
     */
    let createCalendarEventsFromModels = function(data)
    {
        let answer = [];

        _.each(data, function(training)
        {
            answer.push(training.getCalendarEventData());
        });

        return answer;
    };


})(Backbone, Drupal, jQuery, _);
