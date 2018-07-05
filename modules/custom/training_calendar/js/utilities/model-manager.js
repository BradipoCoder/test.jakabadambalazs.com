/**
 * @file
 */
(function (Drupal, _) {
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
                resolve("ModelManager initialized.");
            });
        },
    };
})(Drupal, _);
