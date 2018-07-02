/**
 * @file
 */
(function(_)
{
    /**
     * Utility class for managing access and refresh tokens
     *
     * @type {{}}
     */
    Drupal.trainingCalendar.Utilities.TokenManager = {
        token_type: null,
        expires_in: null,
        access_token: null,
        refresh_token: null,

        token_refresh_timeout: null,


        init: function()
        {
            this.registerNewTokens(Drupal.trainingCalendar.Utilities.DrupalSettingsManager.getDrupalSettingsValue("training_calendar.oauth_token_data"));
            this.handleTokenRefresh(this);

            console.log("TokenManager initialized.");
        },



        updateNewTokenRefreshTimeout: function()
        {
            if(!_.isNull(this.token_refresh_timeout)){
                clearTimeout(this.token_refresh_timeout);
                this.token_refresh_timeout = null;
            }

            let timeout_in = (this.expires_in - 30) * 1000;

            console.log("SET TIMEOUT HANDLE: " + timeout_in);

            this.token_refresh_timeout = setTimeout(this.handleTokenRefresh, timeout_in, this);
        },

        handleTokenRefresh: function(self) {
            console.log("HANDLE START");
            Drupal.trainingCalendar.Utilities.CommunicationManager.refreshAccessToken();
        },

        /**
         *
         * @param {{token_type, expires_in, access_token, refresh_token}} data
         */
        registerNewTokens: function(data)
        {
            if(_.isUndefined(data.token_type) || _.isNull(data.token_type)) {
                throw new Error("Missing Token Type!");
            }

            if(_.isUndefined(data.expires_in) || _.isNull(data.expires_in)) {
                throw new Error("Missing Token Expiration!");
            }

            if(_.isUndefined(data.access_token) || _.isNull(data.access_token)) {
                throw new Error("Missing Access Token!");
            }

            if(_.isUndefined(data.refresh_token) || _.isNull(data.refresh_token)) {
                throw new Error("Missing Refresh Token!");
            }

            this.token_type = data.token_type;
            this.expires_in = data.expires_in;
            this.access_token = data.access_token;
            this.refresh_token = data.refresh_token;

            this.updateNewTokenRefreshTimeout(this);
        }
    };
})(_);