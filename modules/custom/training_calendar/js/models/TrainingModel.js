/**
 * @file
 * Training Model
 */

(function (Backbone, Drupal, drupalSettings, _, moment) {
    /**
     * Backbone model for the Wizard.
     *
     * @constructor
     *
     * @augments Backbone.Model
     */
    Drupal.trainingCalendar.TrainingModel = Backbone.Model.extend({
        idAttribute: 'id',

        /**
         * @type {{}}
         *
         * @property {int} nid
         * @property {string} type
         *
         */
        defaults: {
            /**
             * Node ID
             * @type {int}
             */
            "id": 0,

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
            "field_start_date": "",

            /** @type {string} */
            "field_total_distance": "",

            /** @type {string} */
            "field_activity_type": "",

            /** @type {string} */
            "created": "",

            /** @type {string} */
            "changed": "",
        },

        url: function () {
            let id = this.get(this.idAttribute);
            return Drupal.url("node/" + encodeURIComponent(id));
        },

        save: function (attrs, options) {
            let self = this;
            options = options || {};
            options.patch = true;
            options.wait = true;

            /*NOT WORKING: https://stackoverflow.com/questions/9892717/why-does-my-backbone-model-haschanged-always-return-false*/
            //let changedKeys = _.keys(this.changedAttributes());

            /**
             * We need to extend backbone's model definition and create a custom array of "dirty" fields
             */


            let changedKeys = _.keys(this.defaults);
            console.log("CHANGED-KEYS: ", changedKeys);

            attrs = {};
            _.each(changedKeys, function (key) {
                attrs[key] = [{"value": self.get(key)}];
            });
            delete attrs["field_training_type"];

            attrs["type"] = [{"target_id": this.get("type")}];

            console.log("ATTRS-2-SAVE:  ", attrs);


            // Proxy the call to the original save function
            Backbone.Model.prototype.save.call(this, attrs, options);
        },

        // **parse** converts a response into the hash of attributes to be `set` on
        // the model. The default implementation is just to pass the response along.
        /*
        parse: function (response, options) {
            let answer = {};
            let extraKeys = {};
            let defaultKeys = _.keys(this.defaults);

            _.each(response, function (value, key) {
                if (_.contains(defaultKeys, key)) {
                    let first = _.first(value);
                    if (first) {
                        if (_.has(first, "value")) {
                            answer[key] = first["value"];
                        } else if(_.has(first, "target_id")) {
                            //node type
                            answer[key] = first["target_id"];
                        }
                    }
                } else {
                    extraKeys[key] = value;
                }
            });

            //console.log("DK: ", defaultKeys);
            //console.log("extraKeys: ", extraKeys);
            //console.log("PARSED: ", answer);

            return answer;
        },*/

        /**
        get: function (attr) {
            let val = Backbone.Model.prototype.get.call(this, attr);
            return val;
        },*/
    });

    //------------------------------------------------------------------------------------------------------------------
    //----------------------------------------------------------------------------------------------------COLLECTION ---
    //------------------------------------------------------------------------------------------------------------------
    Drupal.trainingCalendar.TrainingModels = Backbone.Collection.extend({
        model: Drupal.trainingCalendar.TrainingModel,
        url: Drupal.url('training_calendar/rest/trainings'),

        models_loaded_from_date: null,
        models_loaded_to_date: null,

        /**
         *
         * @param {moment} from_date
         * @param {moment} to_date
         * @return {boolean}
         */
        areModelsLoadedForTimespan: function(from_date, to_date)
        {
            let answer = false;

            if(moment.isMoment(from_date) && moment.isMoment(to_date))
            {
                if(moment.isMoment(this.models_loaded_from_date) && moment.isMoment(this.models_loaded_to_date))
                {
                    if(moment.min(this.models_loaded_from_date, from_date) == this.models_loaded_from_date
                        && moment.max(this.models_loaded_to_date, to_date) == this.models_loaded_to_date)
                    {
                        answer = true;
                    }
                }
            }

            return answer;
        },

        /**
         *
         * @param {moment} from_date
         */
        setModelsLoadedFromDate: function(from_date)
        {
            this.models_loaded_from_date = from_date;
            console.log("setModelsLoadedFromDate: " + this.models_loaded_from_date.format());
        },

        /**
         *
         * @param {moment} to_date
         */
        setModelsLoadedToDate: function(to_date)
        {
            this.models_loaded_to_date = to_date;
            console.log("setModelsLoadedToDate: " + this.models_loaded_to_date.format());
        },

        /**
         *
         * @return {moment}
         */
        getModelsLoadedFromDate: function()
        {
            return this.models_loaded_from_date;
        },

        /**
         *
         * @return {moment}
         */
        getModelsLoadedToDate: function()
        {
            return this.models_loaded_to_date;
        },
    });



})(Backbone, Drupal, drupalSettings, _, moment);