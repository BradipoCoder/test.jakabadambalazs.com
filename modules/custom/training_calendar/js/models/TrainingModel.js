/**
 * @file
 * Training Model
 */

(function (Backbone, Drupal) {
    /**
     * Backbone model for the Wizard.
     *
     * @constructor
     *
     * @augments Backbone.Model
     */
    Drupal.trainingCalendar.TrainingModel = Backbone.Model.extend({
        urlRoot:'https://tests.jakabadambalazs.com/node',

        save: function(attrs, options) {
            options.patch = true;
            // Proxy the call to the original save function
            Backbone.Model.prototype.save.call(this, attrs, options);
        },

        /**
         * @type {object}
         *
         * @prop pages
         * @prop activePage
         */
        defaults: {
            /**
             * Node ID
             * @type {int}
             */
            "nid": 0,

            /**
             * Node type
             * @type {string}
             */
            "type": "",

            /** @type {string} */
            "title": "",

            /** @type {string} */
            "body": "",

            /** @type {string} */
            "field_date_programmed": "",

            /** @type {string} */
            "field_total_distance": "",

            /** @type {string} */
            "field_training_type": "",
        },
    });

    Drupal.trainingCalendar.TrainingModels = Backbone.Collection.extend({
        model: Drupal.trainingCalendar.TrainingModel,
        url: 'https://tests.jakabadambalazs.com/rest/trainings-listing?_format=hal_json',
    });

})(Backbone, Drupal);