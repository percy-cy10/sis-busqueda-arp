<template>
  <q-dialog v-model="mostrarDialogo" persistent>
    <q-card class="q-pa-md" style="min-width: 380px; max-width: 95vw;">
      <q-card-section>
        <div class="text-h6 text-primary">Detalles del Pago</div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div v-for="item in items" :key="item.id" class="row items-center q-mb-sm">
          <div class="col-8">
            <strong>{{ item.description }}</strong>, ({{ item.cantidad }})
          </div>
          <!-- <div class="col-4 text-right text-caption text-grey">
            S/ {{ Number(item.subtotal).toFixed(2) }}
          </div> -->
          <div class="col-4 text-right text-caption text-grey">
            S/ {{ formatNumberToSoles(redondearConDecimales(item.subtotal)) }}
          </div>
        </div>
        <q-separator spaced />
        <div class="row q-mt-md">
          <div class="col-7 text-right text-weight-bold text-subtitle1">
            Total a pagar:
          </div>
          <!-- <div class="col-5 text-right text-primary text-subtitle1">
            S/ {{ Number(total).toFixed(2) }}
          </div> -->
          <div class="col-5 text-right text-primary text-subtitle1">
            S/ {{ formatNumberToSoles(redondearConDecimales(total)) }}
          </div>
        </div>
      </q-card-section>

      <q-card-section>
        <div class="row items-center">
          <div class="col-7 text-right text-weight-bold text-subtitle1">
            Monto entregado:
          </div>
          <div class="col-5">
            <q-input
              v-model.number="montoEntregado"
              type="number"
              dense
              outlined
              min="0"
              class="q-ml-sm"
              placeholder="0.00"
              color="primary"
              input-class="text-right"
              @keyup.enter="confirmarPago"
              clearable
            >
              <!-- <template v-slot:after v-if="montoEntregado">
                <div class="text-subtitle1 q-pa-sm">
                  {{ formatNumberToSoles(redondearConDecimales(montoEntregado)) }}
                </div>
              </template> -->
            </q-input>
          </div>
        </div>
        <div v-if="montoEntregado && parseFloat(montoEntregado) !== 0" class="row q-mt-sm">
          <div class="col-7 text-right text-weight-bold text-subtitle1">
            {{ montoEntregado < total ? 'Faltante:' : 'Vuelto:' }}
          </div>
          <div
            class="col-5 text-right text-subtitle1"
            :class="montoEntregado < total ? 'text-negative' : 'text-positive'"
          >
            <!-- {{ formatNumberToSoles(montoEntregado - total) }} -->
            {{ formatNumberToSoles(redondearConDecimales(montoEntregado - total)) }}
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn label="Cerrar" @click="cerrarModal" color="secondary" flat />
        <q-btn
          label="Confirmar Pago"
          @click="confirmarPago"
          color="primary"
          :disable="!puedeConfirmar"
          unelevated
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import {
  formatNumberToSoles,
  redondearConDecimales,
} from "src/utils/ConvertMoney";

const props = defineProps({
  items: Array,
  total: Number,
  showModal: Boolean
})

const emit = defineEmits(['close', 'confirm', 'update:showModal'])

const montoEntregado = ref(0)

const mostrarDialogo = computed({
  get: () => props.showModal,
  set: val => emit('update:showModal', val)
})

const puedeConfirmar = computed(() => montoEntregado.value >= props.total && montoEntregado.value > 0)

const cerrarModal = () => {
  emit('close')
  emit('update:showModal', false)
}

const confirmarPago = () => {
  emit('confirm', { amount: montoEntregado.value, change: montoEntregado.value - props.total })
  emit('update:showModal', false)
}

watch(() => props.showModal, (nuevoValor) => {
  if (nuevoValor) {
    montoEntregado.value = 0
  }
})

// Formatear a soles
// function formatNumberToSoles(valor) {
//   const n = Number(valor)
//   return (n < 0 ? '-S/ ' : 'S/ ') + Math.abs(n).toFixed(2)
// }
</script>

<style scoped>
.text-primary {
  color: #1976d2;
}
.text-positive {
  color: #21ba45;
}
.text-negative {
  color: #c10015;
}
.q-card {
  border-radius: 12px;
}
</style>
