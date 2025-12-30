<template>
  <q-page>
    <!-- Migas de pan -->
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />
        <q-breadcrumbs-el label="Buscar Escrituras" icon="search" />
      </q-breadcrumbs>
    </div>

    <q-separator />

    <!-- Botones de secciones -->
    <q-card-section class="row q-gutter-sm">
      <q-btn
        label="Nombres"
        icon="person"
        class="bg-primary text-white q-px-xl q-py-sm rounded-borders shadow-3"
        :flat="seccionActiva !== 'nombres'"
        :unelevated="seccionActiva === 'nombres'"
        @click="seccionActiva = 'nombres'"
      />

      <q-btn
        label="Bien"
        class="bg-primary text-white q-px-xl q-py-sm rounded-borders shadow-3"
        :flat="seccionActiva !== 'bien'"
        :unelevated="seccionActiva === 'bien'"
        @click="seccionActiva = 'bien'"
      />

      <q-btn
        label="Fecha"
        class="bg-primary text-white q-px-xl q-py-sm rounded-borders shadow-3"
        :flat="seccionActiva !== 'fecha'"
        :unelevated="seccionActiva === 'fecha'"
        @click="seccionActiva = 'fecha'"
      />
    </q-card-section>

    <!-- Filtros por Nombres -->
    <q-card-section v-if="seccionActiva === 'nombres'">
      <div class="row q-col-gutter-md">
        <!-- Otorgante -->
        <div class="col-6 row items-center q-gutter-sm">
          <q-select
            filled
            v-model="filtro.otorgante"
            use-input
            hide-selected
            fill-input
            input-debounce="200"
            label="Otorgante"
            :options="opcionesFiltradas.otorgante"
            @filter="(val, update) => filtrar('otorgante', otorgantesBase, val, update)"
            @input-value="actualizarInput('otorgante', $event)"
            clearable
            @update:model-value="guardarComoTexto('otorgante', $event)"
            @keyup.enter="buscar('otorgante')"
            class="col"
            use-chips
            new-value-mode="add-unique"
          />
          <q-btn
            icon="search"
            flat
            dense
            @click="buscar('otorgante')"
            :disable="!puedeBuscar('otorgante')"
          />
        </div>

        <!-- Favorecido -->
        <div class="col-6 row items-center q-gutter-sm">
          <q-select
            filled
            v-model="filtro.favorecido"
            use-input
            hide-selected
            fill-input
            input-debounce="200"
            label="Favorecido"
            :options="opcionesFiltradas.favorecido"
            @filter="(val, update) => filtrar('favorecido', favorecidosBase, val, update)"
            @input-value="actualizarInput('favorecido', $event)"
            clearable
            @update:model-value="guardarComoTexto('favorecido', $event)"
            @keyup.enter="buscar('favorecido')"
            class="col"
            use-chips
            new-value-mode="add-unique"
          />
          <q-btn
            icon="search"
            flat
            dense
            @click="buscar('favorecido')"
            :disable="!puedeBuscar('favorecido')"
          />
        </div>
      </div>

      <!-- Buscar por nombre general (Otorgante o Favorecido) -->
      <div class="row items-center q-gutter-sm q-mt-md">
        <q-select
          filled
          v-model="filtro.nombres"
          use-input
          hide-selected
          fill-input
          input-debounce="200"
          label="Buscar por Nombre (Otorgante o Favorecido)"
          :options="opcionesFiltradas.nombres"
          @filter="(val, update) => filtrar('nombres', nombresBase, val, update)"
          @input-value="actualizarInput('nombres', $event)"
          clearable
          @update:model-value="guardarComoTexto('nombres', $event)"
          @keyup.enter="buscar('nombres')"
          class="col"
          use-chips
          new-value-mode="add-unique"
        />
        <q-btn
          icon="search"
          flat
          dense
          @click="buscar('nombres')"
          :disable="!puedeBuscar('nombres')"
        />
      </div>
    </q-card-section>

    <!-- Filtros por Bien -->
    <q-card-section v-if="seccionActiva === 'bien'">
      <div class="row">
        <div class="col-12 row items-center q-gutter-sm">
          <q-select
            filled
            v-model="filtro.bien"
            use-input
            hide-selected
            fill-input
            input-debounce="200"
            label="Bien"
            :options="opcionesFiltradas.bien"
            @filter="(val, update) => filtrar('bien', bienesBase, val, update)"
            @input-value="actualizarInput('bien', $event)"
            clearable
            @update:model-value="guardarComoTexto('bien', $event)"
            @keyup.enter="buscar('bien')"
            class="col"
          />
          <q-btn
            icon="search"
            flat
            dense
            @click="buscar('bien')"
            :disable="!puedeBuscar('bien')"
          />
        </div>
      </div>
    </q-card-section>

    <!-- Filtros por Fecha -->
    <q-card-section v-if="seccionActiva === 'fecha'">
      <div class="row q-col-gutter-md">
        <!-- Año -->
        <div class="col-3 row items-center q-gutter-sm">
          <InputAnio
            filled
            v-model="filtro.anio"
            type="number"
            label="Año"
            min="1900"
            max="2100"
            clearable
            class="col"
            @clear="filtro.anio = ''"
            @keyup.enter="buscar('anio')"
          />
          <q-btn icon="search" flat dense @click="buscar('anio')" :disable="!puedeBuscar('anio')" />
        </div>

        <!-- Mes -->
        <div class="col-3 row items-center q-gutter-sm">
          <InputMes
            filled
            v-model="filtro.mes"
            type="number"
            label="Mes"
            min="1"
            max="12"
            clearable
            class="col"
            @clear="filtro.mes = ''"
            @keyup.enter="buscar('mes')"
          />
          <q-btn icon="search" flat dense @click="buscar('mes')" :disable="!puedeBuscar('mes')" />
        </div>

        <!-- Día -->
        <div class="col-3 row items-center q-gutter-sm">
          <InputDia
            filled
            v-model="filtro.dia"
            type="number"
            label="Día"
            min="1"
            max="31"
            clearable
            class="col"
            @clear="filtro.dia = ''"
            @keyup.enter="buscar('dia')"
          />
          <q-btn icon="search" flat dense @click="buscar('dia')" :disable="!puedeBuscar('dia')" />
        </div>

        <!-- Fecha completa -->
        <div class="col-3 row items-center q-gutter-sm">
          <q-btn
            icon="search"
            label="Buscar Fecha"
            color="primary"
            @click="buscar('fecha')"
            class="full-width"
            :disable="!puedeBuscar('fecha')"
          />
        </div>
      </div>
    </q-card-section>

    <!-- Tabla de resultados -->
    <q-card-section>
      <q-table
        :rows-per-page-options="[7, 10, 15]"
        class="my-sticky-header-table htable q-ma-sm"
        title="Resultados de la búsqueda"
        ref="tableRef"
        :rows="resultados"
        :columns="columnsConCoincidencia"
        row-key="id"
        v-model:pagination="pagination"
        :loading="loading"
        :filter="filter"
        binary-state-sort
        @request="onRequest"
      >
        <!-- Botón Ver Escritura -->
        <template v-slot:body-cell-ver="props">
          <q-td :props="props">
            <q-btn
              v-if="props.row.file_name"
              icon="visibility"
              color="primary"
              flat
              dense
              label="Ver"
              @click="verArchivo(props.row.file_name)"
            />
            <span v-else class="text-grey">No disponible</span>
          </q-td>
        </template>

        <!-- Mensaje si no hay datos -->
        <template v-slot:no-data>
          <div class="full-width row flex-center text-grey q-gutter-sm">
            <q-icon name="warning" size="2em" />
            <span v-if="loading">Cargando datos...</span>
            <span v-else>No se encontraron resultados.</span>
          </div>
        </template>

        <!-- Resaltado de coincidencias -->
        <template v-slot:body-cell-otorgante="props">
          <q-td :props="props">
            <span v-html="resaltarCoincidencia(props.row.otorgante || '', 'otorgante')"></span>
          </q-td>
        </template>

        <template v-slot:body-cell-favorecido="props">
          <q-td :props="props">
            <span v-html="resaltarCoincidencia(props.row.favorecido || '', 'favorecido')"></span>
          </q-td>
        </template>

        <template v-slot:body-cell-protocolo="props">
          <q-td :props="props">
            <div>
              <strong>{{ props.row.protocolo }}</strong>
              <div class="text-caption text-grey">
                ({{ props.row.notario }})
              </div>
            </div>
          </q-td>
        </template>

        <template v-slot:body-cell-bien="props">
          <q-td :props="props">
            <span v-html="resaltarCoincidencia(props.row.bien || '', 'bien')"></span>
          </q-td>
        </template>

        <template v-slot:body-cell-fecha="props">
          <q-td :props="props">
            <span :class="resaltarFecha(props.row.fecha)">
              {{ props.row.fecha }}
            </span>
          </q-td>
        </template>

        <template v-slot:body-cell-detalles="props">
          <q-td :props="props">
            <q-btn
              icon="info"
              color="secondary"
              flat
              dense
              round
              @click="abrirDetalles(props.row)"
            >
              <q-tooltip>Ver detalles</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card-section>

    <!-- Modal de detalles -->
    <q-dialog v-model="modalDetalles" maximized>
      <q-card class="q-pa-none shadow-10 rounded-borders" style="width: 80%; height: 85%;">
        <!-- Encabezado -->
        <q-card-section class="row items-center justify-between bg-primary text-white">
          <div class="text-h6">📄 Detalles de Escritura</div>
          <q-btn icon="close" color="negative" round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <!-- Contenido -->
        <div class="row no-wrap" style="height: calc(100% - 11%);">
          <!-- Columna izquierda (scrollable) -->
          <div class="col-5 q-pa-md bg-grey-1" style="overflow-y: auto;">
            <q-list bordered separator class="rounded-borders">
              <q-item>
                <q-item-section>
                  <div><q-icon name="mdi-text-box" color="primary" /> <b>Protocolo:</b> {{ escrituraSeleccionada.libro?.protocolo }}</div>
                  <div><b>N° Escritura:</b> {{ escrituraSeleccionada.cod_escritura }}</div>
                  <div><b>Folio Inicial:</b> {{ escrituraSeleccionada.cod_folioInicial }}</div>
                  <div><b>Folio Final:</b> {{ escrituraSeleccionada.cod_folioFinal }}</div>
                  <div><b>Fecha:</b> {{ escrituraSeleccionada.fecha }}</div>
                  <div><b>Año:</b> {{ escrituraSeleccionada.anio }} - <b>Mes:</b> {{ escrituraSeleccionada.mes }} - <b>Día:</b> {{ escrituraSeleccionada.dia }}</div>
                  <div><b>Subserie:</b> {{ escrituraSeleccionada.sub_serie?.nombre }}</div>
                </q-item-section>
              </q-item>

              <q-separator spaced />

              <q-item>
                <q-item-section>
                  <div><q-icon name="mdi-account-multiple" color="primary" /> <b>Otorgantes:</b></div>
                  <ul>
                    <li v-for="o in (escrituraSeleccionada.otorgantes || [])" :key="o.id || o.nombre_completo">
                      {{ o.nombre_completo }}
                    </li>
                  </ul>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section>
                  <div><q-icon name="mdi-handshake" color="primary" /> <b>Favorecidos:</b></div>
                  <ul>
                    <li v-for="f in (escrituraSeleccionada.favorecidos || [])" :key="f.id || f.nombre_completo">
                      {{ f.nombre_completo }}
                    </li>
                  </ul>
                </q-item-section>
              </q-item>

              <q-separator spaced />

              <q-item>
                <q-item-section>
                  <div>
                    <q-icon name="mdi-account-tie" color="primary" class="q-mr-sm" />
                    <b>Notario:</b> {{ escrituraSeleccionada.notario }}
                  </div>
                  <div>
                    <q-icon name="place" color="primary" class="q-mr-sm" />
                    <b>Ubicación:</b> {{ ubicacionNotarioSeleccionado }}
                  </div>
                  <div><b>Bien:</b> {{ escrituraSeleccionada.bien }}</div>
                  <div><b>Archivo:</b> {{ escrituraSeleccionada.file_name }}</div>
                  <div><b>Observaciones:</b> {{ escrituraSeleccionada.observaciones || 'Sin observaciones' }}</div>
                </q-item-section>
              </q-item>
            </q-list>
          </div>

          <!-- Columna derecha (PDF) -->
          <div class="col-7 bg-grey-3 flex flex-center">
            <template v-if="pdfUrl">
              <iframe
                :src="pdfUrl"
                style="width: 98%; height: 98%; border: none; border-radius: 12px; background: white;"
              ></iframe>
            </template>
            <div v-else class="text-grey text-center">📑 PDF no disponible</div>
          </div>
        </div>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue"
import { api } from "src/boot/axios"
import OtorganteService from 'src/services/OtorganteService'
import FavorecidoService from 'src/services/FavorecidoService'
import InputAnio from 'src/components/InputAnio.vue'
import InputMes from 'src/components/InputMes.vue'
import InputDia from 'src/components/InputDia.vue'
import NotarioService from "src/services/NotarioService"

// Estado global
const seccionActiva = ref("nombres")
const filtro = ref({
  otorgante: "",
  favorecido: "",
  nombres: "",
  notario: "",
  bien: "",
  anio: "",
  mes: "",
  dia: ""
})

const baseUrl = import.meta.env.VITE_APP_STORAGE_URL
const notariosMapCompleto = ref({})
const escrituraSeleccionada = ref({
  libro: {},
  otorgantes: [],
  favorecidos: [],
  fecha: '',
  file_name: null
})
const pdfUrl = computed(() => {
  return escrituraSeleccionada.value.file_name ? `${baseUrl}/${escrituraSeleccionada.value.file_name}` : null
})

const filter = ref('')
const resultados = ref([])
const loading = ref(false)
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
})

// Opciones base (listas completas de BD)
const modalDetalles = ref(false)
const otorgantesBase = ref([])
const favorecidosBase = ref([])
const nombresBase = ref([])
const bienesBase = ref([])
const opcionesFiltradas = ref({
  otorgante: [],
  favorecido: [],
  nombres: [],
  bien: []
})

// Columnas de la tabla
const columnsConCoincidencia = [
  { name: "otorgante", label: "Otorgante(s)", field: "otorgante", align: "left" },
  { name: "favorecido", label: "Favorecido(s)", field: "favorecido", align: "left" },
  { name: "protocolo", label: "Protocolo", field: "protocolo", align: "center" },
  { name: "bien", label: "Bien", field: "bien", align: "left" },
  { name: "cod_escritura", label: "N° Escritura", field: "cod_escritura", align: "center" },
  { name: "cod_folioInicial", label: "Folio Inicial", field: "cod_folioInicial", align: "center" },
  { name: "fecha", label: "Fecha", field: "fecha", align: "center" },
  { name: "ver", label: "Archivo", field: "ver", align: "center" },
  { name: "detalles", label: "Detalles", field: "detalles", align: "center" }
]

// Cargar datos iniciales
onMounted(async () => {
  try {
    // Cargar otorgantes
    const otorgantes = await OtorganteService.getData({ per_page: 1000 })
    otorgantesBase.value = otorgantes.data.map(o => o.nombre_completo)
    opcionesFiltradas.value.otorgante = otorgantesBase.value

    // Cargar favorecidos
    const favorecidos = await FavorecidoService.getData({ per_page: 1000 })
    favorecidosBase.value = favorecidos.data.map(f => f.nombre_completo)
    opcionesFiltradas.value.favorecido = favorecidosBase.value

    // Combinar para búsqueda general de nombres
    nombresBase.value = [...new Set([...otorgantesBase.value, ...favorecidosBase.value])]
    opcionesFiltradas.value.nombres = nombresBase.value

    // Cargar bienes
    const bienesResp = await api.get('/api/escrituras', { params: { per_page: 1000 } })
    bienesBase.value = [...new Set(bienesResp.data.data.map(e => e.bien).filter(Boolean))]
    opcionesFiltradas.value.bien = bienesBase.value

    // Cargar notarios
    cargarNotarios()
  } catch (error) {
    console.error("Error cargando datos iniciales:", error)
  }
})

// Cargar notarios
async function cargarNotarios() {
  try {
    const lista = await NotarioService.getAll()
    notariosMapCompleto.value = Object.fromEntries(lista.map(n => [n.id, n]))
  } catch (err) {
    console.error("Error cargando notarios:", err)
  }
}

// Verifica si se puede buscar (mínimo 3 caracteres o campo numérico válido)
function puedeBuscar(campo) {
  const valor = filtro.value[campo]
  if (!valor) return false

  if (["anio", "mes", "dia"].includes(campo)) {
    return !!valor
  }
  return valor.length >= 3
}

// Filtrado dinámico en los selects
function filtrar(campo, base, val, update) {
  if (val === "") {
    update(() => { opcionesFiltradas.value[campo] = base.value })
    return
  }

  update(() => {
    if (!base.value) return
    const needle = val.toLowerCase()
    opcionesFiltradas.value[campo] = base.value.filter(v =>
      v.toLowerCase().includes(needle)
    )
  })
}

// Mantener siempre el texto escrito en el input
function actualizarInput(campo, valor) {
  filtro.value[campo] = valor
}

// Guardar el valor como string (por si viene como objeto de Quasar)
function guardarComoTexto(campo, valor) {
  if (typeof valor === "string") filtro.value[campo] = valor
}

// Búsqueda

// async function buscar(campo) {
//   if (!puedeBuscar(campo) && campo !== "general") return

//   loading.value = true
//   try {
//     const params = {}

//     if (campo === "general") {
//       // enviar todos los filtros activos
//       for (const key in filtro.value) {
//         if (filtro.value[key]) {
//           params[key] = filtro.value[key]
//         }
//       }
//     } else if (campo === "nombres") {
//       params.nombres = filtro.value.nombres
//     } else if (campo === "fecha") {
//       params.anio = filtro.value.anio
//       params.mes = filtro.value.mes
//       params.dia = filtro.value.dia
//     } else {
//       params[campo] = filtro.value[campo]
//     }

//     const res = await api.get("/api/escrituras", { params })

//     resultados.value = (res.data.data || res.data).map(e => ({
//       ...e,
//       otorgante: e.otorgantes?.map(o => o.nombre_completo).join('<br>') || '',
//       favorecido: e.favorecidos?.map(f => f.nombre_completo).join('<br>') || '',
//       otorgantes: e.otorgantes || [],
//       favorecidos: e.favorecidos || [],
//       libro: e.libro || {},
//       protocolo: e.libro?.protocolo || '',
//       notario: e.libro?.notario?.nombre_completo ||
//         notariosMapCompleto.value[e.libro?.notario_id]?.nombre_completo ||
//         'No asignado',
//       cod_folioInicial: e.folio_inicial || e.cod_folioInicial || '',
//       cod_folioFinal: e.folio_final || e.cod_folioFinal || ''
//     }))

//     pagination.value.rowsNumber = res.data.total || resultados.value.length
//   } catch (err) {
//     console.error("Error al buscar:", err)
//     resultados.value = []
//   } finally {
//     loading.value = false
//   }
// }
async function buscar(campo) {
  if (!puedeBuscar(campo) && campo !== "general") return

  loading.value = true
  try {
    const params = {
      page: pagination.value.page,
      per_page: pagination.value.rowsPerPage
    }

    if (campo === "general") {
      for (const key in filtro.value) {
        if (filtro.value[key]) {
          params[key] = filtro.value[key]
        }
      }
    } else if (campo === "nombres") {
      params.nombres = filtro.value.nombres
    } else if (campo === "fecha") {
      params.anio = filtro.value.anio
      params.mes = filtro.value.mes
      params.dia = filtro.value.dia
    } else {
      params[campo] = filtro.value[campo]
    }

    const res = await api.get("/api/escrituras", { params })

    resultados.value = (res.data.data || res.data).map(e => ({
      ...e,
      otorgante: e.otorgantes?.map(o => o.nombre_completo).join('<br>') || '',
      favorecido: e.favorecidos?.map(f => f.nombre_completo).join('<br>') || '',
      otorgantes: e.otorgantes || [],
      favorecidos: e.favorecidos || [],
      libro: e.libro || {},
      protocolo: e.libro?.protocolo || '',
      notario: e.libro?.notario?.nombre_completo ||
        notariosMapCompleto.value[e.libro?.notario_id]?.nombre_completo ||
        'No asignado',
      cod_folioInicial: e.folio_inicial || e.cod_folioInicial || '',
      cod_folioFinal: e.folio_final || e.cod_folioFinal || ''
    }))

    // El backend debe devolver el total global
    pagination.value.rowsNumber = res.data.total
  } catch (err) {
    console.error("Error al buscar:", err)
    resultados.value = []
  } finally {
    loading.value = false
  }
}

// Resaltado de coincidencias
function escapeRegExp(string) {
  return String(string).replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

function resaltarCoincidencia(texto, campo) {
  if (!texto || typeof texto !== "string") return texto || ""

  let termino = filtro.value[campo]

  // Si el filtro viene como array (use-chips), unirlo en un string
  if (Array.isArray(termino)) {
    termino = termino.join(' ')
  }

  if (!termino || String(termino).length < 1) return texto

  try {
    const safe = escapeRegExp(String(termino))
    const regex = new RegExp(`(${safe})`, "gi")
    return texto.replace(regex, '<span class="bg-yellow text-black"><b>$1</b></span>')
  } catch (err) {
    console.error("Error en resaltarCoincidencia:", err)
    return texto
  }
}

function resaltarFecha(fecha) {
  if (!fecha) return ""
  if (filtro.value.fecha && fecha.includes(filtro.value.fecha)) {
    return "bg-yellow"
  }
  return ""
}

const ubicacionNotarioSeleccionado = computed(() => {
  const libro = escrituraSeleccionada.value.libro || {}
  const notarioObj = libro.notario || null

  // Si el notario está embebido en el objeto 'libro'
  if (notarioObj && notarioObj.ubigeo && notarioObj.ubigeo.nombre) {
    return notarioObj.ubigeo.nombre
  }

  // Si solo está el ID del notario, buscamos en el mapa
  const notarioId = libro.notario_id
  const mapa = notariosMapCompleto.value || {}

  if (notarioId != null && mapa[notarioId] && mapa[notarioId].ubigeo && mapa[notarioId].ubigeo.nombre) {
    return mapa[notarioId].ubigeo.nombre
  }

  return 'No asignado'
})

// Abrir detalles
function abrirDetalles(escritura) {
  escrituraSeleccionada.value = {
    ...escritura,
    fecha: escritura.fecha || "",
    libro: escritura.libro || {},
    otorgantes: escritura.otorgantes || [],
    favorecidos: escritura.favorecidos || [],
    file_name: escritura.file_name || null
  }
  modalDetalles.value = true
}

// Eventos de tabla
// async function onRequest(props) {
//   pagination.value = props.pagination
//   await buscar("general")
// }

async function onRequest(props) {
  pagination.value = props.pagination

  // Si no hay filtros, no traemos nada
  const hayFiltros = Object.values(filtro.value).some(v => v && v !== "")
  if (!hayFiltros) {
    resultados.value = []   // vacío hasta que hagan una búsqueda
    pagination.value.rowsNumber = 0
    return
  }

  // Ejecutar búsqueda con los filtros actuales
  await buscar("general")
}


// Abrir archivo PDF
function verArchivo(fileName) {
  if (!fileName) return
  const url = `${baseUrl}/${fileName}`
  window.open(url, "_blank")
}
</script>

<style scoped>
.bg-yellow {
  background: #fff59d;
  border-radius: 4px;
  padding: 2px 6px;
}
</style>
