import './bootstrap';  // Your bootstrap file
import { Application } from 'stimulus';
import { definitionsFromContext } from 'stimulus/webpack-helpers';

// Import controllers from the controllers directory
import CalendarController from './controllers/calendar_controller';

// Create a Stimulus application instance
const application = Application.start();

// Automatically load all controllers from the controllers directory
const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));

// Register FullCalendar controller
application.register('calendar', CalendarController);
