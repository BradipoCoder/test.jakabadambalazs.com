/**
 * @file
 * Training Model
 */

(function (Backbone, Drupal, drupalSettings, _) {
    /**
     * Backbone model for the Wizard.
     *
     * @constructor
     *
     * @augments Backbone.Model
     */
    Drupal.trainingCalendar.TrainingModel = Backbone.Model.extend({
        idAttribute: 'nid',

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

        save: function (attrs, options) {
            options = options || {};
            options.patch = true;

            attrs = {
                "type": [
                    {
                        "target_id": "tr_run"
                    }
                ],
                "title": [
                    {
                        "value": this.get("title"),
                        "lang": "en"
                    }
                ]
            };

            // Proxy the call to the original save function
            Backbone.Model.prototype.save.call(this, attrs, options);

        },

        url: function () {
            let id = this.get(this.idAttribute);
            return "https://tests.jakabadambalazs.com/node/" + encodeURIComponent(id) + "?_format=hal_json";
        },

        /*
        get: function(attr) {
            let answer = false;

            if(_.has(this.attributes, attr)){
                let thisAttribute = this.attributes[attr];
                let thisAttributeFirstElement = _.first(thisAttribute);
                if(_.has(thisAttributeFirstElement, "value")){
                    answer = thisAttributeFirstElement["value"];
                }
            }

            console.log("GET["+attr+"]: " + answer);

            return answer;
            //return this.attributes[attr];
        },

        toRenderJSON: function(options) {
            //let c = _.clone(this.attributes);
            let json = _.mapObject(this.attributes, function(val, key)
            {
                let answer = null;
                let first = _.first(val);
                if(first){
                    if(_.has(first, "value")){
                        answer = first["value"];
                    }
                }
                return answer;
            });

            return json;
        },*/

        // **parse** converts a response into the hash of attributes to be `set` on
        // the model. The default implementation is just to pass the response along.
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
                        }
                    }
                } else {
                    //extraKeys[key] = value;
                }
            });

            //console.log("DK: ", defaultKeys);
            //console.log("extraKeys: ", extraKeys);
            //console.log("PARSED: ", answer);

            return answer;
        }
    });

    Drupal.trainingCalendar.TrainingModels = Backbone.Collection.extend({
        model: Drupal.trainingCalendar.TrainingModel,
        url: 'https://tests.jakabadambalazs.com/rest/trainings-listing?_format=hal_json',
    });

})(Backbone, Drupal, drupalSettings, _);