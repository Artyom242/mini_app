<template>
    <div v-if="isLoading">
        <Spinner></Spinner>
    </div>
    <div v-else>
        <template v-if="hasEvents">
            <div v-if="upcomingGroupedEvents.length" class="block_card block_card-blue">
                <h1 class="block_title">Актуальные записи</h1>
                <div class="container_my_events flex">
                    <div v-for="event in upcomingGroupedEvents" :key="event.date"
                         class="block_card block_card-white flex flex_column blocks_my_events">
                        <p class="date_my_events">{{ formatDateYMD(event.date) }}</p>

                        <!-- Консультации -->
                        <div v-if="event.slots.Consultation.length" class="flex row block_card_event">
                            <div class="block_card_event_btns_times">
                                <h4>Консультация:</h4>
                            </div>
                            <div class="btns_times flex row block_card_event_btns_times">
                                <button v-for="time in event.slots.Consultation" :key="time"
                                        class="btn_time selectedTimes">
                                    {{ time }}
                                </button>
                            </div>
                        </div>

                        <!-- Запись на прием -->
                        <div v-if="event.slots['Appointment'].length" class="flex row block_card_event">
                            <div class="block_card_event_btns_times">
                                <h4>Запись на прием:</h4>
                            </div>
                            <div class="btns_times flex row block_card_event_btns_times">
                                <button v-for="time in event.slots['Appointment']" :key="time"
                                        class="btn_time selectedTimes">
                                    {{ time }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Прошедшие записи -->
            <div v-if="pastGroupedEvents.length" class="block_card block_card-old">
                <h1 class="block_title">Прошедшие записи</h1>
                <div class="container_my_events flex">
                    <div v-for="event in pastGroupedEvents" :key="event.date"
                         class="block_card block_card-white flex flex_column blocks_my_events">
                        <p class="date_my_events">{{ formatDateYMD(event.date) }}</p>

                        <div v-if="event.slots.Consultation.length" class="flex row block_card_event">
                            <div class="block_card_event_btns_times">
                                <h4>Консультация:</h4>
                            </div>
                            <div class="btns_times flex row block_card_event_btns_times">
                                <button v-for="time in event.slots.Consultation" :key="time"
                                        class="btn_time selectedTimes">
                                    {{ time }}
                                </button>
                            </div>
                        </div>

                        <div v-if="event.slots['Appointment'].length" class="flex row block_card_event">
                            <div class="block_card_event_btns_times">
                                <h4>Запись на прием:</h4>
                            </div>
                            <div class="btns_times flex row block_card_event_btns_times">
                                <button v-for="time in event.slots['Appointment']" :key="time"
                                        class="btn_time selectedTimes">{{ time }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Если нет записей -->
        <div v-else class="non_events">
            <p>У вас пока нет записей</p>
        </div>
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
        formatDateYMD,
        async loadEvents(userId) {
            try {
                const response = await axios.post('/api/get-events', {id_user: userId});
                this.upcomingEvents = Object.values(response.data.upcoming_events || []).sort((a, b) => {
                    return new Date(b.start) - new Date(a.start);
                });
                this.pastEvents = Object.values(response.data.past_events || []).sort((a, b) => {
                    return new Date(b.start) - new Date(a.start);
                });
            }
            catch (error) {
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

            events.forEach(event => {
                const [date, time] = event.start.split(' ');
                const type = time === '08:45' ? "Consultation" : "Appointment";

                if (!grouped[date]) {
                    grouped[date] = {
                        date,
                        slots: {Consultation: [], Appointment: []}
                    };
                }
                grouped[date].slots[type].push(time);
            });

            return Object.values(grouped);
        }
    }
}
</script>

<style scoped>
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
}

.non_events {
    position: absolute;
    top: 50%;
    text-align: center;
    min-height: auto;
    color: #9ca3af;
    font-size: 13px;
}

.block_card-old {
    margin-top: 20px;
    background: none;
}
</style>
