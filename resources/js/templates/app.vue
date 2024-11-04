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

                <transition name="expand" mode="out-in">
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
                </transition>
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

        this.initializeCache();
        this.updateMainButton();
    },
    watch: {
        selectedTimes(newTimes) {
            this.updateMainButton();
        }
    },
    methods: {
        async initializeCache() {
            try {
                let response = await axios.post('api/initialize-cache');
                const eventsByDate = response.data;
                localStorage.setItem('eventsByDate', JSON.stringify(eventsByDate));
                console.log('Cache initialized successfully');
            } catch (error) {
                console.error('Error initializing cache:', error);
            }
        },

        handleTimeData(response) {
            this.availableSlots.consultation = response.consultation || {};
            this.availableSlots.reception = response.reception || {};

            this.selectedTimes = this.selectedTimes.filter(time =>
                this.availableSlots.consultation[time] || this.availableSlots.reception[time]
            );

            this.updateMainButton();
        },
        handleDateSelection(date) {
            this.selectedDate = date;
        },
        toggleTime(time) {
            const index = this.selectedTimes.indexOf(time);
            if (index > -1) {
                this.selectedTimes = [
                    ...this.selectedTimes.slice(0, index),
                    ...this.selectedTimes.slice(index + 1)
                ];
            } else {
                this.selectedTimes = [...this.selectedTimes, time];
            }
        },
        updateMainButton(){
            let tg = window.Telegram.WebApp;
            if (this.selectedTimes.length > 0) {
                tg.MainButton.show();
                tg.MainButton.text = "Продолжить";
                tg.MainButton.onClick(() => {
                    this.handleMainButtonClick();
                });
            } else {
                tg.MainButton.hide();
            }
        },
    },
};
</script>

<style>
.fade-slide-enter-active, .fade-slide-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-slide-enter, .fade-slide-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
.block_time.expanded {
    max-height: 500px; /* Установите нужную максимальную высоту */
}

.title_close {
    margin-bottom: 0;
    color: #555;
}
</style>
