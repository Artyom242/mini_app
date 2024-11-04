<template>
    <!--Блоки Цена/Отзывы/Мои записи-->
    <div class="flex row gap_15">
        <div class="flex blocks container_cards">
            <router-link class="block_card" to="/prices">Цены</router-link>
            <router-link class="block_card" to="/reviews">Отзывы</router-link>
        </div>
        <router-link class="block_card block_card__blue flex container_cards" to="/my-records">
            <p>Мои<br>Записи</p>
            <div class="arrow_card flex center"><span>›</span></div>
        </router-link>
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
                                @click="toggleTime('08:45')" class="btn_time">8:45
                        </button>
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
                            <button v-if="available" class="btn_time"
                                    :class="{ selected: selectedTimes.includes(time) }"
                                    @click="toggleTime(time)">{{ time }}
                            </button>
                        </template>
                    </div>

                </div>
            </div>
        </transition>
    </div>

    <transition name="fade">
        <div v-if="isModalOpen" class="modal-overlay" @click="closeModal">
            <div class="modal" @click.stop>
                <h5 class="title modal_form-title">Осталось совсем немного</h5>
                <div class="modal_form flex column">
                    <div class="flex column modal_form_pole">
                        <label ref="nameLabel" class="modal_form_label" :class="{ 'active': !name && !isNameFocused }"  for="name">Имя</label>
                        <input class="input" type="text" v-model="name" @focus="onFocus('name')" @blur="onBlur('name')" />
                    </div>
                    <div class="flex column modal_form_pole">
                        <label ref="phoneLabel" class="modal_form_label" :class="{ 'active': !phone && !isPhoneFocused }"  for="phone">Телефон</label>
                        <MaskInput class="input" mask="+7 (###) ### ##-##" type="tel" v-model="phone"
                                  @focus="onFocus('phone')"
                                  @blur="onBlur('phone')"></MaskInput>
<!--                        <input class="input" type="text" v-model="phone"-->
<!--                               @focus="onFocus('phone')"-->
<!--                               @blur="onBlur('phone')" />-->
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script >
import Calendar from './components/calendar.vue';
import { MaskInput } from 'vue-3-mask';
export default {
    name: 'Home',
    components: {
        Calendar,
        MaskInput,
    },

    data() {
        return {
            selectedTimes: [],
            selectedDate: null,
            availableSlots: {
                consultation: {},
                reception: {}
            },
            isModalOpen: false,
            name: '',
            phone: '',
            isNameFocused: false,
            isPhoneFocused: false,
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
        updateMainButton() {
            let tg = window.Telegram.WebApp;
            if (this.selectedTimes.length > 0) {
                tg.MainButton.show();
                tg.MainButton.text = "Продолжить";
                tg.MainButton.onClick(() => {
                    this.openModal();
                });
            } else {
                tg.MainButton.hide();
            }
        },
        openModal() {
            this.isModalOpen = true;
            let tg = window.Telegram.WebApp;
            tg.MainButton.text = "Записаться";
        },
        closeModal() {
            this.isModalOpen = false;
            let tg = window.Telegram.WebApp;
            tg.MainButton.text = "Продолжить";
        },
        onFocus(field) {
            if (field === 'name') {
                this.isNameFocused = true;  // Установите флаг фокуса
            } else if (field === 'phone') {
                this.isPhoneFocused = true;  // Установите флаг фокуса
            }
        },
        onBlur(field) {
            if (field === 'name') {
                this.isNameFocused = false;  // Сбросьте флаг фокуса
            } else if (field === 'phone') {
                this.isPhoneFocused = false;  // Сбросьте флаг фокуса
            }
        }
    },
};
</script>

<style>

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    z-index: 1000;
    transition: all 0.3s ease;
}

.modal {
    background: var(--tg-theme-bg-color);
    padding: 30px 12px 15px;
    width: 100%;
    position: absolute;
    bottom: 0;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}

.modal-slide-enter-from,
.modal-slide-leave-to {
    transform: translateY(100px);
}

.modal-slide-enter-to,
.modal-slide-leave-from {
    transform: translateY(0);
}

.title_close {
    margin-bottom: 0;
    color: #555;
}

.modal_form_pole {
    padding: 5px 0;
}

.modal_form-title {
    color: white;
    font-weight: 700;
    text-align: left;
    margin-bottom: 20px;
}

.modal_form_label {
    color: var(--tg-theme-subtitle-text-color);
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s ease;
    cursor: default;
}

.modal_form_label.active {
    font-size: 16px;
    transform: translateY(17px);
}

.input {
    background: none;
    z-index: 10;
    padding: 7px 0;
    color: white;
    font-weight: 500;
    border-bottom: 1px solid var(--tg-theme-section-separator-color);
}

input:focus {
    outline: none;
}

</style>
