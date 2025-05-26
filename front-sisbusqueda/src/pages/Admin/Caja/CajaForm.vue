<template>
  <q-card class="my-card" style="width: 1400px; max-width: 80vw">
    <q-card-section class="bg-primary text-white row">
      <div class="text-h6 row items-center">{{ title }}</div>
      <q-space />
      <q-btn icon="close" color="negative" round v-close-popup />
    </q-card-section>

    <q-card-section>
      <q-form @submit.prevent="submit">
        <!-- Toggle PDF -->
        <q-toggle v-model="verPDF" color="red" label="Ver PDF" />

        <!-- Tipo de Copia -->
        <q-option-group
          v-model="tipoCopia"
          color="primary"
          inline
          class="q-my-md"
          :options="opcionesCopias"
        />

        <!-- Detalles de montos -->
        <div class="row q-col-gutter-md">
          <div class="col-7">
            <q-item>
              <q-item-section class="text-right">Costo de Verificación:</q-item-section>
              <q-item-section>{{ formatNumberToSoles(costoVerificación) }}</q-item-section>
            </q-item>

            <!-- Cantidad de folios -->
            <q-item>
              <q-item-section class="text-right">Cantidad de Folios:</q-item-section>
              <q-item-section>
                <q-btn dense round icon="remove" @click="decrementFolio" :disable="cantiFolio <= 1" />
                <span class="q-px-sm">{{ cantiFolio }}</span>
                <q-btn dense round icon="add" @click="incrementFolio" :disable="cantiFolio >= 10" />
              </q-item-section>
            </q-item>

            <!-- Cantidad de copias -->
            <q-item>
              <q-item-section class="text-right">Cantidad de Copias:</q-item-section>
              <q-item-section>
                <q-btn dense round icon="remove" @click="decrementCopia" :disable="cantiCopia <= 1" />
                <span class="q-px-sm">{{ cantiCopia }}</span>
                <q-btn dense round icon="add" @click="incrementCopia" :disable="cantiCopia >= 10" />
              </q-item-section>
            </q-item>

            <!-- Monto entregado -->
            <q-item>
              <q-item-section class="text-right">Monto Entregado:</q-item-section>
              <q-item-section>
                <q-input
                  v-model="montoEntrega"
                  dense
                  outlined
                  mask="#.##"
                  prefix="S/"
                  input-class="text-right"
                  style="max-width: 150px;"
                />
              </q-item-section>
            </q-item>
          </div>

          <!-- Columna Totales -->
          <div class="col-5">
            <q-item-label>Total Folios: {{ formatNumberToSoles(Pagofolio) }}</q-item-label>
            <q-item-label>Total Copias: {{ formatNumberToSoles(Pagocopia) }}</q-item-label>
            <q-item-label>Total: <span class="text-green-13">{{ formatNumberToSoles(total) }}</span></q-item-label>
            <q-item-label v-if="montoEntrega">Vuelto: {{ formatNumberToSoles(vuelto) }}</q-item-label>
          </div>
        </div>

        <!-- PDF Viewer -->
        <div v-if="verPDF" class="q-my-md">
          <GenerarPDFSolicitud :datosSolicitudRow="D_solicitud" :datosBusqueda="D_busqueda" :datosVerificacion="D_verificacion" :ver="true" height="450px" />
        </div>

        <q-separator class="q-my-md" />

        <!-- Botones -->
        <q-card-actions align="right">
          <q-btn label="Cancelar" flat v-close-popup />
          <q-btn label="Guardar" color="positive" :loading="form.processing" type="submit" />
        </q-card-actions>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, onBeforeMount } from 'vue'
import { useForm } from 'laravel-precognition-vue'
import GenerarPDFSolicitud from 'src/components/GenerarPDFSolicitud.vue'
import TupaService from 'src/services/TupaService'
import { formatNumberToSoles, redondearConDecimales } from 'src/utils/ConvertMoney'

const props = defineProps({
  title: String,
  tipo_solicitud: String,
  D_solicitud: Object,
  D_busqueda: Object,
  D_verificacion: Object
})

const emits = defineEmits(['save'])

const verPDF = ref(false)
const tipoCopia = ref(0)
const cantiFolio = ref(1)
const cantiCopia = ref(1)
const montoEntrega = ref(0)

const costoVerificación = ref(10)
const cod_precios = ['0703', '0702', '0701']
const precios = ref([])
const CostofotoCopia = ref(0)

// Cálculos
const Pagofolio = computed(() => {
  return redondearConDecimales(precios.value[tipoCopia.value]) * cantiFolio.value
})

const Pagocopia = computed(() => {
  return cantiFolio.value * cantiCopia.value * redondearConDecimales(CostofotoCopia.value)
})

const total = computed(() => {
  return costoVerificación.value + Pagofolio.value + Pagocopia.value
})

const vuelto = computed(() => {
  return montoEntrega.value - total.value
})

const opcionesCopias = computed(() => [
  { label: `Copia Simple (${formatNumberToSoles(precios.value[0])})`, value: 0 },
  { label: `Copia Certificada (${formatNumberToSoles(precios.value[1])})`, value: 1 },
  { label: `Testimonio (${formatNumberToSoles(precios.value[2])})`, value: 2 }
])

// Formulario
const form = useForm('post', 'api/pagos', {
  solicitud_id: props.D_solicitud.id,
  pago_busqueda: props.D_solicitud.pago_busqueda,
  pago_verificacion: costoVerificación.value,
  cantidad_folio: cantiFolio.value,
  pago_folio: Pagofolio.value,
  cantidad_fotocopia: cantiCopia.value,
  pago_fotocopia: Pagocopia.value,
  tipo_copia: null
})

onBeforeMount(async () => {
  const folioIni = props.D_busqueda.cod_folioInicial.match(/\d+/)
  const folioFin = props.D_busqueda.cod_folioFinal.match(/\d+/)
  cantiFolio.value = parseInt(folioFin[0]) - parseInt(folioIni[0]) + 1
  form.cantidad_folio = cantiFolio.value

  await getPrecios()
})

async function getPrecios () {
  for (const key of cod_precios) {
    precios.value.push(await TupaService.getData({ params: { precio_sub_code: key } }))
  }
  CostofotoCopia.value = await TupaService.getData({ params: { precio_sub_code: '0501' } })
}

function submit () {
  form.tipo_copia = cod_precios[tipoCopia.value]
  form.cantidad_folio = cantiFolio.value
  form.cantidad_fotocopia = cantiCopia.value
  form.submit()
    .then(() => {
      emits('save', 'caja')
    })
    .catch(err => {
      console.error(err)
    })
}

// Métodos para botones
function incrementFolio () {
  if (cantiFolio.value < 10) cantiFolio.value++
}

function decrementFolio () {
  if (cantiFolio.value > 1) cantiFolio.value--
}

function incrementCopia () {
  if (cantiCopia.value < 10) cantiCopia.value++
}

function decrementCopia () {
  if (cantiCopia.value > 1) cantiCopia.value--
}
</script>

<style scoped lang="scss">
.my-card {
  width: 100%;
  max-width: 80vw;
}
</style>
