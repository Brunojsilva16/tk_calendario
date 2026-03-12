    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'pt-br', // Deixa tudo em português
            initialView: 'timeGridWeek', // Pode ser 'dayGridMonth', 'timeGridWeek', 'timeGridDay'
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: {
                today: 'Hoje',
                month: 'Mês',
                week: 'Semana',
                day: 'Dia',
                list: 'Lista'
            },
            navLinks: true, // permite clicar nos dias para ir para a visão diária
            editable: false, // Mude para true se quiser arrastar os eventos para mudar a data
            selectable: true,
            nowIndicator: true,
            slotMinTime: '10:00:00', // Horário que o calendário começa
            slotMaxTime: '23:59:00', // Horário que o calendário termina
            
            // Aqui é onde a mágica acontece: o FullCalendar vai automaticamente
            // chamar a sua rota enviando ?start=XX & end=YY
            events: '<?= URL_BASE ?>/api/sessoes/eventos',

            // Evento ao clicar em um espaço vazio (para agendar nova sessão)
            select: function(info) {
                // Exemplo: Redirecionar para página de cadastro passando a data na URL
                // window.location.href = '<?= URL_BASE ?>/sessoes/nova?data=' + info.startStr;
            },

            // Evento ao clicar em uma consulta já existente
            eventClick: function(info) {
                if (info.event.url) {
                    info.jsEvent.preventDefault(); // Evita que a página mude imediatamente
                    window.open(info.event.url, "_self"); // Abre o link na mesma aba
                }
            }
        });

        calendar.render();
    });