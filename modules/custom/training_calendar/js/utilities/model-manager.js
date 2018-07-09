/**
 * @file
 */
(function (Drupal, _, moment) {
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
                //let self = Drupal.trainingCalendar.Utilities.ModelManager;
                //init
                trainings = new Drupal.trainingCalendar.TrainingModels;
                //self.setupData();
                //
                resolve("ModelManager initialized.");
            });
        },

        /**
         *
         * @param {{start_date, end_date, timezone}} params
         * @return {Promise<Backbone.Collection>}
         */
        fetchCalendarEvents: function(params)
        {
            return new Promise(function(resolve, reject)
            {
                let ajaxParams = params || {};

                let start_date = ajaxParams.start_date;
                let end_date = ajaxParams.end_date;

                //let alreadyLoaded = Drupal.trainingCalendar.TrainingModels.areModelsLoadedForTimespan(start_date, end_date);
                let alreadyLoaded = false;


                console.info("Fetching collection with params: " + JSON.stringify(ajaxParams));
                console.info("ALREADY LOADED: " + alreadyLoaded);

                ajaxParams.start_date = ajaxParams.start_date.format();
                ajaxParams.end_date = ajaxParams.end_date.format();

                trainings.fetch({
                    data: ajaxParams,
                    success: function(collection, response, options)
                    {
                        //console.info("TRAININGS LOADED FROM REMOTE - fetch OK");
                        console.info(trainings.toJSON());
                        //Drupal.trainingCalendar.TrainingModels.setModelsLoadedFromDate(start_date);
                        //Drupal.trainingCalendar.TrainingModels.setModelsLoadedToDate(end_date);
                        resolve(collection);
                    },
                    error: function(collection, response, options)
                    {
                        reject(new Error("Unable to fetch Calendar Events!"));
                    }
                });
            });
        }

    };

    //---------------------------------------------------------------------------------------------------PRIVATE METHODS



})(Drupal, _, moment);
