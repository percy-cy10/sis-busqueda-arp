<template>
  <q-card class="my-card">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">{{ title }}</div>
    </q-card-section>

    <q-form @submit.prevent="submit" class="q-gutter-md">
      <q-card-section class="q-pa-md">
        <!-- 1. Selección de Solicitud -->
        <q-select
          dense outlined
          v-model="form.solicitud_id"
          :options="filteredSolicitudes"
          option-value="id"
          option-label="label"
          label="Solicitud *"
          emit-value map-options
          class="q-mb-sm"
          placeholder=""
          clearable
          :disable="false"
          use-input
          input-debounce="0"
          @filter="filterSolicitudes"
        >
          <template v-slot:prepend>
            <q-icon name="assignment" />
          </template>
        </q-select>

        <!-- 2. Tipo de Documento -->
        <q-select
          dense outlined
          v-model="form.tipo_documento"
          :options="tipoDocumentoOptions"
          label="Tipo Documento *"
          class="q-mb-sm"
          :rules="[val => !!val || 'Campo requerido']"
          clearable
          :disable="!!form.solicitud_id"
          emit-value
          map-options
        >
          <template v-slot:prepend>
            <q-icon name="badge" />
          </template>
        </q-select>

        <!-- 3. Número de Documento -->
        <q-input
          dense outlined
          v-model="form.num_documento"
          label="Número Documento *"
          class="q-mb-sm"
          :rules="[
            val => !!val || 'Campo requerido',
            val => form.tipo_documento === 'RUC' ? (val && val.length === 11) || 'El RUC debe tener 11 dígitos' : true
          ]"
          :mask="form.tipo_documento === 'RUC' ? '###########' : '###########'"
          @blur="autocompletarSolicitante"
          :disable="!!form.solicitud_id"
        >
          <template v-slot:prepend>
            <q-icon name="confirmation_number" />
          </template>
        </q-input>

        <!-- 4. Nombre Completo -->
        <q-input
          dense outlined
          v-model="form.nombre_completo"
          label="Nombre Completo *"
          class="q-mb-sm"
          :rules="[val => !!val || 'Campo requerido']"
          :readonly="!!form.solicitud_id"
          :disable="!!form.solicitud_id"
        >
          <template v-slot:prepend>
            <q-icon name="person" />
          </template>
        </q-input>

        <!-- 5. Total (solo lectura) -->
        <q-input
          dense outlined
          v-model.number="form.total"
          label="Total *"
          type="number"
          class="q-mb-sm"
          :rules="[val => val > 0 || 'Debe ser mayor a 0']"
          readonly
        >
          <template v-slot:after>
            <div class="text-subtitle1 q-pa-sm">
              {{ formatNumberToSoles(redondearConDecimales(form.total)) }}
            </div>
          </template>
        </q-input>

        <!-- 6. Tupas (detalle de pagos) -->
        <div class="q-mb-sm">
          <label class="text-bold">Tupas</label>
          <div v-for="(tupaSel, idx) in form.tupas" :key="idx" class="row q-gutter-sm items-center q-mb-xs">
            <q-select
              dense outlined
              v-model="tupaSel.tupa_id"
              :options="tupas"
              option-value="id"
              option-label="label"
              label="Tupa"
              emit-value map-options
              style="min-width: 220px"
              :rules="[val => !!val || 'Seleccione un tupa']"
              @update:model-value="val => actualizarPrecioTupa(idx, val)"
              clearable
            >
              <template v-slot:prepend>
                <q-icon name="description" />
              </template>
            </q-select>

            <q-input
              dense outlined
              v-model.number="tupaSel.cantidad"
              type="number"
              label="Cantidad"
              style="width: 90px"
              min="1"
              :rules="[val => val > 0 || 'Debe ser mayor a 0']"
              @update:model-value="val => actualizarSubtotal(idx)"
            />

            <q-input
              dense outlined
              v-model.number="tupaSel.Subtotal"
              type="number"
              label="Subtotal"
              style="width: 120px"
              min="0"
              :readonly="true"
            />

            <q-btn icon="delete" color="red" flat round dense @click="() => quitarTupa(idx)" />
          </div>

          <q-btn icon="add" color="primary" flat dense @click="agregarTupa" label="Agregar Tupa" />
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right" class="q-pa-md">
        <q-btn label="Cancelar" flat v-close-popup class="q-mr-sm" />
        <q-btn label="Guardar" :loading="form.processing" type="submit" color="positive" />
      </q-card-actions>
    </q-form>
  </q-card>
</template>



<script setup>
import { ref,computed, onMounted, watch } from "vue";
import PagoService from "src/services/PagoService";
import SolicitudService from "src/services/SolicitudService";
import UsuarioService from "src/services/UsuarioService";
import TupaService from "src/services/TupaService";
import DniService from "src/services/DniService";
import {
  formatNumberToSoles,
  redondearConDecimales,
} from "src/utils/ConvertMoney";

const emits = defineEmits(["save", "close"]);
const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false },
});

const form = ref({
  solicitud_id: null,
  tipo_documento: "",
  num_documento: "",
  nombre_completo: "",
  total: 0,
  // user_id: null,
  // tupas: [],
  tupas: [
    // Selecciona por defecto el tupa con id 14
    { tupa_id: 14, cantidad: 1, Subtotal: 0, precio: 0, denominacion: '' }
  ],
  processing: false,
});


const solicitudesOriginal = ref([]);
const solicitudes = ref([]);
const filteredSolicitudes = ref([]);


// const solicitudes = ref([]);
const usuarios = ref([]);
const tupas = ref([]);
const tipoDocumentoOptions = [
  { label: "DNI", value: "DNI" },
  { label: "RUC", value: "RUC" },
];

function solicitudOptionLabel(solicitud) {
  if (!solicitud) return '';
  const nombre = solicitud.solicitante?.nombre_completo || '-';
  const tipoDoc = solicitud.solicitante?.tipo_documento || '';
  const numDoc = solicitud.solicitante?.num_documento || '';
  return `N° = ${solicitud.id} - ${nombre}`;
}

onMounted(async () => {

  // solicitudesOriginal.value = ((await SolicitudService.getData()).data || []).map(s => ({
  solicitudesOriginal.value = ((await SolicitudService.getData({ params: { rowsPerPage: -1 } })).data || []).map(s => ({
    ...s,
    id: Number(s.id),
    label: `N° = ${s.id} - Solicitante: ${s.solicitante?.nombre_completo || '-'}`,
  }));
  solicitudes.value = solicitudesOriginal.value;
  filteredSolicitudes.value = solicitudesOriginal.value;

  usuarios.value = ((await UsuarioService.getData()).data || []).map(u => ({
    ...u,
    id: Number(u.id),
    name: u.name,
    email: u.email,
  }));
  tupas.value = ((await TupaService.getData()).data || []).map(t => ({
    ...t,
    id: Number(t.id),
    // label: t.denominacion,
    label: `${t.denominacion} -  ${t.costo || '-'}`,
    costo: t.costo,
  }));

  // Pruebas de consola para depuración
  console.log('Solicitudes:', solicitudes.value);
  console.log('Usuarios:', usuarios.value);
  console.log('Tupas:', tupas.value);

  // Si NO es edición, setea los datos del tupa por defecto

  if (!props.edit) {
    const tupaDefault = tupas.value.find(t => t.id === 14);
    if (tupaDefault) {
      form.value.tupas = [{
        tupa_id: 14,
        cantidad: 1,
        Subtotal: Number(tupaDefault.costo),
        precio: Number(tupaDefault.costo),
        denominacion: tupaDefault.denominacion
      }];
      actualizarTotal();
    }
  }

  if (props.edit && props.id) {
    const pago = await PagoService.get(props.id);
    Object.assign(form.value, pago);
    form.value.solicitud_id = Number(pago.solicitud_id);
    form.value.user_id = Number(pago.user_id);
    form.value.tupas = pago.tupas?.map(t => ({
      tupa_id: Number(t.id),
      cantidad: t.pivot.cantidad,
      Subtotal: Number(t.pivot.Subtotal),
      precio: Number(t.costo),
      denominacion: t.denominacion
    })) || [];
    actualizarTotal();
    // Prueba de consola para ver el form cargado en edición
    console.log('Form cargado para edición:', form.value);
  }
});


watch(() => form.value.solicitud_id, (newVal) => {
  if (!newVal) return;
  const solicitud = solicitudes.value.find(s => s.id === newVal);
  if (solicitud) {
    form.value.tipo_documento = solicitud.solicitante?.tipo_documento || "";
    // Fuerza a string para evitar truncamiento de ceros
    form.value.num_documento = solicitud.solicitante?.num_documento ? String(solicitud.solicitante.num_documento) : "";
    // Si no hay nombre_completo y es RUC, intenta buscarlo por servicio
    if (!solicitud.solicitante?.nombre_completo && solicitud.solicitante?.tipo_documento === "RUC") {
      form.value.nombre_completo = "";
    } else {
      form.value.nombre_completo = solicitud.solicitante?.nombre_completo || "";
    }
  }
});


async function autocompletarSolicitante() {
  if (form.value.tipo_documento === "DNI" && form.value.num_documento.length === 8) {
    const res = await DniService.getSolicitanteDni(form.value.num_documento);
    if (res?.existe) {
      form.value.nombre_completo = `${res.nombres} ${res.apellido_paterno} ${res.apellido_materno}`;
      console.log('Autocompletado desde DNI:', form.value.nombre_completo);
    }
  }
  if (form.value.tipo_documento === "RUC" && form.value.num_documento.length === 11) {
    // Aquí deberías llamar a tu servicio de RUC, por ejemplo:
    // const res = await RucService.getSolicitanteRuc(form.value.num_documento);
    // if (res?.existe) {
    //   form.value.nombre_completo = res.razon_social;
    //   console.log('Autocompletado desde RUC:', form.value.nombre_completo);
    // }
  }
}

function filterSolicitudes(val, update) {
  if (!val) {
    update(() => {
      filteredSolicitudes.value = solicitudesOriginal.value;
    });
    return;
  }
  const needle = val.toLowerCase();
  update(() => {
    filteredSolicitudes.value = solicitudesOriginal.value.filter(s =>
      s.label.toLowerCase().includes(needle)
    );
  });
}

function agregarTupa() {
  form.value.tupas.push({ tupa_id: null, cantidad: 1, Subtotal: 0, precio: 0, denominacion: '' });
  actualizarTotal();
  console.log('Tupa agregado:', form.value.tupas);
}
function quitarTupa(idx) {
  form.value.tupas.splice(idx, 1);
  actualizarTotal();
  console.log('Tupa quitado. Tupas actuales:', form.value.tupas);
}
function actualizarPrecioTupa(idx, tupa_id) {
  const tupa = tupas.value.find(t => t.id === tupa_id);
  if (tupa) {
    form.value.tupas[idx].precio = Number(tupa.costo);
    form.value.tupas[idx].denominacion = tupa.denominacion;
    actualizarSubtotal(idx);
    console.log(`Precio actualizado para tupa[${idx}]:`, tupa);
  }
}
function actualizarSubtotal(idx) {
  const tupaSel = form.value.tupas[idx];
  tupaSel.Subtotal = Number(tupaSel.precio) * Number(tupaSel.cantidad);
  actualizarTotal();
  console.log(`Subtotal actualizado para tupa[${idx}]:`, tupaSel.Subtotal);
}
function actualizarTotal() {
  form.value.total = form.value.tupas.reduce((sum, t) => sum + Number(t.Subtotal), 0);
  console.log('Total actualizado:', form.value.total);
}


async function submit() {
  form.value.processing = true;
  try {
    // Forzar tipo_documento a string si es objeto
    if (typeof form.value.tipo_documento === 'object' && form.value.tipo_documento !== null) {
      form.value.tipo_documento = form.value.tipo_documento.value;
    }
    console.log('Formulario a enviar:', form.value);

    const payload = {
      ...form.value,
      estado: 1,
      boleta_id: null
    };


    console.log('Formulario a enviar:', payload);
    if (props.edit && props.id) {
      await PagoService.save({ ...payload, id: props.id });
    } else {
      await PagoService.save(payload);
    }
    emits("save");
  } catch (error) {
    // Mostrar errores de validación del backend
    if (error.response && error.response.data && error.response.data.errors) {
      const errores = Object.values(error.response.data.errors).flat().join('\n');
      // Si usas Quasar:
      if (typeof $q !== 'undefined' && $q.notify) {
        $q.notify({ type: 'negative', message: errores });
      } else {
        alert(errores);
      }
      console.error("Errores de validación:", error.response.data.errors);
    } else {
      // Otro error
      if (typeof $q !== 'undefined' && $q.notify) {
        $q.notify({ type: 'negative', message: 'Error al guardar el pago' });
      } else {
        alert('Error al guardar el pago');
      }
      console.error("Error en submit:", error);
    }
  } finally {
    form.value.processing = false;
  }
}

</script>

<style scoped>
.my-card {
  width: 100%;
  max-width: 80vw;
}
</style>
