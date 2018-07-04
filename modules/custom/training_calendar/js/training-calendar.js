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

            let classesToInit = [
                Drupal.trainingCalendar.Utilities.DrupalSettingsManager.init,
                Drupal.trainingCalendar.Utilities.CommunicationManager.init,
                Drupal.trainingCalendar.Utilities.TokenManager.init
            ];

            Promise.reduce(classesToInit, function(accumulator, initMethod)
            {
                /** @type {Promise<any>} initPromise */
                let initPromise = initMethod.call();
                initPromise.then(function(initMessage) {
                    if(initMessage)
                    {
                        console.log(initMessage);
                    }
                }).catch(function(e) {
                    console.error(e);
                });
                return initPromise;
            }, null).then(function()
            {
                console.log("Training Calendar is initialized and ready for use.");
                $('.training-calendar-overlay').delay(500).fadeOut(500);
            });

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
    };

    //--------------------------------------------------------------------------------------------------------------INIT
    Drupal.behaviors.trainingCalendar = {
        attach: function attach(context, settings)
        {
            Drupal.trainingCalendar.init();
        }
    };

})(Backbone, Drupal, drupalSettings, jQuery, _);

