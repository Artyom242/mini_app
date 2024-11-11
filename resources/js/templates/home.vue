<template>
    <!--Блоки Цена/Отзывы/Мои записи-->
    <div class="flex row gap_10">
        <div class="flex blocks container_cards">
            <router-link class="block_card" to="/prices"><h1 class="block_title">Цены</h1></router-link>
            <router-link class="block_card" to="/reviews"><h1 class="block_title">Отзывы</h1></router-link>
        </div>
        <div class="flex blocks container_cards">
            <router-link class="block_card block_card__blue flex container_cards" to="/my-records">
                <h1 class="block_title">Мои<br>Записи</h1>
                <div class="arrow_card flex center"><span>›</span></div>
            </router-link>
        </div>
    </div>
    <div class="container_calendar flex">

        <Calendar @timeData="handleTimeData"
                  @dateSelected="handleDateSelection"
        ></Calendar>

        <transition name="expand" mode="out-in">
            <div v-if="!selectedDate" class="flex column block_time">
                <h4 class="little_title">Выберите дату</h4>
            </div>

            <div v-else class="flex column block_time">
                <div>
                    <template v-if="hasAvailableReceptionSlots">
                        <h4 class="little_title">Прием</h4>
                        <div class="flex row block_time_btns">
                            <template v-for="(available, time) in availableSlots.reception">
                                <button v-if="available" class="btn_time"
                                        :class="{ selected: selectedTimes.includes(time) }"
                                        @click="toggleTime(time)">{{ time }}
                                </button>
                            </template>
                        </div>
                    </template>
                    <div v-else>
                        <h4 class="title_close little_title">Все время для приема занято</h4>
                    </div>
                </div>
                <div v-if="availableSlots.consultation['08:45']">
                    <h4 class="little_title">Консультация</h4>
                    <div class="flex row">
                        <button :class="{ selected: selectedTimes.includes('08:45') }"
                                @click="toggleTime('08:45')" class="btn_time">8:45
                        </button>
                    </div>
                </div>
                <div v-else>
                    <h4 class="title_close little_title">Консультация занята</h4>
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
                        <label ref="nameLabel" class="modal_form_label" :class="{ 'active': !name && !isNameFocused }"
                               for="name">Имя</label>
                        <input class="input" type="text" v-model="name"
                               @input="updateMainButton"
                               @focus="onFocus('name')"
                               @blur="onBlur('name')"/>
                    </div>
                    <div class="flex column modal_form_pole">
                        <label ref="phoneLabel" class="modal_form_label"
                               :class="{ 'active': !phone && !isPhoneFocused }" for="phone">Телефон</label>
                        <MaskInput class="input" mask="+7 (###) ### ##-##" type="tel" v-model="phone"
                                   @input="updateMainButton"
                                   @focus="onFocus('phone')"
                                   @blur="onBlur('phone')"></MaskInput>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
import Calendar from './components/calendar.vue';
import {formatDate} from "../convert-data.js"
import {MaskInput} from 'vue-3-mask';

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
            isFirstHandlerActive: true,
        };
    },
    computed: {
        hasAvailableReceptionSlots() {
            return Object.values(this.availableSlots.reception).some(available => available);
        }
    },
    mounted() {
        let tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();

        this.initializeCache();
        this.updateMainButton();
    },
    watch: {
        selectedTimes() {
            this.updateMainButton();
        },
        name() {
            this.updateMainButton();
        },
        phone() {
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
        async checkEvents() {
            let tg = window.Telegram.WebApp;
            tg.MainButton.showProgress();

            try {
                let response = await axios.post('api/check-events', {
                    date: formatDate(this.selectedDate),
                    times: this.selectedTimes
                });
                const eventsByDate = response.data;
                tg.MainButton.hideProgress();

                if (eventsByDate.occupiedTimes.length === 0) {
                    await axios.post('/api/message', {
                        chat_id: tg.initDataUnsafe.user.id,
                        date: formatDate(this.selectedDate),
                        times: this.selectedTimes,
                        name: this.name,
                        phone: this.phone,
                    });

                    this.closeModal();
                    tg.close();

                } else {
                    let times = this.selectedTimes.join(', ');
                    let formattedDate = new Date(eventsByDate.date).toLocaleDateString('ru-RU');

                    tg.MainButton.hideProgress();
                    this.closeModal();
                    tg.showAlert(`К сожалению, на ${formattedDate} в выбранное время (${times}) уже есть запись. Пожалуйста, выберите другое время.`);
                }
            } catch (error) {
                tg.MainButton.hideProgress();
                this.closeModal();
                tg.showAlert("Произошла ошибка. Попробуйте позже.");
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

        handleFirstButtonClick() {
            this.openModal();
            this.isFirstHandlerActive = false;
            setTimeout(() => {
                this.updateMainButton();
            }, 0);
        },
        handleSecondButtonClick() {
            this.checkEvents();
        },
        openModal() {
            this.isModalOpen = true;
            let tg = Telegram.WebApp;
            tg.MainButton.setText("Записаться");
        },
        updateMainButton() {
            let tg = Telegram.WebApp;

            tg.MainButton.offClick(this.handleFirstButtonClick);
            tg.MainButton.offClick(this.handleSecondButtonClick);

            if (this.isFirstHandlerActive) {
                tg.MainButton.onClick(this.handleFirstButtonClick);
                tg.MainButton.setText("Продолжить");
                tg.MainButton.enable();
            } else {
                const isFormValid = this.name.trim() !== '' && this.phone.trim().length >= 18;
                if (isFormValid) {
                    tg.MainButton.enable();
                    tg.MainButton.onClick(this.handleSecondButtonClick);
                } else {
                    tg.MainButton.disable();
                }
            }

            // Проверяем, нужно ли показывать кнопку
            if (this.selectedTimes.length > 0) {
                tg.MainButton.show();
                tg.MainButton.color = "#55BC28";
            } else {
                tg.MainButton.hide();
            }
        },
        closeModal() {
            this.isModalOpen = false;
            this.isFirstHandlerActive = true;
            this.updateMainButton();
            this.phone = '';
        },
        onFocus(field) {
            if (field === 'name') {
                this.isNameFocused = true;
            } else if (field === 'phone') {
                this.isPhoneFocused = true;
            }
        },
        onBlur(field) {
            if (field === 'name') {
                this.isNameFocused = false;
            } else if (field === 'phone') {
                this.isPhoneFocused = false;
            }
        },
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
