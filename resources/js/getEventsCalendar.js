export function getAvailableTimeSlots(selectedDate) {
    const consultationSlots = { '08:45': true };
    const receptionSlots = {
        '09:00': true,
        '10:00': true,
        '11:00': true,
        '12:00': true,
        '13:00': true,
        '14:00': true,
        '15:00': true,
        '16:00': true,
    };

    const eventsByDate = JSON.parse(localStorage.getItem('eventsByDate') || '{}');
    const events = eventsByDate[selectedDate] || [];

    const occupiedSlots = events.map(event => ({
        start: event.startDateTime,
        end: event.endDateTime,
    }));

    occupiedSlots.forEach(occupied => {
        Object.keys(consultationSlots).forEach(slot => {
            const slotEnd = addMinutes(slot, 15);
            if (timeOverlaps(slot, slotEnd, occupied.start, occupied.end)) {
                consultationSlots[slot] = false;
            }
        });
    });

    // Проверка занятости слотов приема
    occupiedSlots.forEach(occupied => {
        Object.keys(receptionSlots).forEach(time => {
            const slotEnd = addMinutes(time, 60);
            if (timeOverlaps(time, slotEnd, occupied.start, occupied.end)) {
                receptionSlots[time] = false;
            }
        });
    });

    return {
        consultation: consultationSlots,
        reception: receptionSlots,
    };
}

function timeOverlaps(start1, end1, start2, end2) {
    return (start1 < end2 && start2 < end1);
}

function addMinutes(time, minutesToAdd) {
    const [hours, minutes] = time.split(':').map(Number);
    const newMinutes = minutes + minutesToAdd;
    const newHours = hours + Math.floor(newMinutes / 60);
    return `${String(newHours).padStart(2, '0')}:${String(newMinutes % 60).padStart(2, '0')}`;
}
