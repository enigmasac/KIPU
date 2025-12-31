<template>
    <div class="relative bg-body z-10 rounded-lg shadow-2xl p-5 sm:p-10 full-height-mobile overflow-hidden">
        <WizardSteps :active_state="active"></WizardSteps>

        <div class="flex flex-col justify-between -mt-5 sm:mt-0" style="height:523px;">
            <div v-if="pageLoad" class="absolute left-0 right-0 top-0 bottom-0 w-full h-full bg-white rounded-lg flex items-center justify-center z-50">
                <span class="material-icons form-spin animate-spin text-9xl">data_usage</span>
            </div>

            <div class="flex flex-col items-center justify-center text-center mt-10 w-full">
                <div class="w-full lg:w-2/3">
                    <h1 class="text-3xl font-bold text-black mb-4">
                        ¡Todo listo!
                    </h1>
                    <p class="text-gray-600 text-lg mb-8">
                        La configuración de tu empresa en Perú se ha completado con éxito. Ahora puedes empezar a gestionar tus facturas y contabilidad.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <base-button class="btn flex items-center justify-center text-lg bg-green hover:bg-green-700 text-white rounded-lg py-3 px-10 font-bold shadow-lg transition-all"
                         @click="finish()">
                            {{ translations.finish.create_first_invoice }}
                        </base-button>
                        
                        <a :href="route_url" class="text-purple hover:underline font-medium text-lg">
                            Ir al Panel de Control
                        </a>
                    </div>
                </div>

                <div class="mt-12 opacity-50">
                    <img :src="image_src" class="w-48" alt="Akaunting" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import WizardSteps from "./Steps.vue";

export default {
    name: "Finish",

    components: {
        WizardSteps
    },

    props: {
        modules: {
            type: [Object, Array],
        },

        translations: {
            type: [Object, Array],
        },

        pageLoad: {
          type: [Boolean, String]
        }
    },

    data() {
        return {
            active: 4,
            route_url: url,
            image_src: app_url + "/public/img/wizard-rocket.gif",
            anchor_loading: false
        };
    },

    created() {
        window.axios({
            method: "PATCH",
            url: url + "/wizard/finish",
        })
        .then((response) => {
        })
        .catch((error) => {
            this.$notify({
                verticalAlign: 'bottom',
                horizontalAlign: 'left',
                message: this.translations.finish.error_message,
                timeout: 1000,
                icon: "",
                type: 0
            });

            this.prev();
        });
    },

    methods: {
        prev() {
            if (this.active-- > 2);

            this.$router.push("/wizard/currencies");
        },

        finish() {
            window.location.href = url + "/sales/invoices/create";
            this.anchor_loading = true;
        },
    },
};
</script>

<style scoped>
    .sliding-app:hover {
        animation: slidingAnimation 600ms ease-out forwards;
    }   

    @keyframes slidingAnimation {
        0% { transform: translateX(0); }
        40% { transform: translateX(36px); }
        60% { transform: translateX(24px); }
        80% { transform: translateX(30px); }
        100% { transform: translateX(24px); }
    }

    @media only screen and (max-width: 991px) {
        [modal-container] {
            height: 100% !important;
        }

        .scroll{
            height:450px;
        }
    }
</style>
