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

        init: function()
        {
            console.log("CommunicationManager initialized.");
        },

        ping: function()
        {
            let self = this;
            let token_type = Drupal.trainingCalendar.Utilities.TokenManager.token_type;
            let access_token = Drupal.trainingCalendar.Utilities.TokenManager.access_token;
            //@todo: check if access_token is not null - otherwise try right away with refresh token
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

        refreshAccessToken: function()
        {
            let self = this;
            let refresh_token = Drupal.trainingCalendar.Utilities.TokenManager.refresh_token;
            //@todo: check if refresh_token is not null - otherwise bail out and redirect user to login
            $.ajax({
                url: Drupal.url("oauth/token"),
                type: 'POST',
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                data: {
                    grant_type: "refresh_token",
                    client_id: "6578f259-aca8-4a41-87fd-4992753f574c",
                    client_secret: "TrainingCalendarApp2018",
                    refresh_token: refresh_token,
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
                    if(statusCode == 401) {
                        console.log("Invalid request("+statusCode+")! " + xhr.responseJSON.message);//xhr.responseJSON.message
                        Drupal.trainingCalendar.Utilities.TokenManager.refresh_token = null;
                        //@todo: this refresh token is probably expired! User needs to re-login!
                    } else {
                        console.log("Unknown error("+statusCode+")! ", xhr.responseJSON);//xhr.responseJSON.message
                    }
                },
            }).done(function(data)
            {
                Drupal.trainingCalendar.Utilities.TokenManager.registerNewTokens(data);
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


        Drupal.trainingCalendar.getCsrfToken(function(csrfToken)
        {
            let headers = _.has(options, "headers") ? options.headers : {};

            headers = _.extend(headers, {
                'Accept': 'application/hal+json',
                'Authorization': 'Basic ' + Drupal.trainingCalendar.getUserhash(),
            });

            if(!_.isNull(csrfToken)) {
                headers["X-CSRF-Token"] = csrfToken;
            }
            options.headers = headers;

            console.log("SYNC[" + method + "]OPT: ", options);

            return Backbone._sync.call(self, method, model, options);
        }, method);
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
