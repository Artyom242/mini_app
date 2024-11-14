<template>
    <div class="section_start_title my_events_section">
        <div v-if="isLoading">
            <Spinner></Spinner>
        </div>
        <template v-else>
            <template v-if="hasEvents">

                <div v-if="upcomingGroupedEvents.length">
                    <h1 class="title_big margin_title ml_5">Мои записи</h1>

                    <div class="flex column gap_5">
                        <template v-for="(event, index) in upcomingGroupedEvents" :key="event.date">
                            <template v-for="(time) in event.slots">
                                <div class="block_card">
                                    <div class="two_block_card row flex flex-jc">
                                        <div class="flex row center gap_10">
                                            <h2 class="title_big ml_5">{{ event.day }}</h2>
                                            <div>
                                                <h4 class="title" style="margin-bottom: 4px">{{ event.month }}</h4>
                                                <p v-if="time === '8:45'" class="text-grey">Консультация</p>
                                                <p v-else class="text-grey">Прием</p>
                                            </div>
                                        </div>
                                        <div :class="['block_card', { 'block_card-blue': index === upcomingGroupedEvents.length - 1, 'border_blue': index !== upcomingGroupedEvents.length - 1}]" class="flex center" style="max-width: 130px">
                                            <h2 class="title_big">{{time}}</h2>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                </div>

                <!-- Прошедшие записи -->
                <div class="section_mt container_rel" v-if="pastGroupedEvents.length">
                    <h1 class="title_big margin_title ml_5">Прошедшие <br> записи</h1>
                    <img class="img_fon img_check" :src="`/images/my-check.webp`">

                    <div class="flex column gap_5">
                        <template v-for="event in pastGroupedEvents" :key="event.date">
                            <template v-for="(time, index) in event.slots">
                                <div class="block_card">
                                    <div class="two_block_card row flex flex-jc">
                                        <div class="flex row center gap_10">
                                            <h2 class="title_big ml_5">{{ event.day }}</h2>
                                            <div>
                                                <h4 class="title" style="margin-bottom: 4px">{{ event.month }}</h4>
                                                <p v-if="time === '8:45'" class="text-grey">Консультация</p>
                                                <p v-else class="text-grey">Прием</p>
                                            </div>
                                        </div>
                                        <div class="block_card flex center" style="max-width: 130px">
                                            <h2 class="title_big text-grey">{{ time }}</h2>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                </div>

            </template>

            <!-- Если нет записей -->
            <div v-else class="non_events flex center column">
                <img class="img" style="width: 150px;" :src="`/images/my-ev.webp`">
                <p class="text-grey">У вас пока нет записей</p>
            </div>
        </template>
    </div>
</template>

<script>
import axios from 'axios';
import Spinner from "./components/spinner.vue";
import {formatDateYMD} from "../convert-data.js";

export default {
    name: 'MyEvents',
    components: {Spinner},
    data() {
        return {
            upcomingEvents: [],
            pastEvents: [],
            isLoading: true,
        };
    },
    computed: {
        upcomingGroupedEvents() {
            return this.upcomingEvents ? this.groupEvents(this.upcomingEvents) : [];
        },
        pastGroupedEvents() {
            return this.pastEvents ? this.groupEvents(this.pastEvents) : [];
        },
        hasEvents() {
            return this.upcomingGroupedEvents.length || this.pastGroupedEvents.length;
        }
    },
    mounted() {
        let tg = window.Telegram.WebApp;
        tg.BackButton.show();
        tg.BackButton.onClick(function () {
            window.history.back();
            tg.BackButton.hide();
        });

        tg.MainButton.hide();

        const userId = tg.initDataUnsafe.user.id;
        this.loadEvents(userId);
    },
    methods: {
        async loadEvents(userId) {
            try {
                const response = await axios.post('/api/get-events', {id_user: userId});
                this.upcomingEvents = Object.values(response.data.upcoming_events || []).sort((a, b) => {
                    return new Date(b.start) - new Date(a.start);
                });
                this.pastEvents = Object.values(response.data.past_events || []).sort((a, b) => {
                    return new Date(b.start) - new Date(a.start);
                });
            } catch (error) {
                console.log("Error loading events:", error);
            } finally {
                this.isLoading = false;
            }
        },
        groupEvents(events) {
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
    }
}
</script>

<style scoped>
.width_100 {
    width: 100%;
}

.img_check {
    top: -20px;
    right: 10px;
    width: 130px;
    transform: rotate(-15deg);
}

.my_events_section .little_text {
    color: white;
    margin-bottom: 0;
    font-weight: normal;
}

.bottom-20 {
    margin-bottom: 20px;
}

.blocks_my_events {
    gap: 10px;
}

.block_card_event:not(:last-child) {
    padding-bottom: 10px;
    border-bottom: 1px solid #BBBBBB;
}

.block_card_event_btns_times {
    width: 50%;
}

.date_my_events {
    color: #575757;
    font-size: 13px;
}

.container_my_events {
    gap: 10px;
    min-height: 100px;
}

.non_events {
    position: absolute;
    left: 0;
    top: 50%;
    text-align: center;
}

.block_card-none-bg {
    background: none;
}
</style>
