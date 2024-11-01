<template>
    <section class="section_white">
        <div class="__container wrapper_calendar flex gap_15">

            <!--Блоки Цена/Отзывы/Мои записи-->
            <div class="flex row gap_15">
                <div class="flex blocks container_cards">
                    <a href="#" class="block_card">
                        Цены
                    </a>
                    <a href="#" class="block_card">
                        Отзывы
                    </a>
                </div>
                <a href="#" class="block_card block_card__blue flex container_cards ">
                    <p> Мои <br>Записи</p>
                    <div class="arrow_card flex center"><span>›</span></div>
                </a>
            </div>

            <div class="container_calendar flex">

                <Calendar @timeData="handleTimeData"
                          @dateSelected="handleDateSelection"
                ></Calendar>

                <div v-if="!selectedDate" class="flex column block_time">
                    <h4 class="title_block_time">Выберите дату</h4>
                </div>

                <div v-else class="flex column block_time">
                    <div v-if="availableSlots.consultation['08:45']">
                        <h4 class="title_block_time">Консультация</h4>
                        <div class="flex row">
                            <button :class="{ selected: selectedTimes.includes('08:45') }"
                                    @click="toggleTime('08:45')" class="btn_time">8:45</button>
                        </div>
                    </div>
                    <div v-else>
                        <h4 class="title_close title_block_time">Консультация занята</h4>
                    </div>
                    <div>
                        <h4 class="title_block_time">Прием</h4>
                        <div class="flex row block_time_btns">
                            <template v-for="(available, time) in availableSlots.reception"
                                      class="btn_time">
                                <button v-if="available" class="btn_time" :class="{ selected: selectedTimes.includes(time) }"
                                        @click="toggleTime(time)" >{{ time }}</button>
                            </template>
                        </div>

                    </div>
                </div>

                <!--                <div class="flex row block_zapis">-->
                <!--                    <button class="btn_zapis btn_zapis_active">Прием</button>-->
                <!--                    <button class="btn_zapis">Консультация</button>-->
                <!--                </div>-->
            </div>
        </div>
    </section>
</template>
<script>
import Calendar from './calendar.vue';

export default {
    name: 'App',
    components: {
        Calendar,
    },

    data() {
        return {
            selectedTimes: [],
            selectedDate: null,
            availableSlots: {
                consultation: {},
                reception: {}
            },
        };
    },
    mounted() {
        let tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();
        tg.MainButton.show();
        tg.MainButton.text = "Записаться";
        tg.MainButton.onClick(() => {
            this.handleMainButtonClick();
        });
    },

    methods: {

        handleTimeData(response) {
            this.availableSlots.consultation = response.consultation || {};
            this.availableSlots.reception = response.reception || {};
            console.log(this.selectedTimes)
        },
        handleDateSelection(date) {
            this.selectedDate = date;
            this.filterUnavailableTimes();
        },
        toggleTime(time) {
            const index = this.selectedTimes.indexOf(time);
            if (index > -1) {
                this.selectedTimes.splice(index, 1);
            } else {
                this.selectedTimes.push(time);
            }
        },
        filterUnavailableTimes() {
            this.selectedTimes = this.selectedTimes.filter(time =>
                this.availableSlots.consultation[time] || this.availableSlots.reception[time]
            );
        },
    },
};
</script>

<style>
.title_close {
    margin-bottom: 0;
    color: #555;
}
</style>
