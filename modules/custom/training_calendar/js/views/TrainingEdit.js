/**
 * @file
 * A Backbone view for the Wizard.
 */

(function(Backbone, Drupal, $, _, moment)
{
    //----------------------------------------------------@todo: move me elsewhere
    Backform.MomentFormatter = function()
    {
    };
    _.extend(Backform.MomentFormatter.prototype, {
        fromRaw: function(rawData, model)
        {
            let d = model.get("field_start_date");
            let formattedData = d.format("YYYY-MM-DD");
            console.warn("MF(fromRaw):", formattedData);
            return formattedData;
        },
        toRaw: function(formattedDate, model)
        {
            formattedDate = formattedDate + 'T12:00:00.000Z';
            let rawDate = moment(formattedDate);
            console.warn("MF(toRaw):" + formattedDate + " - " + rawDate);
            return rawDate;
        }
    });

    /*@see: https://www.eyecon.ro/bootstrap-datepicker */
    Backform.DatepickerMomentControl = Backform.DatepickerControl.extend({
        formatter: Backform.MomentFormatter,
    });
    //----------------------------------------------------@todo: move me elsewhere


    let editFormFields = [
        {
            name: "title",
            label: "Title",
            control: "input",
            required: true,
        },
        {
            name: "field_activity_type",
            label: "Activity",
            control: "select",
            options: [
                {value: 3, label: "Run"},
                {value: 4, label: "Bike"},
                {value: 5, label: "Swim"},
                {value: 6, label: "Walk"},
            ],
            required: true,
        },
        {
            name: "field_start_date",
            label: "Date",
            control: Backform.DatepickerMomentControl,
            /*required: true,*/
            options: {
                format: 'yyyy-mm-dd',
                weekStart: 1,
            }
        },
        {
            name: "field_total_distance",
            label: "Distance(m)",
            control: "input",
            type: 'number',
            required: true,
            /*helpMessage: "Distance in meters."*/
        },
        {
            name: "body",
            label: "Instructions",
            control: "textarea",
        },

    ];


    Drupal.trainingCalendar.TrainingEdit = Backbone.Modal.extend({
        //tagName: 'div',
        //template: '#template--training-edit',
        template: _.template($('#template--training-edit').html()),
        submitEl: '.btn.submit-modal',
        cancelEl: '.btn.cancel-modal',


        initialize: function initialize()
        {
            Backbone.Modal.prototype.initialize.apply(this);
            //this.listenTo(this.model, 'change', this.render);
        },

        events: {
            'click .btn.delete-modal': 'deleteTraining',
        },

        deleteTraining: function()
        {
            if(!confirm("DELETE?"))
            {
                this.triggerCancel();
                return true;
            }

            let destroyOptions = {
                success: function(model, response, options)
                {


                    console.info('Calendar event was deleted.');
                    Drupal.trainingCalendar.Utilities.ViewManager.refetchEventsForCurrentView();
                },
                error: function(model, response, options)
                {
                    console.error('Error deleting calendar event!');
                    Drupal.trainingCalendar.Utilities.ViewManager.refetchEventsForCurrentView();
                }
            };

            this.triggerCancel();
            this.model.destroy(destroyOptions);
        },

        beforeSubmit: function()
        {
            return true;
        },

        submit: function()
        {
            let saveOptions = {
                success: function(model, response, options)
                {
                    if(model.isNew())
                    {
                        let storedData = _.first(response);
                        if(_.has(storedData, "id"))
                        {
                            model.set("id", storedData.id);
                            console.info('new model now has ID:' + model.getId());
                        }
                    }

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

})(Backbone, Drupal, jQuery, _, moment);