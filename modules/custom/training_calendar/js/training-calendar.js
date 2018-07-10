/**
 * @file
 */
(function(Drupal)
{

    //------------------------------------------------------------------------------------------------------------Drupal
    Drupal.trainingCalendar = Drupal.trainingCalendar || {
        /**
         * Declare namespaces
         */
        Utilities: {},
        models: {},
        views: {},

        /**
         * Initialize trainingCalendar
         */
        init: function()
        {
            console.log("Starting Training Calendar...");

            let classesToInit = [
                Drupal.trainingCalendar.Utilities.DrupalSettingsManager.init,
                Drupal.trainingCalendar.Utilities.CommunicationManager.init,
                Drupal.trainingCalendar.Utilities.TokenManager.init,
                Drupal.trainingCalendar.Utilities.ModelManager.init,
                Drupal.trainingCalendar.Utilities.ViewManager.init,
            ];

            Promise.reduce(classesToInit, function(accumulator, initMethod)
            {
                /** @type {Promise<any>} initPromise */
                let initPromise = initMethod.call();
                initPromise.then(function(initMessage) {
                    if(initMessage)
                    {
                        console.log(initMessage);
                    }
                }).catch(function(e) {
                    console.error(e);
                });
                return initPromise;
            }, null).then(function()
            {
                console.log("Training Calendar is initialized and ready for use.");
                Drupal.trainingCalendar.Utilities.ViewManager.overlayHide();
            });
        }
    };

    //--------------------------------------------------------------------------------------------------------------INIT
    Drupal.behaviors.trainingCalendar = {
        attach: function attach()
        {
            Drupal.trainingCalendar.init();
        }
    };
})(Drupal);

