/**
 * @file
 */
(function(Backbone, Drupal, $, _)
{
    let $overlayDiv = $('.training-calendar-overlay');

    let $trainingCalendarApp = $('#training-calendar-main');
    let $trainingCalendarDiv = $('.tc-calendar', $trainingCalendarApp);
    let $trainingCalendarModal = $('.tc-modals', $trainingCalendarApp);

    let TrainingEditModal = Backbone.Modal.extend({
        template: '#template--training-edit',
        cancelEl: '.bbm-button'
    });

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
                    console.log('Clicked Event('+calEvent.id+'): ' + calEvent.title);

                    let viewOptions = {};

                    let training = Drupal.trainingCalendar.Utilities.ModelManager.getTrainingById(calEvent.id);
                    if(training){
                        viewOptions.model = training;
                    }

                    let TrainingEditModalView = new TrainingEditModal(viewOptions);
                    $trainingCalendarModal.html(TrainingEditModalView.render().el);

                    /*
                    let fields = [
                        {
                            name: 'id',
                            label: "ID",
                            control: "input",
                            disabled: true,

                        },
                        {
                            name: "title",
                            label: "Title",
                            control: "input",
                            helpMessage: "Be creative!"
                        },
                        {
                            control: "button",
                            label: "Save"
                        }
                    ];


                    let editForm = new Backform.Form({
                        el: $trainingCalendarModal,
                        model: training,
                        fields: fields, // Will get converted to a collection of Backbone.Field models
                        events: {
                            "submit": function(e) {
                                e.preventDefault();
                                /*
                                this.model.save()
                                    .done(function(result) {
                                        alert("Successful!");
                                    })
                                    .fail(function(error) {
                                        alert(error);
                                    });
                                    * /
                                console.warn("SAVE")
                                return false;
                            }
                        }
                    });

                    editForm.render();
                    */


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
     *
     * @param {Array} data - The full collection
     * @return {Array}
     */
    let createCalendarEventsFromModels = function(data)
    {
        let answer = [];

        _.each(data, function(training) {
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


})(Backbone, Drupal, jQuery, _);
