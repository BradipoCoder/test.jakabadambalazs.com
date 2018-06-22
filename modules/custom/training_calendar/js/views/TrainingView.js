/**
 * @file
 * A Backbone view for the Wizard.
 */

(function ($, Backbone, Drupal) {


    Drupal.trainingCalendar.TrainingView = Backbone.View.extend({
        tagName: 'div',
        template: _.template($('#template--training-view').html()),

        events: {
            'click .training-calendar--event--action-1': 'doFunkyStuff',
        },

        initialize: function initialize() {
            this.listenTo(this.model, 'change', this.render);
        },

        render: function() {
            this.$el.html(this.template(this.model.toJSON()));

            return this;
        },

        doFunkyStuff: function onNextClick(event) {
            this.model.doSomething();
            event.preventDefault();
            event.stopPropagation();
        },
    });


    Drupal.trainingCalendar.TrainingList = Backbone.View.extend({
        el: $('#trainings-list-container'),


        template: _.template($('#template--trainings-list').html()),
        /*template: _.template('<ul></ul>'),*/

        initialize: function() {
            self = this;
            this.collection.fetch({
                success: function() {
                    self.render();
                }
            });
        },

        render: function() {
            this.el.innerHTML = this.template();
            let $list = this.$el.find('div.trainings');
            this.collection.forEach( function(model) {
                $list.append((new Drupal.trainingCalendar.TrainingView({ model: model })).render().el);
            }, this);

            return this;
        },
    });

    /*
    Drupal.trainingCalendar.ExampleToDelete = Backbone.View.extend({
        events: {
            'click .my-wizard--next-page': 'onNextClick',
            'click .my-wizard--previous-page': 'onPrevClick',
        },

        /**
         * Backbone view for the Wizard.
         *
         * @constructs
         *
         * @augments Backbone.View
         * /
        initialize: function initialize() {
            this.listenTo(this.model, 'change', this.render);
        },

        /**
         * @inheritdoc
         *
         * @return {Drupal.trainingCalendar.TrainingView}
         *   The `WizardView` instance.
         * /
        render: function render() {
            var total = this.model.get('pages').length;
            var active_page = this.model.get('activePage');

            // Hide/show pages.
            this.model.get('pages').each(function (index, value) {
                index++;
                var isCurrentPage = (index == active_page);
                $(value).toggleClass('hidden', !isCurrentPage);
            });

            // Update UI.
            this.$el.find('.my-wizard--current-page')
                .html(active_page);
            this.$el.find('.my-wizard--total-pages')
                .html(total);

            // Toggle Next/Prev buttons.
            this.$el.find('.my-wizard--next-page')
                .prop('disabled', (active_page == total));
            this.$el.find('.my-wizard--previous-page')
                .prop('disabled', (active_page == 1));

            return this;
        },
        onNextClick: function onNextClick(event) {
            this.model.doSomething();
            event.preventDefault();
            event.stopPropagation();
        },
        onPrevClick: function onPrevClick(event) {
            this.model.doSomething();
            event.preventDefault();
            event.stopPropagation();
        }
    });
    */

})(jQuery, Backbone, Drupal);