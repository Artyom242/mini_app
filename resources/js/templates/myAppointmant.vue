<template>
    <div class="section_start_title">
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
                                            <h2 class="title_big">{{ time }}</h2>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                </div>

            </template>

            <!-- Если нет записей -->
            <div v-else class="flex center column non_events">
                <img class="img" style="width: 150px;" :src="`/images/my-ev.webp`">
                <p class="little_text">У вас пока нет записей</p>
            </div>
        </template>
    </div>
</template>

<script>
import axios from 'axios';
import Spinner from "./components/spinner.vue";
import {groupEvents} from "@/convert-data.js";

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
            return this.upcomingEvents ? groupEvents(this.upcomingEvents) : [];
        },
        pastGroupedEvents() {
            return this.pastEvents ? groupEvents(this.pastEvents) : [];
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
    }
}
</script>

<style scoped>
.img_check {
    top: -20px;
    right: 10px;
    width: 130px;
    transform: rotate(-15deg);
}

.non_events {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
}
</style>
