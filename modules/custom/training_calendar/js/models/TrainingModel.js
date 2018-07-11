/**
 * @file
 * Training Model
 */

//------------------------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------------- MODEL ---
//------------------------------------------------------------------------------------------------------------------
(function (Backbone, Drupal, _, moment) {

    /**
     * Backbone model for Training.
     *
     * @constructor
     *
     * @augments Backbone.Model
     */
    Drupal.trainingCalendar.TrainingModel = Backbone.Model.extend({
        idAttribute: 'id',

        // An array of attributes whose value differ since instance creation.
        dirty: [],

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
            "type": "training",

            /** @type {string} */
            "title": "",

            /** @type {string} */
            "body": "",

            /** @type {number} */
            "status": 1,

            /** @type {moment} */
            "field_start_date": null,

            /** @type {string} */
            "field_total_distance": "",

            /** @type {number} */
            "field_activity_type": 3,

            /** @type {moment} */
            "created": null,

            /** @type {moment} */
            "changed": null,
        },

        url: function () {
            let id = this.get(this.idAttribute);
            return Drupal.url('training_calendar/rest/training'
                + (id ? '/' + encodeURIComponent(id) : '')
            );
        },

        save: function (attrs, options) {
            options = options || {};
            options.wait = true;

            let id = this.get(this.idAttribute);
            if(id)
            {
                options.patch = true;
            } else {
                options.method = "POST";
            }

            attrs = this.getSaveData();
            console.info("SAVING MODEL DATA:  ", attrs);


            // Proxy the call to the original save function
            Backbone.Model.prototype.save.call(this, attrs, options);
        },

        /**
         * Convert XHR response for model
         * @param response
         * @param options
         */
        parse: function (response, options) {
            //console.warn("PARSING: ", response);
            let answer = {};
            let defaultKeys = _.keys(this.defaults);
            let cv;
            _.each(response, function (value, key) {
                //if (_.contains(defaultKeys, key)) {}
                switch(key)
                {
                    case "field_start_date":
                        answer[key] = moment(value);
                        break;
                    case "created":
                    case "changed":
                        answer[key] = moment.unix(value);
                        break;
                    case "field_activity_type":
                        cv = parseInt(value);
                        answer[key] = cv;
                        break;
                    default:
                        answer[key] = value;
                        break;
                }
                //console.log("K("+key+"): " + value);
            });

            return answer;
        },

        /*
        //--- override set method to set dirty fields to use when saving

        set: function(key, val, options)
        {
            let _kv;
            if (typeof key !== 'object')
            {
                _kv = {};
                _kv[key] = val;
            } else {
                _kv = key;
            }
            _.each(_kv, function(v,k){
                console.log("MODEL-SET KEY("+k+"):" + v);
            });

            Backbone.Model.prototype.set.call(this, key, val, options);
            console.log("MODEL-SET KEYS: ", key);
            console.log("MODEL-SET VALUES: ", val);
            console.log("MODEL-SET OPTIONS: ", options);
            return this;
        },
        */

        /**
        get: function (attr) {
            let val = Backbone.Model.prototype.get.call(this, attr);
            return val;
        },*/

        /* This data will be pushed to server for being saved */
        getSaveData: function()
        {
            let self = this;
            let answer  = {};
            /*NOT WORKING: https://stackoverflow.com/questions/9892717/why-does-my-backbone-model-haschanged-always-return-false*/
            //let changedKeys = _.keys(this.changedAttributes());
            /**
             * We need to extend backbone's model definition and create a custom array of "dirty" fields
             */

            let changedKeys = _.keys(this.defaults);
            let excludedKeys = ['created', 'changed'];

            _.each(changedKeys, function (key) {
                if(!_.contains(excludedKeys, key))
                {
                    switch(key)
                    {
                        // case "field_start_date":
                        //     answer[key] = self.get(key).format();
                        //     break;
                        default:
                            answer[key] = self.get(key);
                    }
                }
            });

            delete answer["field_training_type"];

            return answer;
        },

        getCalendarEventData: function()
        {
            let event = {};

            event.id = this.id;
            event.title = this.get("title");
            event.start = this.get("field_start_date");
            //event.end = training.get("end");

            event.field_activity_type = this.get("field_activity_type");
            event.field_total_distance = this.get("field_total_distance");
            event.distance_km = Math.round(event.field_total_distance / 1000);

            let activityTypeClassName = 'activity-type-' + this.get("field_activity_type");
            event.className = [activityTypeClassName];


            event.overlap = true;
            event.allDay = true;
            event.editable = true;

            return event;
        },

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

        if(!moment.isMoment(loaded_from_date) || from_date.isBefore(loaded_from_date))
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

        if(!moment.isMoment(loaded_to_date) || to_date.isAfter(loaded_to_date))
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