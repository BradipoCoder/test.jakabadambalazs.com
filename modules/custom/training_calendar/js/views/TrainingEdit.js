/**
 * @file
 * A Backbone view for the Wizard.
 */

(function (Backbone, Drupal, $, _)
{
    let editFormFields = [
        {
            name: "title",
            label: "Title",
            control: "input",
        },
        {
            name: "field_total_distance",
            label: "Distance",
            control: "input",
            helpMessage: "Distance in meters."
        }
    ];


    Drupal.trainingCalendar.TrainingEdit = Backbone.Modal.extend({
        //tagName: 'div',
        //template: '#template--training-edit',
        template: _.template($('#template--training-edit').html()),
        submitEl: '.btn.submit-model',


        initialize: function initialize() {
            Backbone.Modal.prototype.initialize.apply(this);
            //this.listenTo(this.model, 'change', this.render);
        },

        beforeSubmit:function()
        {
            return true;
        },

        submit:function()
        {
            let saveOptions = {
                success: function(model, response, options)
                {
                    console.info('Calendar event was saved.');
                    Drupal.trainingCalendar.Utilities.ViewManager.refetchEventsForCurrentView();
                },
                error: function(model, response, options)
                {
                    console.error('Error saving calendar event!');
                    Drupal.trainingCalendar.Utilities.ViewManager.refetchEventsForCurrentView();
                }
            };
            this.model.save(null, saveOptions);

            return true;
        },

        render: function()
        {
            Backbone.Modal.prototype.render.apply(this);

            /* Create and render the Backform into the modal*/
            let editForm = new Backform.Form(
                {
                    el: $('.bbm-modal__form', this.$el),
                    model: this.model,
                    fields: editFormFields,
                }
            );

            editForm.render();

            this.events = {
                'click .btn.submit-model': 'doFunkyStuff',
            };

            return this;
        },
    });

})(Backbone, Drupal, jQuery, _);