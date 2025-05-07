// assets/js/app.js

// 1️⃣ Importez d’abord le cœur
import { Calendar } from '@fullcalendar/core';

// 2️⃣ Puis vos plugins
import dayGridPlugin     from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', () => {
  const calendarEl = document.getElementById('calendar');
  const calendar   = new Calendar(calendarEl, {
    plugins:     [ dayGridPlugin, interactionPlugin ],
    initialView: 'dayGridMonth',
    events:      { url: '/api/emprunts', method: 'GET' },
    editable:    true,
    selectable:  true,
  });
  calendar.render();
});
