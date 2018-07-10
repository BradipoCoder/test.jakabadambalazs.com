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
                //
                resolve("ModelManager initialized.");
            });
        },

        /**
         *
         * @param {{start_date, end_date, timezone}} params
         * @return {Promise<Array>}
         */
        fetchTrainings: function(params)
        {
            return new Promise(function(resolve, reject)
            {
                let ajaxParams = params || {};

                let request_start_date = ajaxParams.start_date;
                let request_end_date = ajaxParams.end_date;

                /* @todo: this is wrong because if fetch fails we will not be able to load resources again for this timespan */
                let needsLoading = trainings.setNewLoadDateLimits(request_start_date, request_end_date);

                if(needsLoading) {
                    ajaxParams.start_date = ajaxParams.start_date.format();
                    ajaxParams.end_date = ajaxParams.end_date.format();
                    console.info("Fetching trainings with params: " + JSON.stringify(ajaxParams));

                    trainings.fetch({
                        data: ajaxParams,
                        remove: false,
                        success: function(collection, response, options)
                        {
                            //console.info("TRAININGS", trainings.toJSON());
                            let data = trainings.getModelsBetweenDates(request_start_date, request_end_date);
                            //console.info("FILTERED-DATA: ", JSON.stringify(data));
                            resolve(data);
                        },
                        error: function(collection, response, options)
                        {
                            reject(new Error("Unable to fetch Calendar Events!"));
                        }
                    });
                } else {
                    //console.info("No fetching needed ... just pick up some models ;)");
                    let data = trainings.getModelsBetweenDates(request_start_date, request_end_date);
                    //console.info("FILTERED-DATA: ", JSON.stringify(data));
                    resolve(data);
                }
            });
        }

    };

    //---------------------------------------------------------------------------------------------------PRIVATE METHODS



})(Drupal, _, moment);
