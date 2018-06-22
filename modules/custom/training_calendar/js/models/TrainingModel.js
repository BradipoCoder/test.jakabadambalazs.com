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

        /**
         * Change the active page to the next.
         */
        doSomething: function () {
            this.set('title', this.get('title') + "+");
        },
    });

    Drupal.trainingCalendar.TrainingModels = Backbone.Collection.extend({
        model: Drupal.trainingCalendar.TrainingModel,
        url: 'https://tests.jakabadambalazs.com/rest/trainings-listing?_format=hal_json',

    });

})(Backbone, Drupal);