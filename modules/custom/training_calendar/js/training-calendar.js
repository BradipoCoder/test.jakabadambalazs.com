/**
 * @file
 */
(function ($, Drupal)
{
    Drupal.trainingCalendar = Drupal.trainingCalendar || {
        models: {},
        views: {}
    };

    Drupal.behaviors.trainingCalendar = {
        attach: function attach(context, settings) {
            console.log("TRAINING CALENDAR...");
            let TrainingCalendarApp = new Drupal.trainingCalendar.TrainingList({
                    collection: new Drupal.trainingCalendar.TrainingModels
            });
        }
    };
})(jQuery, Drupal);
