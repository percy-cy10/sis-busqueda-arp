<template>
  <q-page>
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />
        <q-breadcrumbs-el label="Buscar Escrituras" icon="search" />
      </q-breadcrumbs>
    </div>

    <q-separator />
    <!-- <q-card class="my-card"> -->

      <!-- Botones de sección -->
      <q-card-section class="row q-gutter-sm">
        <q-btn
          label="Nombres"
          icon="person"
          class="bg-primary text-white q-px-xl q-py-sm rounded-borders shadow-3"
          :flat="seccionActiva !== 'nombres'"
          :unelevated="seccionActiva === 'nombres'"
          @click="seccionActiva = 'nombres'"
        />
        <!-- <q-btn
          label="Notarios"
          class="bg-primary text-white q-px-xl q-py-sm rounded-borders shadow-3"
          :flat="seccionActiva !== 'notarios'"
          :unelevated="seccionActiva === 'notarios'"
          @click="seccionActiva = 'notarios'"
        /> -->
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
        <div class="row">
          <!-- Otorgante -->
          <div class="col-6 row items-center q-gutter-sm">
            <q-select filled v-model="filtro.otorgante" use-input hide-selected fill-input input-debounce="200"
              label="Otorgante" :options="opcionesFiltradas.otorgante" :option-label="o => o" :option-value="o => o"
              @filter="(val, update) => filtrar('otorgante', otorgantesBase, val, update)"
              @input-value="actualizarInput('otorgante', $event)" clearable
              @update:model-value="guardarComoTexto('otorgante', $event)" @keyup.enter="buscar('otorgante')"
              class="col" use-chips new-value-mode="add-unique"
            />
            <q-btn icon="search" flat dense @click="buscar('otorgante')" :disable="!puedeBuscar('otorgante')" />
          </div>
          <!-- Favorecido -->
          <div class="col-6 row items-center q-gutter-sm">
            <q-select filled v-model="filtro.favorecido" use-input hide-selected fill-input input-debounce="200"
              label="Favorecido" :options="opcionesFiltradas.favorecido" :option-label="o => o" :option-value="o => o"
              @filter="(val, update) => filtrar('favorecido', favorecidosBase, val, update)"
              @input-value="actualizarInput('favorecido', $event)" clearable
              @update:model-value="guardarComoTexto('favorecido', $event)" @keyup.enter="buscar('favorecido')"
              class="col" use-chips new-value-mode="add-unique"
            />
            <q-btn icon="search" flat dense @click="buscar('favorecido')" :disable="!puedeBuscar('favorecido')" />
          </div>
        </div>
      </q-card-section>

      <!-- Filtros por Notarios -->
      <!-- <q-card-section v-if="seccionActiva === 'notarios'">
        <div class="row">
          <div class="col-12 row items-center q-gutter-sm">
            <q-select filled v-model="filtro.notario" use-input hide-selected fill-input input-debounce="200"
              label="Notario" :options="opcionesFiltradas.notario" :option-label="o => o" :option-value="o => o"
              @filter="(val, update) => filtrar('notario', notariosBase, val, update)"
              @input-value="actualizarInput('notario', $event)" clearable
              @update:model-value="guardarComoTexto('notario', $event)" @keyup.enter="buscar('notario')" class="col"
            />
            <q-btn icon="search" flat dense @click="buscar('notario')" />
          </div>
        </div>
      </q-card-section> -->

      <!-- Filtros por Bien -->
      <q-card-section v-if="seccionActiva === 'bien'">
        <div class="row">
          <div class="col-12 row items-center q-gutter-sm">
            <q-select filled v-model="filtro.bien" use-input hide-selected fill-input input-debounce="200"
              label="Bien" :options="opcionesFiltradas.bien" :option-label="o => o" :option-value="o => o"
              @filter="(val, update) => filtrar('bien', bienesBase, val, update)"
              @input-value="actualizarInput('bien', $event)" clearable
              @update:model-value="guardarComoTexto('bien', $event)" @keyup.enter="buscar('bien')" class="col"
            />
            <q-btn icon="search" flat dense @click="buscar('bien')" :disable="!puedeBuscar('bien')" />
          </div>
        </div>
      </q-card-section>

      <!-- Filtros por Fecha -->
      <!-- <q-card-section v-if="seccionActiva === 'fecha'">
        <div class="row q-col-gutter-md items-end">
          <div class="col-3">
            <div class="row items-end no-wrap">
              <InputAnio filled v-model="filtro.anio" type="number" label="Año" @keyup.enter="buscar('anio')" class="col" />
              <q-btn icon="search" flat dense @click="buscar('anio')" class="q-ml-sm" />
            </div>
          </div>
          <div class="col-3">
            <div class="row items-end no-wrap">
              <InputMes filled v-model="filtro.mes" type="number" label="Mes" min="1" max="12"
                @keyup.enter="buscar('mes')" class="col" />
              <q-btn icon="search" flat dense @click="buscar('mes')" class="q-ml-sm" />
            </div>
          </div>
          <div class="col-3">
            <div class="row items-end no-wrap">
              <InputDia filled v-model="filtro.dia" type="number" label="Día" min="1" max="31"
                @keyup.enter="buscar('dia')" class="col" />
              <q-btn icon="search" flat dense @click="buscar('dia')" class="q-ml-sm" />
            </div>
          </div>
          <div class="col-3">
            <q-btn icon="search" label="Buscar Fecha" color="primary" @click="buscar('fecha')" class="full-width" />
          </div>
        </div>
      </q-card-section> -->

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
          <!-- Buscar Fecha Completa -->
          <div class="col-3 row items-center q-gutter-sm">
            <q-btn icon="search" label="Buscar Fecha" color="primary" @click="buscar('fecha')" class="full-width"
              :disable="!puedeBuscar('fecha')" />
          </div>
        </div>
      </q-card-section>
      <!-- Buscar todo -->
      <!-- <q-card-section>
        <q-btn label="Buscar todo" color="primary" @click="buscar('todos')" class="full-width" />
      </q-card-section> -->

      <!-- Mensaje de búsqueda activa -->
      <!-- <q-card-section v-if="busquedaActiva">
        <div class="text-primary text-bold">
          Buscar por {{ busquedaActivaLabel }}: {{ filtro[busquedaActiva] }}
        </div>
      </q-card-section> -->

      <!-- Resultados -->
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
          <!-- Columna de Ver Escritura -->
          <template v-slot:body-cell-ver="props">
            <q-td :props="props">
              <q-btn v-if="props.row.file_name" icon="visibility" color="primary" flat dense label="Ver"
                @click="verArchivo(props.row.file_name)" />
              <span v-else class="text-grey">No disponible</span>
            </q-td>
          </template>

          <!-- Mensaje cuando no hay datos -->
          <template v-slot:no-data>
            <div class="full-width row flex-center text-grey q-gutter-sm">
              <q-icon name="warning" size="2em" />
              <span v-if="loading">Cargando datos...</span>
              <span v-else>No se encontraron resultados.</span>
            </div>
          </template>
          <template v-slot:body-cell-otorgante="props">
            <q-td :props="props">
              <span v-html="resaltarCoincidencia(props.row.otorgante, 'otorgante')"></span>
            </q-td>
          </template>
          <template v-slot:body-cell-favorecido="props">
            <q-td :props="props">
              <span v-html="resaltarCoincidencia(props.row.favorecido, 'favorecido')"></span>
            </q-td>
          </template>
          <template v-slot:body-cell-notario="props">
            <q-td :props="props">
              <span v-html="resaltarCoincidencia(props.row.notario, 'notario')"></span>
            </q-td>
          </template>
          <template v-slot:body-cell-bien="props">
            <q-td :props="props">
              <span v-html="resaltarCoincidencia(props.row.bien, 'bien')"></span>
            </q-td>
          </template>
          <template v-slot:body-cell-fecha="props">
            <q-td :props="props">
              <span :class="resaltarFecha(props.row.fecha)">
                {{ props.row.fecha }}
              </span>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    <!-- </q-card> -->
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import OtorganteService from 'src/services/OtorganteService'
import FavorecidoService from 'src/services/FavorecidoService'
import { api } from 'src/boot/axios'
import InputAnio from "src/components/InputAnio.vue";
import InputMes from "src/components/InputMes.vue";
import InputDia from "src/components/InputDia.vue";
import InputTextSelect from "src/components/InputTextSelect.vue";

// Estado de filtros y opciones
const filtro = ref({
  otorgante: '',
  favorecido: '',
  notario: '',
  bien: '',
  anio: '',
  mes: '',
  dia: ''
})

const opcionesFiltradas = ref({
  otorgante: [],
  favorecido: [],
  notario: [],
  bien: []
})

const otorgantesBase = ref([])
const favorecidosBase = ref([])
const notariosBase = ref([])
const bienesBase = ref([])
const seccionActiva = ref('')

// Resultados y tabla
const resultados = ref([])
const filter = ref('')
const loading = ref(false)
const pagination = ref({
  sortBy: "id",
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
})

function limpiarFecha() {
  filtro.value.anio = ''
  filtro.value.mes = ''
  filtro.value.dia = ''
}


// ...existing code...
function puedeBuscar(tipo) {
  if (['otorgante', 'favorecido', 'bien', 'notario'].includes(tipo)) {
    return filtro.value[tipo] && filtro.value[tipo].toString().trim().length >= 3
  }
  if (tipo === 'anio') {
    return filtro.value.anio && filtro.value.anio.toString().trim().length >= 4
  }
  if (tipo === 'mes') {
    return filtro.value.mes && filtro.value.mes.toString().trim().length >= 1
  }
  if (tipo === 'dia') {
    return filtro.value.dia && filtro.value.dia.toString().trim().length >= 2
  }
  if (tipo === 'fecha') {
    return filtro.value.anio && filtro.value.mes
  }
  return false
}
// ...existing code...

// Columnas base
const columns = [
  { name: 'otorgante', label: 'Otorgante', field: 'otorgante', align: 'center' },
  { name: 'favorecido', label: 'Favorecido', field: 'favorecido', align: 'center' },
  { name: 'bien', label: 'Bien', field: 'bien', align: 'center' },
  { name: 'protocolo', label: 'N° Protocolo', field: 'protocolo', align: 'center' },
  { name: 'cod_escritura', label: 'N° Escritura', field: 'cod_escritura', align: 'center' },
  { name: 'folio_inicial', label: 'Folio Inicial', field: 'folio_inicial', align: 'center' },
  { name: 'fecha', label: 'Fecha', field: 'fecha', align: 'center' },
  { name: 'ver', label: 'Ver Escritura', field: 'ver', align: 'center' }
]

// Detecta si solo hay un filtro de texto activo (y cuál)
const busquedaActiva = computed(() => {
  const campos = ['otorgante', 'favorecido', 'notario', 'bien']
  const activos = campos.filter(c => filtro.value[c] && filtro.value[c].length >= 3)
  return activos.length === 1 ? activos[0] : ''
})

// Etiqueta legible para el mensaje
const busquedaActivaLabel = computed(() => {
  switch (busquedaActiva.value) {
    case 'otorgante': return 'otorgante'
    case 'favorecido': return 'favorecido'
    case 'notario': return 'notario'
    case 'bien': return 'bien'
    default: return ''
  }
})

// Agrega la columna "Coincidencia" solo si hay un filtro activo
const columnsConCoincidencia = computed(() => {
  if (busquedaActiva.value) {
    return [
      ...columns,
      { name: 'coincidencia', label: 'Coincidencia', field: 'coincidencia', align: 'center' }
    ]
  }
  return columns
})

// Carga inicial de datos para selects
onMounted(async () => {
  const otorgantes = await OtorganteService.getData({ per_page: 1000 })
  otorgantesBase.value = otorgantes.data.map(o => o.nombre_completo)
  opcionesFiltradas.value.otorgante = otorgantesBase.value

  const favorecidos = await FavorecidoService.getData({ per_page: 1000 })
  favorecidosBase.value = favorecidos.data.map(f => f.nombre_completo)
  opcionesFiltradas.value.favorecido = favorecidosBase.value

  const notariosResp = await api.get('/api/notarios', { params: { rowsPerPage: 1000 } })
  notariosBase.value = notariosResp.data.data.map(n => n.nombre_completo)
  opcionesFiltradas.value.notario = notariosBase.value

  const bienesResp = await api.get('/api/escrituras', { params: { per_page: 1000 } })
  bienesBase.value = [...new Set(bienesResp.data.data.map(e => e.bien).filter(Boolean))]
  opcionesFiltradas.value.bien = bienesBase.value
})

// Métodos para los filtros de los selects
function actualizarInput(campo, valor) {
  filtro.value[campo] = valor
}

function guardarComoTexto(campo, valor) {
  if (typeof valor === 'string') {
    filtro.value[campo] = valor
  } else if (valor?.label) {
    filtro.value[campo] = valor.label
  } else {
    filtro.value[campo] = valor
  }
}

function filtrar(campo, base, val, update) {
  if (!val) {
    update(() => {
      opcionesFiltradas.value[campo] = base
    })
  } else {
    const texto = val.toLowerCase()
    if (texto.length >= 3) {
      const resultados = base.filter(op => op.toLowerCase().includes(texto))
      update(() => {
        opcionesFiltradas.value[campo] = resultados
      })
    } else {
      update(() => {
        opcionesFiltradas.value[campo] = []
      })
    }
  }
}

// Cuenta cuántos filtros están activos
function filtrosActivos() {
  let count = 0
  if (filtro.value.otorgante) count++
  if (filtro.value.favorecido) count++
  if (filtro.value.notario) count++
  if (filtro.value.bien) count++
  if (filtro.value.anio) count++
  if (filtro.value.mes) count++
  if (filtro.value.dia) count++
  return count
}

// Búsqueda automática cuando hay al menos 2 filtros activos
// watch(filtro, () => {
//   if (filtrosActivos() >= 2) {
//     buscarAuto()
//   } else {
//     resultados.value = []
//   }
// }, { deep: true })

function resaltarFecha(fecha) {
  const { anio, mes, dia } = filtro.value
  if (!anio && !mes && !dia) return ''
  // Formatea la fecha buscada
  const pad = n => n.toString().padStart(2, '0')
  let buscada = ''
  if (anio && mes && dia) buscada = `${anio}-${pad(mes)}-${pad(dia)}`
  else if (anio && mes) buscada = `${anio}-${pad(mes)}`
  else if (anio) buscada = `${anio}`
  // Resalta si coincide
  return fecha && fecha.startsWith(buscada) ? 'bg-yellow text-black' : ''
}

// Lógica de búsqueda automática usando datos reales de la API
async function buscarAuto() {
  const tieneFiltroTexto = ['otorgante', 'favorecido', 'notario', 'bien'].some(
    campo => filtro.value[campo] && filtro.value[campo].length >= 3
  )
  if (!tieneFiltroTexto && filtrosActivos() < 2) {
    resultados.value = []
    return
  }
  const params = {
    otorgante: filtro.value.otorgante,
    favorecido: filtro.value.favorecido,
    notario: filtro.value.notario,
    bien: filtro.value.bien,
    anio: filtro.value.anio,
    mes: filtro.value.mes,
    dia: filtro.value.dia,
    per_page: 100
  }
  // const resp = await api.get('/api/escrituras', { params })
  // // resultados.value = (resp.data.data || resp.data).map(e => ({
  // //   id: e.id,
  // //   favorecido: e.favorecidos?.map(f => f.nombre_completo).join(', '),
  // //   otorgante: e.otorgantes?.map(o => o.nombre_completo).join(', '),
  // //   bien: e.bien,
  // //   protocolo: e.libro?.protocolo || '',
  // //   cod_escritura: e.cod_escritura,
  // //   folio_inicial: e.cod_folioInicial,
  // //   fecha: e.fecha,
  // //   file_name: e.file_name,
  // //   coincidencia: e.coincidencia || ''
  // // }))

  const resp = await api.get('/api/escrituras', { params })
  resultados.value = (resp.data.data || resp.data).map(e => ({
    id: e.id,
    // favorecido: e.favorecidos?.map(f => f.nombre_completo).join('<br>'),
    // otorgante: e.otorgantes?.map(o => o.nombre_completo).join('<br>'),
    // favorecido: e.favorecidos
    //   ? e.favorecidos.map(f => f.nombre_completo).join(
    //       e.favorecidos.length > 1 ? '<br>' : ', '
    //     )
    //   : '',
    // otorgante: e.otorgantes
    //   ? e.otorgantes.map(o => o.nombre_completo).join(
    //       e.otorgantes.length > 1 ? '<br>' : ', '
    //     )
    //   : '',


    bien: e.bien,
    protocolo: e.libro?.protocolo || '',
    cod_escritura: e.cod_escritura,
    folio_inicial: e.cod_folioInicial,
    fecha: e.fecha, // <-- AGREGA ESTA LÍNEA
    file_name: e.file_name,
    coincidencia: e.coincidencia || ''
  }))
}



// Lógica para abrir el archivo PDF usando la variable de entorno
function verArchivo(fileName) {
  if (fileName) {
    const baseUrl = import.meta.env.VITE_APP_STORAGE_URL
    const url = `${baseUrl}/${fileName}`
    window.open(url, '_blank')
  }
}
function resaltarCoincidencia(texto, campo) {
  const valor = filtro.value[campo]
  if (!valor || valor.length < 3 || !texto) return texto
  // Escapa caracteres especiales para RegExp
  const valorEscapado = valor.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
  const regex = new RegExp(`(${valorEscapado})`, 'gi')
  return texto.replace(regex, '<span style="background:yellow">$1</span>')
}



/// 2. MODIFICA el método buscar para que sí consulte la API y actualice resultados:

async function buscar(tipo) {
  loading.value = true
  const pad = (n) => n.toString().padStart(2, '0')
  const anio = filtro.value.anio?.toString().trim() || ''
  const mes = filtro.value.mes?.toString().trim() || ''
  const dia = filtro.value.dia?.toString().trim() || ''
  let fechaCompleta = ''
  if (anio && mes && dia) {
    fechaCompleta = `${anio}-${pad(mes)}-${pad(dia)}`
  }

  // Prepara los parámetros según el tipo de búsqueda
  const params = {
    otorgante: tipo === 'otorgante' ? filtro.value.otorgante : '',
    favorecido: tipo === 'favorecido' ? filtro.value.favorecido : '',
    notario: tipo === 'notario' ? filtro.value.notario : '',
    bien: tipo === 'bien' ? filtro.value.bien : '',
    anio: tipo === 'anio' || tipo === 'fecha' ? anio : '',
    mes: tipo === 'mes' || tipo === 'fecha' ? mes : '',
    dia: tipo === 'dia' || tipo === 'fecha' ? dia : '',
    per_page: 100
  }

  // Si es "todos", manda todos los filtros
  if (tipo === 'todos') {
    params.otorgante = filtro.value.otorgante
    params.favorecido = filtro.value.favorecido
    params.notario = filtro.value.notario
    params.bien = filtro.value.bien
    params.anio = anio
    params.mes = mes
    params.dia = dia
  }

  // Si es búsqueda por fecha completa, solo busca si hay año, mes y día
  if (tipo === 'fecha' && !(anio && mes && dia)) {
    loading.value = false
    return
  }

  try {
    const resp = await api.get('/api/escrituras', { params })
    resultados.value = (resp.data.data || resp.data).map(e => ({
      id: e.id,
      favorecido: e.favorecidos?.map(f => f.nombre_completo).join('<br>'),
      otorgante: e.otorgantes?.map(o => o.nombre_completo).join('<br>'),
      // favorecido: e.favorecidos
      //   ? e.favorecidos.map(f => f.nombre_completo).join(
      //       e.favorecidos.length > 1 ? '<br>' : ', '
      //     )
      //   : '',
      // otorgante: e.otorgantes
      //   ? e.otorgantes.map(o => o.nombre_completo).join(
      //       e.otorgantes.length > 1 ? '<br>' : ', '
      //     )
      //   : '',

      bien: e.bien,
      fecha:e.fecha,
      protocolo: e.libro?.protocolo || '',
      cod_escritura: e.cod_escritura,
      folio_inicial: e.cod_folioInicial,
      file_name: e.file_name,
      coincidencia: e.coincidencia || ''
    }))
  } catch (e) {
    resultados.value = []
  }
  loading.value = false
}

// ...existing code...

function onRequest() {
  loading.value = false
}
</script>

<style scoped>
.my-card {
  width: 100%;
  max-width: auto;
  margin: 0 auto;
}
.bg-yellow {
  background: #fff59d;
  border-radius: 4px;
  padding: 2px 6px;
}
</style>
