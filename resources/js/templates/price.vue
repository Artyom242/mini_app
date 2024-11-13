<template>
    <p>Price</p>
</template>

<script>
export default {
    name: "PricesPage",

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


