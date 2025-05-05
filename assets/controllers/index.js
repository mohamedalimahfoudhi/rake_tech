import { Application } from 'stimulus';
import { definitionsFromContext } from 'stimulus/webpack-helpers';

// Import controllers here
import CalendarController from './calendar_controller';

// Create a new Stimulus application instance
const application = Application.start();

// Automatically load all controllers from the controllers directory
const context = require.context('./', true, /\.js$/);
application.load(definitionsFromContext(context));

// Register the specific controller for FullCalendar
application.register('calendar', CalendarController);
