<template>
  <q-card class="my-card">
    <!-- ===========================
         HEADER DEL FORMULARIO
    ============================ -->
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">{{ title }}</div>
    </q-card-section>

    <q-form @submit.prevent="submit" class="q-gutter-md">
      <q-card-section class="q-pa-md">

        <!-- ===========================
             PRIMERA FILA: SOLICITUD
        ============================ -->
        <div class="row q-mb-md">
          <div class="col-12">
            <q-select
              dense
              outlined
              v-model="form.solicitud_id"
              :options="filteredSolicitudes"
              option-value="id"
              option-label="label"
              label="Buscar Solicitud"
              emit-value
              map-options
              clearable
              use-input
              input-debounce="300"
              @filter="filterSolicitudes"
              @update:model-value="onSolicitudSeleccionada"
              :loading="loadingSolicitudes"
              hide-dropdown-icon
              @clear="limpiarCamposSolicitante"
              :readonly="campoSolicitudBloqueado"
              :disable="campoSolicitudBloqueado"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>

              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    No se encontraron solicitudes
                  </q-item-section>
                </q-item>
              </template>

              <template v-slot:option="scope">
                <q-item v-bind="scope.itemProps">
                  <q-item-section>
                    <q-item-label class="text-weight-medium">{{ scope.opt.label }}</q-item-label>
                    <q-item-label caption class="text-capitalize">
                      {{ scope.opt.solicitante?.nombre_completo || 'Sin nombre' }}
                    </q-item-label>
                    <q-item-label caption>
                      {{ scope.opt.solicitante?.tipo_documento || 'DOC' }}:
                      {{ scope.opt.solicitante?.num_documento || 'Sin documento' }}
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-badge color="primary">
                      {{ scope.opt.estado || 'Pendiente' }}
                    </q-badge>
                  </q-item-section>
                </q-item>
              </template>

              <template v-slot:selected>
                <template v-if="form.solicitud_id">
                  <div class="text-weight-medium">
                    {{ obtenerLabelSolicitudSeleccionada() }}
                  </div>
                  <div class="text-caption text-grey">
                    {{ form.tipo_documento }}: {{ form.num_documento }} -
                    {{ form.nombre_completo }}
                  </div>
                </template>
                <template v-else></template>
              </template>

              <template v-slot:after>
                <q-icon
                  v-if="campoSolicitudBloqueado"
                  name="lock"
                  color="orange"
                  class="q-ml-xs"
                >
                  <q-tooltip>Campo bloqueado - Complete los datos manualmente</q-tooltip>
                </q-icon>
              </template>
            </q-select>

            <!-- Mensaje informativo -->
            <q-card v-if="campoSolicitudBloqueado" class="bg-orange-1 q-mt-sm">
              <q-card-section class="q-pa-sm">
                <div class="row items-center">
                  <q-icon name="info" color="orange" class="q-mr-sm" />
                  <div class="text-caption text-weight-medium">
                    Complete los datos manualmente. El campo de solicitud está bloqueado.
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- ===========================
             SEGUNDA FILA: 2 COLUMNAS
        ============================ -->
        <div class="row q-col-gutter-md">

          <!-- COLUMNA IZQUIERDA: DATOS PERSONALES -->
          <div class="col-12 col-md-6">

            <!-- Información de Solicitud Seleccionada -->
            <q-card v-if="form.solicitud_id" class="bg-blue-1 q-mb-md">
              <q-card-section class="q-pa-sm">
                <div class="row items-center">
                  <q-icon name="info" color="primary" class="q-mr-sm" />
                  <div class="text-caption text-weight-medium">
                    Datos cargados desde la solicitud seleccionada
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <!-- Tipo de Documento -->
            <q-select
              dense
              outlined
              v-model="form.tipo_documento"
              :options="tipoDocumentoOptions"
              label="Tipo Documento *"
              class="q-mb-sm"
              :rules="[val => !form.solicitud_id ? !!val || 'Campo requerido' : true]"
              clearable
              :disable="!!form.solicitud_id"
              emit-value
              map-options
              @update:model-value="onTipoDocumentoChange"
            >
              <template v-slot:prepend>
                <q-icon name="badge" />
              </template>
              <template v-slot:after>
                <q-icon
                  v-if="form.solicitud_id"
                  name="lock"
                  color="green"
                  class="q-ml-xs"
                >
                  <q-tooltip>Campo autocompletado desde la solicitud</q-tooltip>
                </q-icon>
              </template>
            </q-select>

            <!-- Número de Documento -->
            <q-input
              dense
              outlined
              v-model="form.num_documento"
              label="Número Documento *"
              class="q-mb-sm"
              :rules="[
                val => !form.solicitud_id ? !!val || 'Campo requerido' : true,
                (val) => validarDocumento(val)
              ]"
              :mask="obtenerMascaraDocumento()"
              @blur="autocompletarSolicitante"
              :disable="!!form.solicitud_id"
              @update:model-value="onNumDocumentoChange"
            >
              <template v-slot:prepend>
                <q-icon name="confirmation_number" />
              </template>

              <template v-slot:after>
                <q-icon
                  v-if="form.solicitud_id"
                  name="lock"
                  color="green"
                  class="q-ml-xs"
                >
                  <q-tooltip>Campo autocompletado desde la solicitud</q-tooltip>
                </q-icon>
                <q-btn
                  v-else-if="form.tipo_documento === 'DNI' && form.num_documento?.length === 8"
                  icon="search"
                  flat
                  dense
                  @click="autocompletarSolicitante"
                  :loading="buscandoDni"
                >
                  <q-tooltip>Buscar en RENIEC</q-tooltip>
                </q-btn>
              </template>
            </q-input>

            <!-- Nombre Completo -->
            <q-input
              dense
              outlined
              v-model="form.nombre_completo"
              label="Nombre Completo *"
              class="q-mb-sm"
              :rules="[val => !form.solicitud_id ? !!val || 'Campo requerido' : true]"
              :readonly="!!form.solicitud_id"
              :disable="!!form.solicitud_id"
            >
              <template v-slot:prepend>
                <q-icon name="person" />
              </template>
              <template v-slot:after>
                <q-icon
                  v-if="form.solicitud_id"
                  name="lock"
                  color="green"
                  class="q-ml-xs"
                >
                  <q-tooltip>Campo autocompletado desde la solicitud</q-tooltip>
                </q-icon>
              </template>
            </q-input>

          </div>

          <!-- COLUMNA DERECHA: INFORMACIÓN DE PAGO -->
          <div class="col-12 col-md-6">

            <!-- Total -->
            <q-input
              dense
              outlined
              v-model.number="form.total"
              label="Total a Pagar *"
              type="number"
              class="q-mb-md"
              :rules="[(val) => val > 0 || 'Debe ser mayor a 0']"
              readonly
            >
              <template v-slot:prepend>
                <q-icon name="payments" />
              </template>
              <template v-slot:after>
                <div class="text-subtitle1 q-pa-sm text-green text-weight-bold">
                  {{ formatNumberToSoles(redondearConDecimales(form.total)) }}
                </div>
              </template>
            </q-input>

            <!-- Selección Rápida de Tupas Importantes -->
            <div class="q-mb-md">
              <div class="text-caption text-weight-medium q-mb-sm">Tupas Importantes:</div>
              <div class="row q-col-gutter-sm">
                <div class="col-4" v-for="tupa in tupasImportantes" :key="tupa.id">
                  <q-btn
                    :icon="tupa.icon"
                    :color="tupa.color"
                    flat
                    dense
                    class="full-width tupa-importante-btn"
                    @click="agregarTupaImportante(tupa.id)"
                    :label="tupa.nombreCorto"
                    :disable="!tupa.disponible"
                  >
                    <q-tooltip>
                      <div class="text-center">
                        <div class="text-weight-bold">{{ tupa.simbolo }} Tupa {{ tupa.id }}</div>
                        <div>{{ tupa.denominacion }}</div>
                        <div class="text-green">{{ formatNumberToSoles(tupa.costo) }}</div>
                      </div>
                    </q-tooltip>
                  </q-btn>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- ===========================
             TERCERA FILA: TUPAS (ANCHO COMPLETO)
        ============================ -->
        <div class="row">
          <div class="col-12">
            <div class="q-mb-sm">
              <div class="row items-center justify-between q-mb-md">
                <label class="text-bold text-h6">Servicios/Tupas *</label>
                <q-badge color="primary" class="q-px-sm q-py-xs text-subtitle2">
                  Total: {{ formatNumberToSoles(redondearConDecimales(form.total)) }}
                </q-badge>
              </div>

              <!-- Mensaje cuando no hay tupas -->
              <q-card v-if="form.tupas.length === 0" class="bg-grey-2 q-mb-md">
                <q-card-section class="text-center q-pa-md">
                  <q-icon name="info" color="grey" size="24px" class="q-mb-sm" />
                  <div class="text-caption text-grey">
                    No hay tupas agregados. Use los botones de arriba o agregue uno manualmente.
                  </div>
                </q-card-section>
              </q-card>

              <div
                v-for="(tupaSel, idx) in form.tupas"
                :key="idx"
                class="row q-gutter-sm items-center q-mb-sm q-pa-sm rounded-borders"
                :class="idx % 2 === 0 ? 'bg-grey-2' : ''"
              >
                <!-- Selección de Tupa -->
                <q-select
                  dense
                  outlined
                  v-model="tupaSel.tupa_id"
                  :options="tupasFiltradas"
                  option-value="id"
                  option-label="denominacion"
                  label="Seleccionar Tupa *"
                  emit-value
                  map-options
                  style="min-width: 250px; flex: 2"
                  :rules="[(val) => !!val || 'Seleccione un tupa']"
                  @update:model-value="(val) => actualizarPrecioTupa(idx, val)"
                  clearable
                  use-input
                  @filter="filterTupas"
                  :disable="tupaSeleccionado(tupaSel.tupa_id, idx)"
                >
                  <template v-slot:prepend>
                    <q-icon name="description" />
                  </template>

                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>{{ scope.opt.denominacion }}</q-item-label>
                        <q-item-label caption>
                          {{ formatNumberToSoles(scope.opt.costo) }}
                        </q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-badge color="green">
                          {{ formatNumberToSoles(scope.opt.costo) }}
                        </q-badge>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>

                <!-- Cantidad -->
                <q-input
                  dense
                  outlined
                  v-model.number="tupaSel.cantidad"
                  type="number"
                  label="Cantidad *"
                  style="width: 120px; flex: 1"
                  min="1"
                  :rules="[(val) => val > 0 || 'Debe ser mayor a 0']"
                  @update:model-value="(val) => actualizarSubtotal(idx)"
                />

                <!-- Subtotal -->
                <q-input
                  dense
                  outlined
                  v-model.number="tupaSel.Subtotal"
                  type="number"
                  label="Subtotal"
                  style="width: 150px; flex: 1"
                  min="0"
                  readonly
                >
                  <template v-slot:after>
                    <div class="text-caption text-green text-weight-medium">
                      {{ formatNumberToSoles(redondearConDecimales(tupaSel.Subtotal)) }}
                    </div>
                  </template>
                </q-input>

                <!-- Botón Eliminar -->
                <q-btn
                  icon="delete"
                  color="red"
                  flat
                  round
                  dense
                  @click="() => quitarTupa(idx)"
                >
                  <q-tooltip>Eliminar tupa</q-tooltip>
                </q-btn>
              </div>

              <!-- Botón Agregar Tupa -->
              <div class="row justify-between items-center q-mt-md">
                <q-btn
                  icon="add"
                  color="primary"
                  flat
                  dense
                  @click="agregarTupa"
                  label="Agregar Tupa Manualmente"
                />

                <q-btn
                  icon="refresh"
                  color="orange"
                  flat
                  dense
                  @click="limpiarTupas"
                  label="Limpiar Todos"
                  v-if="form.tupas.length > 0"
                />
              </div>
            </div>
          </div>
        </div>

      </q-card-section>

      <q-separator />

      <!-- ===========================
           ACCIONES DEL FORMULARIO
      ============================ -->
      <q-card-actions align="right" class="q-pa-md">
        <q-btn
          label="Cancelar"
          flat
          v-close-popup
          class="q-mr-sm"
          :disable="form.processing"
        />
        <q-btn
          label="Guardar Pago"
          :loading="form.processing"
          type="submit"
          color="positive"
          :disable="!formValido"
          icon="save"
        />
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import PagoService from "src/services/PagoService";
import SolicitudService from "src/services/SolicitudService";
import UsuarioService from "src/services/UsuarioService";
import TupaService from "src/services/TupaService";
import DniService from "src/services/DniService";
import { formatNumberToSoles, redondearConDecimales } from "src/utils/ConvertMoney";

const emits = defineEmits(["save", "close"]);
const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false },
});

/* ===========================
   FORMULARIO
=========================== */
const form = ref({
  solicitud_id: null,
  tipo_documento: "",
  num_documento: "",
  nombre_completo: "",
  total: 0,
  tupas: [], // Inicialmente vacío - no se agrega tupa vacío al inicio
  processing: false,
});

/* ===========================
   VARIABLES
=========================== */
const solicitudesOriginal = ref([]);
const filteredSolicitudes = ref([]);
const tupasOriginal = ref([]);
const tupasFiltradas = ref([]);
const loadingSolicitudes = ref(false);
const buscandoDni = ref(false);
const campoSolicitudBloqueado = ref(false);

const tipoDocumentoOptions = [
  { label: "DNI", value: "DNI" },
  { label: "RUC", value: "RUC" },
];

/* ===========================
   COMPUTED
=========================== */
const formValido = computed(() => {
  // Validar tupas y total (siempre requeridos)
  const tupasValidos = form.value.tupas.length > 0 &&
                       form.value.tupas.every(t => t.tupa_id && t.cantidad > 0);
  const totalValido = form.value.total > 0;

  // Si hay solicitud, los campos personales no son requeridos (se autocompletan)
  if (form.value.solicitud_id) {
    return totalValido && tupasValidos;
  } else {
    // Si no hay solicitud, validar los campos personales
    const documentosValidos = form.value.tipo_documento &&
                             form.value.num_documento &&
                             form.value.nombre_completo;
    return documentosValidos && totalValido && tupasValidos;
  }
});

const tupasImportantes = computed(() => {
  const importantes = [
    {
      id: 14,
      nombreCorto: "TUpa 14",
      icon: "star",
      color: "primary",
      simbolo: "★"
    },
    {
      id: 5,
      nombreCorto: "Tupa 5",
      icon: "star",
      color: "secondary",
      simbolo: "★"
    },
    {
      id: 7,
      nombreCorto: "Tupa 7",
      icon: "star",
      color: "accent",
      simbolo: "★"
    }
  ];

  return importantes.map(t => {
    const tupaEncontrado = tupasOriginal.value.find(tupa => tupa.id === t.id);
    return {
      ...t,
      disponible: !!tupaEncontrado,
      denominacion: tupaEncontrado?.denominacion || "No disponible",
      costo: tupaEncontrado?.costo || 0
    };
  });
});

/* ===========================
   WATCHERS
=========================== */
watch(
  () => form.value.solicitud_id,
  (newVal) => {
    if (!newVal) {
      limpiarCamposSolicitante();
      return;
    }

    const solicitud = solicitudesOriginal.value.find(s => s.id === newVal);
    if (solicitud) {
      autocompletarDesdeSolicitud(solicitud);
    }
  }
);

// Watch para detectar cuando el usuario empieza a escribir en los campos manualmente
watch(
  () => [form.value.tipo_documento, form.value.num_documento, form.value.nombre_completo],
  (newValues) => {
    const [tipoDoc, numDoc, nombre] = newValues;

    // Si el usuario comienza a llenar datos manualmente y no hay solicitud seleccionada,
    // bloquear el campo de solicitud
    if (!form.value.solicitud_id && (tipoDoc || numDoc || nombre)) {
      campoSolicitudBloqueado.value = true;
    }
  },
  { deep: true }
);

/* ===========================
   CARGA INICIAL
=========================== */
onMounted(async () => {
  await cargarSolicitudes();
  await cargarTupas();

  if (props.edit && props.id) {
    await cargarPagoEdicion();
  }
  // En modo creación NO se agrega ningún tupa vacío inicialmente
});

async function cargarSolicitudes() {
  loadingSolicitudes.value = true;
  try {
    const data = (await SolicitudService.getData({ params: { rowsPerPage: -1 } })).data || [];
    solicitudesOriginal.value = data.map((s) => {
      const hasCode = !!s.solicitud_code;
      return {
        ...s,
        id: Number(s.id),
        solicitud_code: s.solicitud_code,
        label: hasCode
          ? `COD: ${s.solicitud_code} - ${s.solicitante?.nombre_completo || '-'}`
          : `ID: ${s.id} - ${s.solicitante?.nombre_completo || '-'}`,
        displayText: hasCode
          ? `COD: ${s.solicitud_code} - ${s.solicitante?.nombre_completo || "-"}`
          : `ID: ${s.id} - ${s.solicitante?.nombre_completo || "-"}`,
      };
    }).sort((a, b) => {
      if (!a.solicitud_code && b.solicitud_code) return 1;
      if (a.solicitud_code && !b.solicitud_code) return -1;
      if (!a.solicitud_code && !b.solicitud_code) return b.id - a.id;
      const [numA, yearA] = a.solicitud_code.split("-");
      const [numB, yearB] = b.solicitud_code.split("-");
      if (yearA !== yearB) return Number(yearB) - Number(yearA);
      return Number(numB) - Number(numA);
    });

    filteredSolicitudes.value = solicitudesOriginal.value.slice(0, 50);
  } catch (error) {
    console.error("Error cargando solicitudes:", error);
  } finally {
    loadingSolicitudes.value = false;
  }
}

async function cargarTupas() {
  try {
    tupasOriginal.value = (await TupaService.getData()).data?.map(t => ({
      ...t,
      id: Number(t.id),
      label: `${t.denominacion} - ${formatNumberToSoles(t.costo)}`,
      costo: t.costo,
    })) || [];

    tupasFiltradas.value = tupasOriginal.value;
  } catch (error) {
    console.error("Error cargando tupas:", error);
  }
}

async function cargarPagoEdicion() {
  try {
    const pago = await PagoService.get(props.id);

    // Cargar los datos del pago
    form.value.solicitud_id = Number(pago.solicitud_id);
    form.value.tipo_documento = pago.tipo_documento || "";
    form.value.num_documento = pago.num_documento || "";
    form.value.nombre_completo = pago.nombre_completo || "";

    // Cargar tupas
    if (pago.tupas?.length > 0) {
      form.value.tupas = pago.tupas.map(t => ({
        tupa_id: Number(t.id),
        cantidad: t.pivot.cantidad,
        Subtotal: Number(t.pivot.Subtotal),
        precio: Number(t.costo),
        denominacion: t.denominacion,
      }));
    }
    // En edición, si no hay tupas, se deja el array vacío

    actualizarTotal();
  } catch (error) {
    console.error("Error cargando pago para edición:", error);
  }
}

/* ===========================
   MÉTODOS DE SOLICITUDES
=========================== */
function filterSolicitudes(val, update) {
  if (!val) {
    update(() => {
      filteredSolicitudes.value = solicitudesOriginal.value.slice(0, 50);
    });
    return;
  }

  const needle = val.toLowerCase();
  update(() => {
    filteredSolicitudes.value = solicitudesOriginal.value.filter(s =>
      (s.solicitud_code || "").toLowerCase().includes(needle) ||
      (s.displayText || "").toLowerCase().includes(needle) ||
      (s.solicitante?.nombre_completo || "").toLowerCase().includes(needle) ||
      (s.solicitante?.num_documento || "").includes(needle) ||
      (s.id.toString().includes(needle))
    ).slice(0, 50);
  });
}

function onSolicitudSeleccionada(solicitudId) {
  console.log("Solicitud seleccionada ID:", solicitudId);
  // Cuando se selecciona una solicitud, desbloquear el campo
  campoSolicitudBloqueado.value = false;
}

function autocompletarDesdeSolicitud(solicitud) {
  if (!solicitud) return;

  form.value.tipo_documento = solicitud.solicitante?.tipo_documento || "";
  form.value.num_documento = solicitud.solicitante?.num_documento || "";
  form.value.nombre_completo = solicitud.solicitante?.nombre_completo || "";

  // Si la solicitud tiene tupas asociados, sugerirlos
  if (solicitud.tupas && solicitud.tupas.length > 0) {
    form.value.tupas = solicitud.tupas.map(t => ({
      tupa_id: Number(t.id),
      cantidad: 1,
      Subtotal: Number(t.costo),
      precio: Number(t.costo),
      denominacion: t.denominacion,
    }));
    actualizarTotal();
  }

  console.log("Datos autocompletados desde solicitud:", solicitud);
}

function obtenerLabelSolicitudSeleccionada() {
  if (!form.value.solicitud_id) return "";
  const solicitud = solicitudesOriginal.value.find(s => s.id === form.value.solicitud_id);
  if (!solicitud) return "";

  return solicitud.solicitud_code
    ? `COD: ${solicitud.solicitud_code}`
    : `ID: ${solicitud.id}`;
}

function limpiarCamposSolicitante() {
  form.value.tipo_documento = "";
  form.value.num_documento = "";
  form.value.nombre_completo = "";
  // Al limpiar, también desbloquear el campo de solicitud
  campoSolicitudBloqueado.value = false;
}

/* ===========================
   MÉTODOS DE DOCUMENTOS
=========================== */
function onTipoDocumentoChange(newValue) {
  form.value.num_documento = "";
  // Al cambiar tipo documento, verificar si debemos bloquear solicitud
  if (newValue && !form.value.solicitud_id) {
    campoSolicitudBloqueado.value = true;
  }
}

function onNumDocumentoChange(newValue) {
  if (newValue && form.value.tipo_documento === 'RUC' && newValue.length !== 11) {
    console.warn("El RUC debe tener 11 dígitos");
  }
  // Al cambiar número documento, verificar si debemos bloquear solicitud
  if (newValue && !form.value.solicitud_id) {
    campoSolicitudBloqueado.value = true;
  }
}

function validarDocumento(val) {
  if (!val) return true;

  if (form.value.tipo_documento === 'RUC') {
    return (val && val.length === 11) || 'El RUC debe tener 11 dígitos';
  }

  if (form.value.tipo_documento === 'DNI') {
    return (val && val.length === 8) || 'El DNI debe tener 8 dígitos';
  }

  return true;
}

function obtenerMascaraDocumento() {
  if (form.value.tipo_documento === 'RUC') {
    return '###########';
  }
  return '########';
}

/* ===========================
   AUTOCOMPLETAR DNI
=========================== */
async function autocompletarSolicitante() {
  if (form.value.tipo_documento === "DNI" && form.value.num_documento?.length === 8 && !form.value.solicitud_id) {
    buscandoDni.value = true;
    try {
      const res = await DniService.getSolicitanteDni(form.value.num_documento);
      if (res?.existe) {
        form.value.nombre_completo = `${res.nombres} ${res.apellido_paterno} ${res.apellido_materno}`.trim();
      } else {
        form.value.nombre_completo = "";
      }
    } catch (error) {
      console.error("Error al consultar DNI:", error);
    } finally {
      buscandoDni.value = false;
    }
  }
}

/* ===========================
   MÉTODOS DE TUPAS
=========================== */
function filterTupas(val, update) {
  if (!val) {
    update(() => {
      tupasFiltradas.value = tupasOriginal.value;
    });
    return;
  }

  const needle = val.toLowerCase();
  update(() => {
    tupasFiltradas.value = tupasOriginal.value.filter(t =>
      t.denominacion.toLowerCase().includes(needle) ||
      t.label.toLowerCase().includes(needle)
    );
  });
}

function tupaSeleccionado(tupaId, currentIndex) {
  return form.value.tupas.some((t, idx) =>
    idx !== currentIndex && t.tupa_id === tupaId
  );
}

function agregarTupa() {
  // Agregar nuevo tupa vacío para selección manual
  form.value.tupas.push({
    tupa_id: null,
    cantidad: 1,
    Subtotal: 0,
    precio: 0,
    denominacion: ""
  });
}

function agregarTupaImportante(tupaId) {
  const tupa = tupasOriginal.value.find(t => t.id === tupaId);
  if (tupa) {
    // Verificar si el tupa ya está en la lista
    const tupaExistente = form.value.tupas.find(t => t.tupa_id === tupaId);

    if (tupaExistente) {
      // Si ya existe, incrementar la cantidad
      tupaExistente.cantidad += 1;
      actualizarSubtotal(form.value.tupas.indexOf(tupaExistente));
    } else {
      // Si no existe, agregarlo
      form.value.tupas.push({
        tupa_id: tupaId,
        cantidad: 1,
        Subtotal: Number(tupa.costo),
        precio: Number(tupa.costo),
        denominacion: tupa.denominacion,
      });
      actualizarTotal();
    }
  } else {
    console.warn(`No se encontró el tupa con ID ${tupaId}`);
  }
}

function limpiarTupas() {
  form.value.tupas = [];
  actualizarTotal();
}

function quitarTupa(idx) {
  form.value.tupas.splice(idx, 1);
  actualizarTotal();
}

function actualizarPrecioTupa(idx, tupa_id) {
  const tupa = tupasOriginal.value.find(t => t.id === tupa_id);
  if (tupa) {
    form.value.tupas[idx].precio = Number(tupa.costo);
    form.value.tupas[idx].denominacion = tupa.denominacion;
    actualizarSubtotal(idx);
  } else {
    form.value.tupas[idx].precio = 0;
    form.value.tupas[idx].denominacion = "";
    actualizarSubtotal(idx);
  }
}

function actualizarSubtotal(idx) {
  const t = form.value.tupas[idx];
  t.Subtotal = Number(t.precio) * Number(t.cantidad || 1);
  actualizarTotal();
}

function actualizarTotal() {
  form.value.total = form.value.tupas.reduce((sum, t) => sum + Number(t.Subtotal || 0), 0);
}

/* ===========================
   SUBMIT
=========================== */
async function submit() {
  if (!formValido.value) {
    console.error("Formulario inválido");
    return;
  }

  form.value.processing = true;
  try {
    const payload = {
      ...form.value,
      estado: 1,
      boleta_id: null,
      solicitud_id: form.value.solicitud_id ? Number(form.value.solicitud_id) : null,
      tupas: form.value.tupas.map(t => ({
        ...t,
        tupa_id: Number(t.tupa_id)
      }))
    };

    if (props.edit && props.id) {
      await PagoService.save({ ...payload, id: props.id });
    } else {
      await PagoService.save(payload);
    }
    emits("save");
  } catch (error) {
    console.error("Error guardando pago:", error);
  } finally {
    form.value.processing = false;
  }
}
</script>

<style scoped>
.my-card {
  width: 100%;
  max-width: 95vw;
  min-width: 300px;
}

.rounded-borders {
  border-radius: 8px;
}

.text-capitalize {
  text-transform: capitalize;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .col-md-6 {
    width: 100%;
  }
}

/* Mejoras visuales para los campos */
.q-field {
  margin-bottom: 12px;
}

/* Estilo para los tupas en grid responsive */
@media (min-width: 1024px) {
  .tupa-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 12px;
    align-items: center;
  }
}

/* Estilo para los botones de tupas importantes */
.tupa-importante-btn {
  min-height: 40px;
}
</style>
