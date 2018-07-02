/**
 * @file
 */
(function (drupalSettings, _) {
    /**
     * Utility class for drupalSettings
     *
     * @type {{}}
     */
    Drupal.trainingCalendar.Utilities.DrupalSettingsManager = {
        ds: null,

        init: function()
        {
            if(!_.isUndefined(drupalSettings)) {
                this.ds = drupalSettings;
            }
            console.log("DrupalSettingsManager initialized.");
        },

        /**
         * Get a value from drupalSettings using dot(".") notation like:
         * getDrupalSettingsValue("path.currentLanguage");
         *
         * @param {string} name
         * @param {*} [default_value]
         * @return {*|null}
         */
        getDrupalSettingsValue: function(name, default_value) {
            let answer = default_value || null;
            let keys = [];
            let target = this.ds;

            if(_.isString(name)) {
                keys = name.split(".");
            }

            _.each(keys, function(key){
                if (!_.isUndefined(target[key])) {
                    target = target[key];
                }
                answer = target;
            });

            return answer;
        }

    };
})(drupalSettings, _);