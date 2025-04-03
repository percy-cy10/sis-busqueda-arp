<template>
    <q-card class="my-card" style="width: 1400px; max-width: 80vw">
        <q-card-section class="bg-primary text-white row">
            <div class="text-h6 row items-center">{{ title }}</div>
            <q-space />
            <q-btn icon="close" color="negative" round  v-close-popup />
        </q-card-section>
        <q-card-section>
            <q-form @submit.prevent="submit">
                <q-toggle  v-model="verPDF" color="red" label="Ver PDF" />
                <div class="col-12 q-px-lg q-mx-lg q-py-md">
                    <q-option-group v-model="tipoCopia" color="primary" class="row justify-end" inline
                        :options="[ { label: `Copia Simple (${formatNumberToSoles(precios[0])})`, value: 0 },
                                { label: `Copia certificada (${formatNumberToSoles(precios[1])})`, value: 1 },
                                { label: `Testimonio (${formatNumberToSoles(precios[2])})`, value: 2 } ]" />
                     <div class="col-12 row justify-end">
                        <div class="col-7">
                            <div class="q-pa-sm text-weight-bold text-subtitle1 text-right">Costo de Verificación :</div>
                            <div class="q-pa-sm row items-center justify-end">
                              <span class="q-pr-md text-weight-bold text-subtitle1">Cantidad de Folios :</span>
                              <q-btn round color="primary" icon="horizontal_rule" size="xs" @click="cantiFolio>1?cantiFolio=cantiFolio-1:null" :disable="true"/> 
                              <span class="q-px-md text-weight-bold">{{ cantiFolio }}</span>
                              <q-btn round color="primary" icon="add" size="xs"  @click="cantiFolio<8?cantiFolio=cantiFolio+1:null" :disable="true"/>
                            </div>
                            <div class="q-pa-sm row items-center justify-end">
                              <span class="q-pr-md text-weight-bold text-subtitle1">Cantidad de copias :</span>
                              <q-btn round color="primary" icon="horizontal_rule" size="xs" @click="cantiCopia>1?cantiCopia=cantiCopia-1:null"/> 
                              <span class="q-px-md text-weight-bold">{{ cantiCopia }}</span>
                              <q-btn round color="primary" icon="add" size="xs"  @click="cantiCopia<8?cantiCopia=cantiCopia+1:null"/>
                            </div>
                            <div class="q-pa-sm text-weight-bold text-subtitle1 text-right">Total :</div>
                            <div class="row q-mr-sm items-center justify-end">
                                <q-input style="max-width: 150px;" label="Monto entregado" dense reverse-fill-mask input-class="text-right"
                                    v-model="montoEntrega" mask="#.##" prefix="s/" fill-mask="0" outlined/>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="q-pa-sm text-subtitle1">{{ formatNumberToSoles(form.pago_verificacion) }}</div>
                            <div class="q-pa-sm text-subtitle1">{{ formatNumberToSoles(form.pago_folio=Pagofolio) }}</div>
                            <div class="q-pa-sm text-subtitle1 lineinferior">{{ formatNumberToSoles(form.pago_fotocopia=Pagocopia) }}</div>
                            <div class="q-pa-sm text-subtitle1 text-green-13">{{ formatNumberToSoles(total) }}</div>
                            <div class="q-pa-sm text-subtitle1">{{ formatNumberToSoles(montoEntrega) }}</div>
                        </div>
                        <div v-if="montoEntrega && parseInt(montoEntrega)!==0" class="col-7 q-pa-sm text-weight-bold text-subtitle1 text-right">
                            Monto de Cambio (vuelto) :
                        </div>
                        <div v-if="montoEntrega && parseInt(montoEntrega)!==0" class="col-5 q-pa-sm text-yellow-9 text-subtitle1 linesuper">
                            {{ formatNumberToSoles(montoEntrega - total) }}
                        </div>
                     </div>
                </div>
                <div v-if="verPDF" class="col-12">
                    <GenerarPDFSolicitud :datosSolicitudRow="D_solicitud" :datosBusqueda="D_busqueda" :datosVerificacion="D_verificacion" :ver="true" height="450px"/>
                </div>
                <q-separator />
                <q-card-actions align="right">
                    <q-btn label="Cancelar" flat v-close-popup />
                    <q-btn label="Guardar" color="positive" :loading="form.processing" type="submit" />
                </q-card-actions>
            </q-form>
        </q-card-section>

    </q-card>
</template>

<script setup>
import { useForm } from 'laravel-precognition-vue';
import GenerarPDFSolicitud from 'src/components/GenerarPDFSolicitud.vue';
import TupaService from 'src/services/TupaService';
import { formatNumberToSoles, redondearConDecimales } from 'src/utils/ConvertMoney';
import { computed, onBeforeMount, ref } from 'vue';
const emits = defineEmits(["save"]);
const props = defineProps({
    title: { default: null},
    tipo_solicitud: { default: null},
    D_solicitud: {default: null,},
    D_busqueda: {default: null,},
    D_verificacion: {default: null,},
});
const verPDF = ref(false);
const montoEntrega = ref(0);
const tipoCopia = ref(0);
const cantiFolio = ref(1);
const cantiCopia = ref(1);
const costoVerificación = ref(10.0);
const cod_precios = ['0703','0702','0701'];
let precios = ref([]);  //costos de derecho de copia
const CostofotoCopia = ref(null);

const Pagofolio = computed(()=>{
    return redondearConDecimales(precios.value[tipoCopia.value])*cantiFolio.value;
});
const Pagocopia = computed(()=>{
    return cantiFolio.value * cantiCopia.value * redondearConDecimales(CostofotoCopia.value);
});
const total = computed(()=>{
    return costoVerificación.value + Pagofolio.value + Pagocopia.value;
});

let form = useForm("post", "api/pagos", {
    solicitud_id: props.D_solicitud.id,
    pago_busqueda: props.D_solicitud.pago_busqueda,
    pago_verificacion: costoVerificación.value,
    cantidad_folio: cantiFolio.value,
    pago_folio: Pagofolio.value,
    cantidad_fotocopia: cantiCopia.value,
    pago_fotocopia: Pagocopia.value,
    tipo_copia:null,
});
onBeforeMount(async()=>{
    let folioIni = props.D_busqueda.cod_folioInicial.match(/\d+/);
    let folioFin = props.D_busqueda.cod_folioFinal.match(/\d+/);
    cantiFolio.value = parseInt(folioFin[0], 10) - parseInt(folioIni[0], 10) + 1;
    form.cantidad_folio = cantiFolio.value;
    await getPrecios();
});
async function getPrecios(){
    for (const key of cod_precios) {
        precios.value.push(await TupaService.getData({params: {precio_sub_code:key}}));
    }
    CostofotoCopia.value = await TupaService.getData({params: {precio_sub_code: '0501'}});
}

function submit(){
    form.tipo_copia = cod_precios[tipoCopia.value];
    form.cantidad_folio = cantiFolio.value;
    form.cantidad_fotocopia = cantiCopia.value;
    form.submit()
        .then((respuesta)=>{
            // console.log(respuesta);
            emits("save",'caja');
        }).catch((error)=>{
            console.log(error);
        });
}

</script>

<style lang="sass" scoped>
.my-card
    width: 100%
    max-width: 80vw
.lineinferior
    border-bottom: 2px solid $grey
.linesuper
    border-top: 2px solid $grey
</style>