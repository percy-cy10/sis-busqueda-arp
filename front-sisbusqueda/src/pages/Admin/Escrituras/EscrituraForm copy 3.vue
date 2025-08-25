<template>
  <q-card class="my-card">
    <!-- Título -->
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">{{ title }}</div>
    </q-card-section>

    <q-form @submit.prevent="submit">
      <q-card-section class="q-pa-md">


        <!-- Fila 1: Fechas | Bien – Código de Escritura -->
        <div class="row q-col-gutter-md q-mb-md">
            <div class="col-6">
            <q-badge class="q-mb-xs text-subtitle2">Bien y Código de Escritura</q-badge>
            <div class="row input-group">
              <q-input dense outlined v-model="form.bien" label="Bien *" :error="!!form.errors.bien" class="col" />
              <!-- Diálogo para el formulario de Subserie -->
                <q-dialog v-model="dialogSubserie" persistent>
                  <SubSeriesForm
                    :title="'Nueva Subserie'"
                    @save="handleSaveSubserie"
                  />
                </q-dialog>
              <q-input dense outlined v-model="form.cod_escritura" label="Código de Escritura" mask="E-######" :error="!!form.errors.cod_escritura" class="col" />
            </div>
          </div>
          <div class="col-6">
            <q-badge class="q-mb-xs text-subtitle2">Fecha</q-badge>
            <div class="row input-group">
              <InputAnio :requerido="true" class="col" dense outlined v-model="form.anio" :error="!!form.errors.anio"/>
              <InputMes :readonly="!form.anio" class="col" dense outlined v-model="form.mes" :modelAnio="form.anio" :error="!!form.errors.mes"/>
              <InputDia :readonly="!form.anio || !form.mes" class="col" dense outlined v-model="form.dia" v-model:modelAnio="form.anio" v-model:modelMes="form.mes" :error="!!form.errors.dia" />
            </div>
          </div>

        </div>

        <!-- Fila 2: Subserie – Libro | Folios -->
        <div class="row q-col-gutter-md q-mb-md">
          <div class="col-6">
            <q-badge class="q-mb-xs text-subtitle2">Subserie y Libro</q-badge>
            <div class="row q-gutter-sm">
              <q-select class="col" v-model="form.subserie_id" :options="subseries" label="Subserie *" :error="!!form.errors.subserie_id" option-label="nombre" option-value="id" dense outlined emit-value map-options />
              <q-btn class="btn-alto" dense icon="person_add" color="primary" @click="dialogSubserie = true" size="md" />
              <q-select class="col" v-model="form.libro_id" :options="libros" label="Libro *" :error="!!form.errors.libro_id" option-label="protocolo" option-value="id" dense outlined emit-value map-options />
            </div>
          </div>
          <div class="col-6">
            <q-badge class="q-mb-xs text-subtitle2">Folios</q-badge>
            <div class="row input-group">
              <q-input class="col" dense outlined v-model="form.cod_folioInicial" label="Folio Inicial *" mask="F-######" :loading="form.validating" @change="form.validate('cod_folioInicial')" :error="form.invalid('cod_folioInicial')">
                <template v-slot:append>
                  <q-toggle v-model="form.vueltaFI" size="xm" dense checked-icon="check" color="blue" unchecked-icon="clear" :disable="deshabili_FI" />
                  <div class="text-caption">Vuelta</div>
                </template>
              </q-input>
              <q-input class="col" dense outlined v-model="form.cod_folioFinal" label="Folio Final *" mask="F-######" :loading="form.validating" @change="form.validate('cod_folioFinal')" :error="form.invalid('cod_folioFinal')">
                <template v-slot:append>
                  <q-toggle v-model="form.vueltaFF" size="xm" dense checked-icon="check" color="blue" unchecked-icon="clear" :disable="deshabili_FF" />
                  <div class="text-caption">Vuelta</div>
                </template>
              </q-input>
            </div>
          </div>
        </div>




        <!-- Sección de Otorgante y Favorecido -->
        <q-separator color="primary" class="q-my-s" />
        <q-badge class="q-mb-sm text-subtitle2">Datos de Otorgante y Favorecido</q-badge>

        <div class="col-12">
          <div class="row q-col-gutter-md">
            <!-- Grupo Otorgante -->
            <div class="col-6">
              <div class="row items-start q-gutter-sm">
                <q-select
                  class="col"
                  v-model="form.otorgantes"
                  :options="otorgantesOptions"
                  label="Otorgantes *"
                  option-label="nombre_completo"
                  option-value="id"
                  dense
                  outlined
                  emit-value
                  map-options
                  multiple
                  use-chips
                  @filter="buscarOtorgantes"
                  :rules="[val => val && val.length > 0 || 'Seleccione al menos un otorgante']"
                />
                <q-btn
                  class="btn-alto"
                  dense
                  icon="person_add"
                  color="primary"
                  @click="dialogOtorgante = true"
                  size="md"
                />
              </div>
            </div>

            <!-- Diálogo para el formulario de Otorgante -->
            <q-dialog v-model="dialogOtorgante" persistent>
              <OtorganteForm
                :title="'Nuevo Otorgante'"
                @save="handleSaveOtorgante"
              />
            </q-dialog>

            <!-- Grupo Favorecido -->
            <div class="col-6">
              <div class="row items-start q-gutter-sm">
                <q-select
                  class="col"
                  v-model="form.favorecidos"
                  :options="favorecidosOptions"
                  label="Favorecidos *"
                  option-label="nombre_completo"
                  option-value="id"
                  dense
                  outlined
                  emit-value
                  map-options
                  multiple
                  use-chips
                  @filter="buscarFavorecidos"
                  :rules="[val => val && val.length > 0 || 'Seleccione al menos un favorecido']"
                />
                <q-btn
                  class="btn-alto"
                  densed
                  icon="person_add"
                  color="primary"
                  @click="dialogFavorecido = true"
                  size="md"
                />
              </div>
            </div>

            <!-- Diálogo para el formulario de Favorecido -->
            <q-dialog v-model="dialogFavorecido" persistent>
              <FavorecidoForm
                :title="'Nuevo Favorecido'"
                @save="handleSaveFavorecido"
              />
            </q-dialog>

          </div>
        </div>



        <q-separator color="primary" class="q-my-s" />

        <!-- Fila: Archivo adjunto | Acciones -->
        <div class="row q-col-gutter-md q-mb-md">
          <!-- Archivo -->
          <div class="col-6">
            <q-badge class="q-mb-xs text-subtitle2">Archivo adjunto (PDF)</q-badge>
            
            <div>
              <!-- Si ya hay archivo cargado o en BD -->
              <div v-if="file || (edit && form.file_name)">
                <q-chip
                  removable
                  color="primary"
                  text-color="white"
                  icon="attach_file"
                  @remove="removerArchivo"
                >
                  {{ file ? file.name : obtenerNombreArchivo(form.file_name) }}
                </q-chip>
              </div>

              <!-- Si no hay archivo -->
              <div v-else>
                <input
                  type="file"
                  @change="handleFileUpload"
                  accept=".pdf,image/*"
                  class="q-mb-sm"
                />
              </div>
            </div>
          </div>

          <!-- Acciones -->
          <div class="col-6 flex items-center">
            <q-badge class="q-mb-xs text-subtitle2">Acciones</q-badge>
            <div>
              <q-btn
                round dense flat
                icon="visibility"
                @click="verArchivo"
                v-if="edit && form.file_name && !file"
                class="q-ml-sm"
              />
              <!-- Aquí puedes agregar más acciones -->
            </div>
          </div>
        </div>



      <q-separator color="primary" class="q-my-s" />
      <q-badge class="q-mb-sm text-subtitle2">Observaciones</q-badge>


      <q-input dense outlined v-model="form.observaciones" label="Observaciones" type="textarea" class="q-mb-s" />
      </q-card-section>

      <!-- Botones de Acción -->
      <q-card-actions align="right">
        <q-btn label="Cancelar" flat v-close-popup />
        <q-btn label="Guardar" :loading="form.processing" type="submit" color="positive" />
      </q-card-actions>
    </q-form>
  </q-card>
</template>


<script setup>
import { ref, watch, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { useForm } from "laravel-precognition-vue";

import SubSerieService from 'src/services/SubSerieService.js';
import LibroService from 'src/services/LibroService.js';
import EscrituraService from 'src/services/EscrituraService.js';
import InputAnio from 'src/components/InputAnio.vue';
import InputMes from 'src/components/InputMes.vue';
import InputDia from 'src/components/InputDia.vue';

// Importaciones adicionales
import OtorganteForm from 'src/pages/Admin/Otorgantes/OtorganteForm.vue';
import FavorecidoForm from 'src/pages/Admin/Favorecidos/FavorecidoForm.vue';
import OtorganteService from 'src/services/OtorganteService';
import FavorecidoService from 'src/services/FavorecidoService';

import SelectLibro from 'src/components/SelectLibro.vue'
import SelectSubSerie from 'src/components/SelectSubSerie.vue'
import SelectFavorecido from 'src/components/SelectFavorecido.vue'
import SelectOtrogante from 'src/components/SelectOtrogante.vue'


// import OtorganteForm from 'src/pages/Admin/Otorgantes/OtorganteForm.vue';
// import FavorecidoForm from 'src/pages/Admin/Favorecidos/FavorecidoForm.vue';
import SubSeriesForm from 'src/pages/Admin/SubSeries/SubSeriesForm.vue';

// Nuevas variables y métodos
const otorgantesOptions = ref([]);
const favorecidosOptions = ref([]);

const file = ref(null);

const dialogOtorgante = ref(false);
const dialogFavorecido = ref(false);
const dialogSubserie = ref(false);


// const emits = defineEmits(['save']);
// const props = defineProps({ title: String, id: Number, edit: { type: Boolean, default: false } });
const $q = useQuasar();
const emits = defineEmits(['save']);
const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false }
});


// Métodos para manejar los formularios
const abrirFormulario = (tipo) => {
  tipoFormulario.value = tipo;
  dialog.value = true;
};

//cargar archivo pdf

// const handleFileUpload = (event) => {
//   const uploadedFile = event.target.files[0];
//   if (uploadedFile && uploadedFile.type === "application/pdf") {
//     file.value = uploadedFile;
//   }
//   else if (uploadedFile) {
//     $q.notify({
//       type: "negative",
//       message: "Solo se permiten archivos PDF.",
//     });
//     file.value = null;
//   } else {
//     file.value = null; // Permitir que sea nulo si no se selecciona archivo
//   }
//   // else {
//   //   $q.notify({
//   //     type: "negative",
//   //     message: "Solo se permiten archivos PDF.",
//   //   });
//   //   file.value = null;
//   // }
// };

//AGREGADOS


//const file = ref(null) // Para el archivo nuevo

const handleFileUpload = (event) => {
  const uploadedFile = event?.target?.files?.[0];
  if (!uploadedFile) return;

  if (uploadedFile.type === "application/pdf") {
    file.value = uploadedFile;
    event.target.value = ''; // limpiar input
  } else {
    $q.notify({ type: "negative", message: "Solo se permiten archivos PDF." });
    event.target.value = '';
  }
};

const removerArchivo = () => {
  file.value = null;
  if (props.edit) {
    form.file_name = null;
  }
};






///////////////////////


// const handleFileUpload = (event) => {
//   const uploadedFile = event.target.files[0];
//   const fileInput = event.target;

//   if (!uploadedFile) {
//     fileInput.value = ''; // Limpiar input
//     file.value = null; // Forzar null si no hay archivo
//     return;
//   }

//   if (uploadedFile.type === "application/pdf") {
//     file.value = uploadedFile;
//   } else {
//     $q.notify({ type: "negative", message: "Solo PDF" });
//     fileInput.value = '';
//     file.value = null;
//   }
// };



function obtenerNombreArchivo(rutaCompleta, path) {
  // Obtiene el nombre del archivo de la ruta completa


  return rutaCompleta.split('//').pop(); // para Windows
  // Si fuera Linux/macOS, usa rutaCompleta.split('/').pop()
}



//cambiar nombre de archivo
const generateFileName = () => {
  const libro = libros.value.find((l) => l.id === form.libro_id); // Buscar el libro seleccionado
  const libroCode = libro?.protocolo || "sin-libro"; // Usar el protocolo del libro o un valor por defecto
  console.log("Libro protocolo:", libroCode); // Log para verificar el protocolo del libro
  const escrituraCode = form.cod_escritura || "sin-escritura";
  console.log("Codigo de escritura:", escrituraCode); // Log para verificar el código de escritura
  const fileName = `${libroCode}-${escrituraCode}.pdf`;
  console.log("Generated file name:", fileName); // Log para verificar el nombre final del archivo
  return fileName;
};


// const verArchivo = () => {
//   if (form.file_name) {
//     window.open(form.file_name, '_blank'); // Abre el PDF en una nueva pestaña
//   }
// };

const verArchivo = () => {

  if (form.file_name) {
    const baseUrl = import.meta.env.VITE_APP_STORAGE_URL;
    const url = `${baseUrl}/${form.file_name}`;
    window.open(url, '_blank'); // Abre el PDF en una nueva pestaña
  }


};




// const handleSave = () => {
//   dialog.value = false;
//   actualizarListados();
// };

const handleSaveOtorgante = () => {
  dialogOtorgante.value = false; // Cierra el diálogo
  actualizarListados(); // Actualiza la lista de otorgantes
};

const handleSaveFavorecido = () => {
  dialogFavorecido.value = false; // Cierra el diálogo
  actualizarListados(); // Actualiza la lista de favorecidos
};

const handleSaveSubserie = () => {
  dialogSubserie.value = false; // Cierra el diálogo
  loadSubseriesAndLibros(); // Actualiza la lista de subseries
};


const actualizarListados = async () => {
  try {
    const [otorgantesRes, favorecidosRes] = await Promise.all([
      OtorganteService.getData(),
      FavorecidoService.getData()
    ]);

    otorgantesOptions.value = otorgantesRes.data || []; // Accede a response.data
    favorecidosOptions.value = favorecidosRes.data || []; // Accede a response.data
  } catch (error) {
    console.error("Error actualizando listados:", error);
    $q.notify({ type: 'negative', message: 'Error actualizando registros' });
  }
};


// Funciones de búsqueda mejoradas
const buscarOtorgantes = async (val, update) => {
  if (val === '') {
    update(() => {
      otorgantesOptions.value = otorgantesOptions.value.slice(0, 10);
    });
    return;
  }

  try {
    const resultados = await OtorganteService.buscar(val);
    update(() => {
      otorgantesOptions.value = resultados.data;
    });
  } catch (error) {
    console.error("Error buscando otorgantes:", error);
  }
};

const buscarFavorecidos = async (val, update) => {
  if (val === '') {
    update(() => {
      favorecidosOptions.value = favorecidosOptions.value.slice(0, 10);
    });
    return;
  }

  try {
    const resultados = await FavorecidoService.buscar(val);
    update(() => {
      favorecidosOptions.value = resultados.data;
    });
  } catch (error) {
    console.error("Error buscando favorecidos:", error);
  }
};


// const $q = useQuasar();
const dialog = ref(false);
const tipoFormulario = ref('');


// const form = useForm('post', '/api/escrituras', {
const form = useForm(props.edit ? 'put' : 'post', props.edit ? `/api/escrituras/${props.id}` : '/api/escrituras', {
  subserie_id: null,
  libro_id: null,
  otorgantes: [], // Array para múltiples otorgantes
  favorecidos: [],
  bien: '',
  anio: '',
  mes: '',
  dia: '',
  fecha: '',
  cod_folioInicial: '',
  cod_escritura: '',
  cod_folioFinal: '',
  observaciones: '',
  vueltaFI: false,
  vueltaFF: false,
});


// const subseries = ref([]);
// const libros = ref([]);

const subseries = ref([]);
const libros = ref([]);
const deshabili_FI = ref(form.cod_folioInicial === '');
const deshabili_FF = ref(form.cod_folioFinal === '');
// const deshabili_FI = ref(form.cod_folioInicial === '');
// const deshabili_FF = ref(form.cod_folioFinal === '');


// Cargar Subseries y Libros

const loadSubseriesAndLibros = async () => {
  try {
    // Carga con validación de estructura
    const [subseriesRes, librosRes] = await Promise.all([
      SubSerieService.getData(),
      LibroService.getData()
    ]);

    subseries.value = subseriesRes.data?.data || subseriesRes.data || [];

    // libros.value = librosRes.data?.data || librosRes.data || [];

    // Filtra solo los libros con estado activo
    libros.value = (librosRes.data?.data || librosRes.data || []).filter(libro => libro.estado === 1);

  } catch (error) {
    console.error("Error cargando datos:", error);
  }
};


onMounted(async () => {
  console.log("onMounted: Iniciando carga de subseries y libros...");
  await loadSubseriesAndLibros();

  await actualizarListados();

  if (props.edit) {
    console.log("onMounted: Modo edición activado, llamando a EscrituraService.get con id =", props.id);

    try {
      const response = await EscrituraService.get(props.id);
      console.log("onMounted: Respuesta completa de API:", response);

      if (!response) {
        throw new Error("Datos no disponibles");
      }

      console.log("onMounted: Datos obtenidos y asignando al formulario...");
      form.setData({
        ...response,
        anio: response.anio ? response.anio.toString() : "",
        mes: response.mes ? Number(response.mes) : 1,
        dia: response.dia ? Number(response.dia) : 1,
        // dia: response.dia ? Number(response.dia) : 1, //
        // dia: response.dia !== undefined && response.dia !== null ? Number(response.dia) : 1, //
        favorecidos: response.favorecidos.map(f => f.id), // Asegurar que sean IDs
        otorgantes: response.otorgantes.map(o => o.id),  // Asegurar que sean IDs
        // asegurarse de que file_name esté presente
        file_name: response.file_name



      });

      // Configurar toggles para folios según si terminan en " V"
      form.vueltaFI = (response.cod_folioInicial || '').trim().endsWith("V");
      deshabili_FI.value = !form.vueltaFI;

      form.vueltaFF = (response.cod_folioFinal || '').trim().endsWith("V");
      deshabili_FF.value = !form.vueltaFF;

      console.log("onMounted: Datos cargados correctamente en el formulario.");

    } catch (error) {
      console.error("onMounted: Error cargando datos:", error);
      $q.notify({ type: 'negative', message: 'Error al cargar la escritura' });
    }
  }
});






watch(() => form.cod_folioInicial, (newVal) => {
  if (newVal === '') {
    form.vueltaFI = false;
    deshabili_FI.value = true;
  } else {
    deshabili_FI.value = false;
  }
});

// watch(
//   () => [form.anio, form.mes, form.dia],
//   ([newAnio, newMes, newDia]) => {
//     if (newAnio && newMes && newDia) {
//       form.fecha = `${newAnio}-${String(newMes).padStart(2, "0")}-${String(newDia).padStart(2, "0")}`;
//     }
//   },
//   { immediate: true, deep: true } // <-- Activar deep
// );

watch(
  () => [form.anio, form.mes, form.dia],
  ([newAnio, newMes, newDia]) => {
    if (newAnio && newMes && newDia) {
      form.fecha = `${newAnio}-${String(newMes).padStart(2, "0")}-${String(newDia).padStart(2, "0")}`;
    }
  },
  { immediate: true, deep: true }
);

watch(() => form.cod_folioFinal, (newVal) => {
  if (newVal === '') {
    form.vueltaFF = false;
    deshabili_FF.value = true;
  } else {
    deshabili_FF.value = false;
  }
});



//holaaaaaaaaa

const submit = async () => {
  // Limpiar "V" antes de añadirla
  if (form.vueltaFI) {
    form.cod_folioInicial = form.cod_folioInicial.replace(/ V$/, '') + ' V';
  }
  if (form.vueltaFF) {
    form.cod_folioFinal = form.cod_folioFinal.replace(/ V$/, '') + ' V';
  }

  // Verificar que favorecidos y otorgantes sean arrays
  if (!Array.isArray(form.favorecidos) || !Array.isArray(form.otorgantes)) {
    console.error('Los campos favorecidos y otorgantes deben ser arrays.');
    return;
  }

  // Validar el archivo (opcional, pero debe ser PDF si se proporciona)
  if (file.value && file.value.type !== "application/pdf") {
      $q.notify({
        type: "negative",
        message: "El archivo debe ser un PDF.",
      });
      return;
    }

  // Generar el nombre del archivo
  // Generar el nombre del archivo solo si hay un archivo seleccionado
  const fileName = file.value ? generateFileName() : null;
  console.log("Submitting with file name:", fileName);
  // const fileName = generateFileName();
  // console.log("Submitting with file name:", fileName);

  // Crear el objeto de datos para enviar
  const data = {
    subserie_id: form.subserie_id,
    libro_id: form.libro_id,
    otorgantes: form.otorgantes,
    favorecidos: form.favorecidos,
    bien: form.bien,
    anio: form.anio,
    mes: form.mes,
    dia: form.dia,
    fecha: form.fecha,
    cod_folioInicial: form.cod_folioInicial,
    cod_escritura: form.cod_escritura,
    cod_folioFinal: form.cod_folioFinal,
    observaciones: form.observaciones,
    vueltaFI: form.vueltaFI,
    vueltaFF: form.vueltaFF,
    // file: file.value || undefined, // Archivo PDF
    // file_name: fileName, // Nombre del archivo
    // file: file.value instanceof File ? file.value : undefined,
    // file_name: fileName || (props.edit ? form.file_name : undefined), // No enviar null
  };

   // Si no estamos en edición y no hay archivo, quita ambos campos:
    // Manejar los tres casos del archivo
  if (file.value) {
    // Caso 2: Se seleccionó un archivo nuevo
    data.file = file.value;
    data.file_name = fileName;
  } else if (props.edit && form.file_name) {
    // Caso 3: Archivo de la base de datos está presente y no se seleccionó uno nuevo

    data.file_name = generateFileName();
  } else {
    // Caso 1: No hay archivo nuevo ni en la base de datos
    delete data.file;
    delete data.file_name;
  }
   // Log para depuración
  console.log("Datos enviados:", data);

  try {
    if (props.edit) {
      await EscrituraService.save({ ...data, id: props.id });
    } else {
      await EscrituraService.save(data);
    }

    $q.notify({
      type: "positive",
      message: "Escritura guardada con éxito.",
    });

    emits('save');
    form.reset();
  } catch (error) {
    console.error('Error al enviar el formulario:', error);
    $q.notify({
      type: "negative",
      message: "Error al guardar la escritura.",
    });
  }
};


onMounted(loadSubseriesAndLibros);

defineExpose({
  form // Expone la instancia del formulario
});

</script>

<style scoped>
.my-card {
  width: 100%;
  max-width: 80%;
}
.input-group {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}
.input-half {
  flex: 1 1 48%;
}
.input-third {
  flex: 1 1 30%;
}

.btn-alto {
  height: 40px;
  width: auto;
  align-self: stretch;
  padding: 0 12px;
  justify-content: center;
}


.boton-expandido {
  height: 40px;
  width: auto;
  align-self: stretch;
  padding: 0 12px;
  justify-content: center;

}
</style>
