// assets/controllers/calendar_controller.js
import { Controller } from '@hotwired/stimulus';
import { Calendar }   from '@fullcalendar/core';
import dayGridPlugin     from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

export default class extends Controller {
  connect() {
    this.calendar = new Calendar(this.element, {
      plugins:     [ dayGridPlugin, interactionPlugin ],
      initialView: 'dayGridMonth',
      events:      '/api/emprunts',
    });
    this.calendar.render();
  }
}
