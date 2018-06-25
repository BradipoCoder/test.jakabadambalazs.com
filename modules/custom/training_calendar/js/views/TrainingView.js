/**
 * @file
 * A Backbone view for the Wizard.
 */

(function ($, Backbone, Drupal, drupalSettings, _) {


    Drupal.trainingCalendar.TrainingView = Backbone.View.extend({
        tagName: 'div',
        template: _.template($('#template--training-view').html()),

        events: {
            'click .training-calendar--event--action-1': 'doFunkyStuff',
        },

        initialize: function initialize() {
            this.listenTo(this.model, 'change', this.render);
        },

        render: function () {
            this.$el.html(this.template(this.model.toJSON()));

            return this;
        },

        doFunkyStuff: function(event) {
            //this.model.doSomething();
            this.promptForNewTitle();
            this.model.save();
            event.preventDefault();
            event.stopPropagation();
        },

        promptForNewTitle: function () {
            let title = prompt("Please enter a new title", this.model.get("title"));
            this.model.set("title", title);
        }
    });


    Drupal.trainingCalendar.TrainingList = Backbone.View.extend({
        el: $('#trainings-list-container'),


        template: _.template($('#template--trainings-list').html()),

        initialize: function () {
            self = this;
            this.collection.fetch({
                success: function () {
                    self.render();
                }
            });
        },

        render: function () {
            this.el.innerHTML = this.template();
            let $list = this.$el.find('div.trainings');

            this.collection.forEach(function (model) {
                $list.append((new Drupal.trainingCalendar.TrainingView({model: model})).render().el);
            }, this);

            return this;
        },
    });

})(jQuery, Backbone, Drupal, drupalSettings, _);