/**
 * @file
 */
(function ($, Backbone, Drupal, drupalSettings, _) {

    //------------------------------------------------------------------------------------------Backbone override - SYNC
    Backbone._sync = Backbone.sync;
    Backbone.sync = function(method, model, options)
    {
        let self = this;
        options = options || {};



        Drupal.trainingCalendar.getCsrfToken(function (csrfToken) {
            let headers = _.has(options, "headers") ? options.headers : {};

            headers = _.extend(headers, {
                'Accept' : 'application/hal+json',
                'Authorization' : 'Basic ' + Drupal.trainingCalendar.getUserhash(),
            });

            if(!_.isNull(csrfToken)){
                headers["X-CSRF-Token"] = csrfToken;
            }
            options.headers = headers;

            console.log("SYNC["+method+"]OPT: ", options);

            return Backbone._sync.call(self, method, model, options);
        }, method);
    };

    //------------------------------------------------------------------------------------------Backbone override - SYNC
    Backbone._ajax = Backbone.ajax;
    Backbone.ajax = function()
    {
        let url = arguments[0].url;
        let re = new RegExp("[^?]*\?_format=hal_json$");
        if(!re.test(url))
        {
            arguments[0].url = url + "?_format=hal_json";
        }

        //console.log("AJAX-OPT: ", arguments);
        return Backbone._ajax.apply(this, arguments);
    };


    //------------------------------------------------------------------------------------------------------------Drupal
    Drupal.trainingCalendar = Drupal.trainingCalendar || {
        models: {},
        views: {}
    };

    Drupal.behaviors.trainingCalendar = {
        attach: function attach(context, settings) {
            console.log("TRAINING CALENDAR...");



            let TrainingCalendarApp = new Drupal.trainingCalendar.TrainingList({
                collection: new Drupal.trainingCalendar.TrainingModels
            });


            /*
            $('#training-calendar').fullCalendar({
                weekends: true,
                defaultView: 'month',
                showNonCurrentDates: true,
                weekNumbers: true,
            });
            */


        }
    };


    /**
     * Write operations in Drupal require CSRF token.
     *
     * @param {function} callback
     * @param {string} method
     *
     * @todo: bleeeehh!
     */
    Drupal.trainingCalendar.getCsrfToken = function (callback, method) {
        let requireTokenMethods = ["create", "update", "patch"];
        if(_.contains(requireTokenMethods, method))
        {
            $.get(Drupal.url('session/token'))
                .done(function (data) {
                    callback(data);
                });
        } else {
            callback(null);
        }
    };

    /**
     * Base64 encoded username:password value created by server side
     *
     * @return {string}
     */
    Drupal.trainingCalendar.getUserhash = function () {
        let answer = "";
        if(!_.isUndefined(drupalSettings))
        {
            if(!_.isUndefined(drupalSettings.training_calendar))
            {
                if(!_.isUndefined(drupalSettings.training_calendar.userhash))
                {
                    answer = drupalSettings.training_calendar.userhash;
                }
            }
        }
        return answer;
    }
})(jQuery, Backbone, Drupal, drupalSettings, _);
