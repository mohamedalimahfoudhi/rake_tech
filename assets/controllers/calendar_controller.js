import { Controller } from 'stimulus';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

export default class extends Controller {
  static targets = ['calendar'];

  connect() {
    // Initialize FullCalendar with the necessary plugins
    const calendar = new Calendar(this.calendarTarget, {
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      events: JSON.parse('{{ events|json_encode() }}'), // Pass Symfony events
      editable: true,
      droppable: true,
      eventClick: function(info) {
        const { event } = info;
        alert('Event: ' + event.title);
      },
    });

    // Render the calendar
    calendar.render();
  }
}
