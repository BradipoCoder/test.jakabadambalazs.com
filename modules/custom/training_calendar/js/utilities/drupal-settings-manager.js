/**
 * @file
 */
(function (Drupal, drupalSettings, _) {
    let _DRUPAL_SETTINGS_;

    /**
     * Utility class for drupalSettings
     *
     * @type {{}}
     */
    Drupal.trainingCalendar.Utilities.DrupalSettingsManager = {
        /**
         * Initialize
         * @return {Promise<any>}
         */
        init: function()
        {
            return new Promise(function(resolve)
            {
                let self = Drupal.trainingCalendar.Utilities.DrupalSettingsManager;
                //init
                if(!_.isUndefined(drupalSettings)) {
                    _DRUPAL_SETTINGS_ = drupalSettings;
                }
                resolve("DrupalSettingsManager initialized.");
            });
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
            let target = _DRUPAL_SETTINGS_;

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
})(Drupal, drupalSettings, _);