/**
 * @file
 * Training Model
 */

//------------------------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------------- MODEL ---
//------------------------------------------------------------------------------------------------------------------
(function (Backbone, Drupal, _, moment) {
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

            /** @type {moment} */
            "field_start_date": null,

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

        /**
         * Convert XHR response for model
         * @param response
         * @param options
         */
        parse: function (response, options) {
            let answer = {};
            let defaultKeys = _.keys(this.defaults);
            _.each(response, function (value, key) {
                //if (_.contains(defaultKeys, key)) {}
                switch(key)
                {
                    case "field_start_date":
                        let custom_value = moment(value);
                        answer[key] = custom_value;
                        break;
                    default:
                        answer[key] = value;
                        break;
                }
                //console.log("K("+key+"): " + value);
            });

            return answer;
        },

        /**
        get: function (attr) {
            let val = Backbone.Model.prototype.get.call(this, attr);
            return val;
        },*/
    });
})(Backbone, Drupal, _, moment);


//------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------COLLECTION ---
//------------------------------------------------------------------------------------------------------------------
(function(Backbone, Drupal, _, moment)
{
    /** @type {moment} */
    let loaded_from_date;

    /** @type {moment} */
    let loaded_to_date;

    /**
     *
     * @param {moment} from_date
     * @return {boolean}
     */
    let _setLoadedFromDate = function(from_date)
    {
        let answer = false;

        if(!moment.isMoment(loaded_from_date) || moment.min(loaded_from_date, from_date) == from_date)
        {
            loaded_from_date = from_date;
            //console.log("set new loaded_from_date: " + loaded_from_date.format());
            answer = true;
        }

        return answer;
    };

    /**
     *
     * @param {moment} to_date
     * @return {boolean}
     */
    let _setLoadedToDate = function(to_date)
    {
        let answer = false;

        if(!moment.isMoment(loaded_to_date) || moment.max(loaded_to_date, to_date) == to_date)
        {
            loaded_to_date = to_date;
            //console.log("set new loaded_to_date: " + loaded_to_date.format());
            answer = true;
        }

        return answer;
    };

    Drupal.trainingCalendar.TrainingModels = Backbone.Collection.extend({
        model: Drupal.trainingCalendar.TrainingModel,
        url: Drupal.url('training_calendar/rest/trainings'),

        /**
         * will set new loaded_from_date and loaded_to_date ONLY if necessary
         * If any of the dates is changed then will return true to indicate
         * that loading of new models is required
         *
         * @param {moment} start_date
         * @param {moment} end_date
         * @return {boolean}
         */
        setNewLoadDateLimits: function(start_date, end_date)
        {
            let start = _setLoadedFromDate(start_date);
            let stop =  _setLoadedToDate(end_date);
            return start || stop;
        },

        /**
         *
         * @param {moment} start_date
         * @param {moment} end_date
         * @return array
         */
        getModelsBetweenDates: function(start_date, end_date)
        {
            let answer = [];

            /** @type {Drupal.trainingCalendar.TrainingModel} training */
            _.each(this.models, function(training) {
                if(training.get("field_start_date").isBetween(start_date, end_date))
                {
                    answer.push(training);
                }
            });

            return answer;
        },


        /**
         *
         * @return {moment}
         */
        getLoadedFromDate: function()
        {
            return loaded_from_date;
        },

        /**
         *
         * @return {moment}
         */
        getLoadedToDate: function()
        {
            return loaded_to_date;
        },
    });
})(Backbone, Drupal, _, moment);