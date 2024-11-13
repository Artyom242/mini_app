<template>
    <div class="loading-overlay">
        <div class="dots-3"></div>
    </div>
</template>

<script>
export default {
    name: "Spinner",
};
</script>

<style scoped>

.dots-3 {
    width: 60px;
    aspect-ratio: 2;
    --_g: no-repeat radial-gradient(circle closest-side, #ffffff 90%,#0000);
    background:
        var(--_g) 0%   50%,
        var(--_g) 50%  50%,
        var(--_g) 100% 50%;
    background-size: calc(100%/3) 50%;
    animation: d3 1s infinite linear;
}
@keyframes d3 {
    20%{background-position:0%   0%, 50%  50%,100%  50%}
    40%{background-position:0% 100%, 50%   0%,100%  50%}
    60%{background-position:0%  50%, 50% 100%,100%   0%}
    80%{background-position:0%  50%, 50%  50%,100% 100%}
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    //background: rgba(0, 0, 0, 0.25);
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
    animation: spin 1s linear infinite;
}

/* Анимация вращения спиннера */
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
