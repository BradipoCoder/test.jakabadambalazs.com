/**
 * @file
 */
(function (Drupal, _) {
    /** Backbone collection */
    let trainings;
    /**
     * Utility class for models
     *
     * @type {{}}
     */
    Drupal.trainingCalendar.Utilities.ModelManager = {

        /**
         * Initialize
         * @return {Promise<any>}
         */
        init: function()
        {
            return new Promise(function(resolve)
            {
                let self = Drupal.trainingCalendar.Utilities.ModelManager;
                //init
                self.setupData();
                //
                resolve("ModelManager initialized.");
            });
        },


        /**
         * Main EventData call to populate calendar with data
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

            trainings.each(function(training) {
                let event = {};
                event.id = training.id;
                event.title = training.get("title");
                event.start = training.get("field_start_date");
                //event.end = training.get("end");
                event.className = ['event', 'generic'];
                event.overlap = false;
                event.allDay = true;
                event.editable = true;

                answer.push(event);
            });

            callback(answer);
        },

        setupData: function()
        {
            trainings = new Drupal.trainingCalendar.TrainingModels;
            trainings.fetch({
                success: function(collection, response, options)
                {
                    console.warn("TRAININGS LOADED FROM REMOTE - updating calendar");
                    //console.warn(trainings.toJSON());
                    Drupal.trainingCalendar.Utilities.ViewManager.updateCalendarEvents();
                }
            });
        },
    };
})(Drupal, _);
