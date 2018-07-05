/**
 * @file
 */
(function(Backbone, Drupal, $, _)
{

    /**
     * Utility class for managing access and refresh tokens
     *
     * @type {{}}
     */
    Drupal.trainingCalendar.Utilities.CommunicationManager = {

        /**
         * Initialize
         * @return {Promise<any>}
         */
        init: function()
        {
            return new Promise(function(resolve)
            {
                let self = Drupal.trainingCalendar.Utilities.CommunicationManager;
                //init

                //
                resolve("CommunicationManager initialized.");
            });
        },

        /*
        ping: function()
        {
            let self = this;
            let token_type = Drupal.trainingCalendar.Utilities.TokenManager.token_type;
            let access_token = Drupal.trainingCalendar.Utilities.TokenManager.access_token;
            $.ajax({
                url: Drupal.url("training_calendar/rest/ping"),
                headers: {
                    "Authorization": token_type + " " + access_token,
                },
                beforeSend: function(xhr)
                {
                    //return false;
                },
                timeout: function(xhr)
                {
                    //return false;
                },
                error: function(xhr)
                {
                    let statusCode = xhr.status;
                    if(statusCode == 403) {
                        console.log("Unauthorized("+statusCode+")!");//xhr.responseJSON.message
                        Drupal.trainingCalendar.Utilities.TokenManager.access_token = null;
                        self.refreshAccessToken();
                    } else {
                        console.log("Unknown error("+statusCode+")! ", xhr.responseJSON);//xhr.responseJSON.message
                    }
                },
            }).done(function(data)
            {
                if(console && console.log) {
                    console.log(data);
                }
            });
        },
        */

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
         * Main (generic) request method
         *
         * @param {{}} settings
         * @return {Promise<any>}
         */
        request: function(settings)
        {
            return new Promise(function(resolve, reject)
            {
                if(!_.isUndefined(settings["complete"]))
                {
                    return reject({
                        status: "BAD",
                        error: "Remove complete callback from ajax settings!"
                    });
                }

                let defaults = {
                    async: true,
                    cache: false,
                    dataType: "json",
                    timeout: 30 * 1000,
                    complete: function(xhr, status)
                    {

                        switch(status) {
                            case "success":
                            case "notmodified":
                                resolve(xhr);
                                break;
                            case "error":
                            case "abort":
                            case "parsererror":
                            case "nocontent":
                            case "timeout":
                                reject(xhr);
                                break;
                            default:
                                console.error("XHR UNKNOWN STATUS:", status);
                        }
                    }
                };
                settings = _.extend(defaults, settings);

                $.ajax(settings);
            });
        }
    };

    //------------------------------------------------------------------------------------------Backbone override - SYNC
    Backbone._sync = Backbone.sync;

    /**
     *  Override Backbone sync method for all requests to Drupal to add Oauth2 authentication data
     *
     * @param method
     * @param model
     * @param options
     */
    Backbone.sync = function(method, model, options)
    {
        let self = this;
        options = options || {};

        let token_type = Drupal.trainingCalendar.Utilities.TokenManager.token_type;
        let access_token = Drupal.trainingCalendar.Utilities.TokenManager.access_token;

        // Drupal.trainingCalendar.getCsrfToken(function(csrfToken)
        // {// }, method);
        // if(!_.isNull(csrfToken)) {
        //     headers["X-CSRF-Token"] = csrfToken;
        // }

        let headers = _.has(options, "headers") ? options.headers : {};

        headers = _.extend(headers, {
            /*'Accept': 'application/hal+json',*/
            "Authorization": token_type + " " + access_token,
        });

        options.headers = headers;

        console.log("SYNC[" + method + "]OPT: ", options);

        return Backbone._sync.call(self, method, model, options);

    };

    //------------------------------------------------------------------------------------------Backbone override - AJAX
    Backbone._ajax = Backbone.ajax;

    /**
     * Override Backbone ajax method for all requests to Drupal:
     *  - add '_format=hal_json' to each url
     *
     *  @todo: regexp and substitution will only work if no other query parameter is present! Fixme!
     *
     * @return {*}
     */
    Backbone.ajax = function()
    {
        let url = arguments[0].url;
        let re = new RegExp("[^?]*\?_format=hal_json$");
        if(!re.test(url)) {
            arguments[0].url = url + "?_format=hal_json";
        }

        //console.log("AJAX-OPT: ", arguments);
        return Backbone._ajax.apply(this, arguments);
    };

})(Backbone, Drupal, jQuery, _);

