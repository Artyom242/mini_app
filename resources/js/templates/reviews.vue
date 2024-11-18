<template>
<div>
    <div style="width:auto;height:1200px;overflow:hidden;position:relative;"><iframe style="width:100%;height:100%;border:1px solid #e6e6e6;border-radius:20px;box-sizing:border-box" src="https://yandex.ru/maps-reviews-widget/1845586589?comments"></iframe><a href="https://yandex.ru/maps/org/vash_doktor/1845586589/" target="_blank" style="box-sizing:border-box;text-decoration:none;color:#b3b3b3;font-size:10px;font-family:YS Text,sans-serif;padding:0 20px;position:absolute;bottom:8px;width:100%;text-align:center;left:0;overflow:hidden;text-overflow:ellipsis;display:block;max-height:14px;white-space:nowrap;padding:0 16px;box-sizing:border-box">Ваш доктор на карте Абакана — Яндекс Карты</a></div>
</div>
</template>

<script>
export default {
    name: "Reviews",

    mounted() {
        let tg = window.Telegram.WebApp;
        let btn = Telegram.WebApp.BackButton;
        btn.show();
        btn.onClick(this.pageBack);
        tg.MainButton.hide();
    },
    methods: {
        pageBack(){
            window.history.back();
            Telegram.WebApp.BackButton.hide();
        }
    }
}
</script>

<style scoped>

</style>
