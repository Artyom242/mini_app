<template>
    <div class="loading-overlay">
        <div class="spinner"></div>
    </div>
</template>

<script>
export default {
    name: 'Spinner'
};
</script>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

/* Спиннер */
.spinner {
    width: 50px;
    height: 50px;
    border: 6px solid rgba(255, 255, 255, 0.3);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite; /* Анимация вращения */
}

/* Анимация вращения спиннера */
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
