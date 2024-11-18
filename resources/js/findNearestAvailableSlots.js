import { getAvailableTimeSlots } from "@/getEventsCalendar.js";

export async function findNearestAvailableSlots() {
    const today = new Date();
    let currentDate = today.toISOString().split('T')[0];
    let foundConsultation = null;
    let foundReception = null;

    function isWeekend(date) {
        const dayOfWeek = new Date(date).getDay();
        return dayOfWeek === 0 || dayOfWeek === 6;
    }

    while (!foundConsultation || !foundReception) {
        // Пропускаем выходные
        if (isWeekend(currentDate)) {
            currentDate = new Date(new Date(currentDate).getTime() + 86400000)
                .toISOString()
                .split('T')[0];
            continue;
        }

        const slots = getAvailableTimeSlots(currentDate);

        if (!foundConsultation && slots.consultation['08:45']) {
            foundConsultation = { date: currentDate, time: '08:45' };
        }

        if (!foundReception) {
            for (const time in slots.reception) {
                if (slots.reception[time]) {
                    foundReception = { date: currentDate, time };
                    break;
                }
            }
        }

        currentDate = new Date(new Date(currentDate).getTime() + 86400000)
            .toISOString()
            .split('T')[0];
    }

    return { consultation: foundConsultation, reception: foundReception };
}
