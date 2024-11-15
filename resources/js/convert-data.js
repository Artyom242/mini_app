// Функция для форматирования даты
export function formatDate(date) {
    const year = date.year;
    const month = String(date.month).padStart(2, '0');
    const day = String(date.day).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

export function groupEvents(events) {
    if (!Array.isArray(events)) {
        console.error("Expected an array of events, but got:", events);
        return [];
    }
    const grouped = {};
    const monthNames = [
        'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня',
        'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'
    ];

    events.forEach(event => {
        const [date, time] = event.start.split(' ');
        const [year, month, day] = date.split('-');

        const dayNumber = parseInt(day, 10);
        const monthName = monthNames[parseInt(month, 10) - 1];

        if (!grouped[date]) {
            grouped[date] = {
                day: dayNumber,
                month: monthName,
                slots: []
            };
        }

        grouped[date].slots.push(time);
    });

    return Object.values(grouped);
}
