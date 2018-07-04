/**
 * @file
 */
(function(Drupal, _)
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

        /**
         * Subtract this many seconds from the expires_in
         * @type {int}
         */
        timeout_margin_seconds: 30,

        token_refresh_timeout: null,

        /**
         * Initialize
         * @return {Promise<any>}
         */
        init: function()
        {
            return new Promise(function(resolve)
            {
                let self = Drupal.trainingCalendar.Utilities.TokenManager;
                self.registerNewTokens(Drupal.trainingCalendar.Utilities.DrupalSettingsManager.getDrupalSettingsValue("training_calendar.oauth_token_data"));
                self.handleTokenRefresh().then(function()
                {
                    resolve("TokenManager initialized.");
                });
            });
        },


        /**
         * Refresh tokens
         *
         * @return {Promise<any>}
         */
        handleTokenRefresh: function()
        {
            return new Promise(function(resolve)
            {
                let self = Drupal.trainingCalendar.Utilities.TokenManager;

                Drupal.trainingCalendar.Utilities.CommunicationManager.request({
                    url: Drupal.url("training_calendar/rest/refresh_tokens"),
                    method: 'POST',
                    data: {
                        grant_type: "refresh_token",
                        refresh_token: self.refresh_token,
                    },
                }).then(function(xhr)
                {
                    let serverResponse = !_.isUndefined(xhr["responseJSON"]) ? xhr["responseJSON"] : xhr;
                    //console.info("XHR OK[" + xhr.status + "]", xhr);
                    self.registerNewTokens(serverResponse);
                    resolve();
                }).catch(function(xhr)
                {
                    console.error("XHR ERROR(" + xhr.status + "):", xhr);
                    self.refresh_token = null;
                    window.location.href = Drupal.url("user/logout");
                });
            });
        },

        updateNewTokenRefreshTimeout: function()
        {
            if(!_.isNull(this.token_refresh_timeout)) {
                clearTimeout(this.token_refresh_timeout);
                this.token_refresh_timeout = null;
            }

            let timeout_in = (this.expires_in - this.timeout_margin_seconds) * 1000;
            //console.log("NEXT TOKEN REFRESH(sec): " + timeout_in/1000);

            this.token_refresh_timeout = setTimeout(this.handleTokenRefresh, timeout_in);
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

            this.updateNewTokenRefreshTimeout();
        }
    };
})(Drupal, _);
