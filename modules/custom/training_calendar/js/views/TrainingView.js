/**
 * @file
 * A Backbone view for the Wizard.
 */

(function (Backbone, Drupal, $, _) {


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
            this.model.set("title", title, {silent : true});

            //let newDistance = this.model.get("field_total_distance") + 100;
            //this.model.set("field_total_distance", newDistance, {silent : true});
        }
    });


})(Backbone, Drupal, jQuery, _);