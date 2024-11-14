<template>
    <div class="calendar_block">
        <table id="calendar">
            <thead>
            <tr class="row_title_calendar">
                <td class="arrow" style="transform: rotateY(180deg)" @click="prevMonth"><svg fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" viewBox="0 0 24 24" width="24"><polyline points="9 18 15 12 9 6"/></svg></td>
                <td colspan="5" class="title_calendar title">{{ monthName }}</td>
                <td class="arrow" @click="nextMonth"><svg fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" viewBox="0 0 24 24" width="24"><polyline points="9 18 15 12 9 6"/></svg></td>
            </tr>
            <tr class="row_deys_calendar">
                <td>Пн</td>
                <td>Вт</td>
                <td>Ср</td>
                <td>Чт</td>
                <td>Пт</td>
                <td>Сб</td>
                <td>Вс</td>
            </tr>
            </thead>
            <tbody class="body_calendar">
            <tr v-for="(week, index) in calendar" :key="index">
                <td class="date_btn"
                    v-for="(day, idx) in week"
                    :key="idx"
                    :class="{ today: isToday(day), selected: isSelected(day), weekend: isWeekend(idx) && day, past: isPastDate(day)}"
                    @click="selectDate(day, idx)"
                >

                    {{ isWeekend(idx) && day ? `·` : (day || '') }}
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import {ref, computed} from 'vue';
import axios from "axios";
import {formatDate} from "../../convert-data.js"
import {getAvailableTimeSlots} from "../../getEventsCalendar.js"

export default {
    name: 'Calendar',
    emits: ['dateSelected', 'timeData'],
    setup(_, {emit}) {
        const today = new Date();
        const year = ref(today.getFullYear());
        const month = ref(today.getMonth());
        const selectedDate = ref(null);

        const monthNames = [
            'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
        ];

        const monthName = computed(() => monthNames[month.value]);

        const calendar = computed(() => {
            const Dlast = new Date(year.value, month.value + 1, 0).getDate();
            const DNfirst = new Date(year.value, month.value, 1).getDay();
            const startOffset = DNfirst === 0 ? 6 : DNfirst - 1;

            const days = [];
            for (let i = 0; i < startOffset; i++) days.push(null);
            for (let i = 1; i <= Dlast; i++) days.push(i);

            const weeks = [];
            while (days.length) weeks.push(days.splice(0, 7));

            return weeks;
        });

        const isToday = (day) =>
            day === today.getDate() &&
            month.value === today.getMonth() &&
            year.value === today.getFullYear();

        const isWeekend = (dayIndex) => dayIndex === 5 || dayIndex === 6;
        const isSelected = (day) => {
            return selectedDate.value &&
                selectedDate.value.day === day &&
                selectedDate.value.month === month.value + 1 &&
                selectedDate.value.year === year.value;
        };

        const prevMonth = () => {
            if (month.value === 0) {
                month.value = 11;
                year.value -= 1;
            } else {
                month.value -= 1;
            }
        };
        const nextMonth = () => {
            if (month.value === 11) {
                month.value = 0;
                year.value += 1;
            } else {
                month.value += 1;
            }
        };

        const selectDate = async (day, idx) => {
            if (day && !isWeekend(idx) && !isPastDate(day)) {
                if (selectedDate.value &&
                    selectedDate.value.day === day &&
                    selectedDate.value.month === month.value + 1 &&
                    selectedDate.value.year === year.value) {
                    selectedDate.value = null;
                    emit('dateSelected', selectedDate.value);
                    emit('timeData', []);
                } else {
                    selectedDate.value = {day, month: month.value + 1, year: year.value};
                    const formattedDate = formatDate(selectedDate.value);

                    let events = getAvailableTimeSlots(formattedDate);
                    emit('dateSelected', selectedDate.value);
                    emit('timeData', events);
                }
            }
        };

        const isPastDate = (day) => {
            const selectedDay = new Date(year.value, month.value, day);
            return selectedDay < today && !isToday(day);
        };

        return {
            year,
            month,
            monthName,
            calendar,
            selectedDate,
            isToday,
            prevMonth,
            nextMonth,
            selectDate,
            isWeekend,
            isSelected,
            isPastDate,

        };
    },
};
</script>

<style>
#calendar .body_calendar tr .weekend {
    font-size: 30px;
    font-weight: bold;
    color: #555;
    cursor: default;
}

#calendar tbody .past {
    color: #555;
    cursor: default;
    pointer-events: none;
}

.selected {
    transform: perspective(600px) rotateX(20deg) rotateY(360deg);
    background-color: #1D7BF6;
}

.date_btn {
    border-radius: 5px;
    transition: all 0.3s;
}
</style>
