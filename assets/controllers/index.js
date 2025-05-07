// Importer d'abord le composant principal
import CalendarController from './calendar_controller.js';
export { CalendarController };



// Initialiser l'application Stimulus
const application = Application.start();

// Charger les contrôleurs
const context = require.context('./', true, /\\.js$/);
application.load(definitionsFromContext(context));

// Enregistrer le contrôleur du calendrier
application.register('calendar', CalendarController);