/**
 * @file
 */
(function(Backbone, Drupal, drupalSettings, $, _)
{

    //------------------------------------------------------------------------------------------------------------Drupal
    Drupal.trainingCalendar = Drupal.trainingCalendar || {
        /**
         * Declare namespaces
         */
        Utilities: {},

        /**
         * Declare other variables
         */
        models: {},
        views: {},

        /**
         * Initialize trainingCalendar
         */
        init: function()
        {
            console.log("Starting trainingCalendar...");

            Drupal.trainingCalendar.Utilities.DrupalSettingsManager.init();
            Drupal.trainingCalendar.Utilities.CommunicationManager.init();
            Drupal.trainingCalendar.Utilities.TokenManager.init();





            console.log("trainingCalendar initialized.");
            /*
            let TrainingCalendarApp = new Drupal.trainingCalendar.TrainingList({
                collection: new Drupal.trainingCalendar.TrainingModels
            });
            */

            /*
            $('#training-calendar').fullCalendar({
                weekends: true,
                defaultView: 'month',
                showNonCurrentDates: true,
                weekNumbers: true,
            });
            */

            // let s1 = Drupal.trainingCalendar.Utilities.DrupalSettingsManager.getDrupalSettingsValue("training_calendar.oauth_token_data.token_type");
            // console.log("S1: " + s1);
            // let s2 = Drupal.trainingCalendar.Utilities.DrupalSettingsManager.getDrupalSettingsValue("path.currentLanguage");
            // console.log("S2: " + s2);
        },

        /**
         * Write operations in Drupal require CSRF token.
         *
         * @param {function} callback
         * @param {string} method
         *
         * @todo: bleeeehh!
         */
        getCsrfToken: function(callback, method)
        {
            let requireTokenMethods = ["create", "update", "patch"];
            if(_.contains(requireTokenMethods, method)) {
                $.get(Drupal.url('session/token'))
                    .done(function(data)
                    {
                        callback(data);
                    });
            } else {
                callback(null);
            }
        },

        /**
         * Base64 encoded username:password value created by server side
         *
         * @return {string}
         */
        getUserhash: function()
        {
            let answer = "";
            if(!_.isUndefined(drupalSettings)) {
                if(!_.isUndefined(drupalSettings.training_calendar)) {
                    if(!_.isUndefined(drupalSettings.training_calendar.userhash)) {
                        answer = drupalSettings.training_calendar.userhash;
                    }
                }
            }
            return answer;
        },


        /**
         *
         * @param {string|Array} name
         * @param {*} default_value
         * @return {*}
         */

    };

    //--------------------------------------------------------------------------------------------------------------INIT
    Drupal.behaviors.trainingCalendar = {
        attach: function attach(context, settings)
        {
            Drupal.trainingCalendar.init();
        }
    };

})(Backbone, Drupal, drupalSettings, jQuery, _);

