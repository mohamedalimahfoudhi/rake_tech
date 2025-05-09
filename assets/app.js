// Stimulus setup
import './bootstrap';  // Your bootstrap file
import { Application } from 'stimulus';
import { definitionsFromContext } from 'stimulus/webpack-helpers';
import CalendarController from './controllers/calendar_controller';

// Leaflet setup
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Create a Stimulus application instance
const application = Application.start();
const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));
application.register('calendar', CalendarController);

// Leaflet map initialization
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

document.addEventListener('DOMContentLoaded', function () {
  const mapElement = document.getElementById('map');
  if (mapElement) {
    const map = L.map(mapElement).setView([36.8, 10.1], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    L.marker([36.8, 10.1]).addTo(map).bindPopup("Tunis").openPopup();
  }
});
