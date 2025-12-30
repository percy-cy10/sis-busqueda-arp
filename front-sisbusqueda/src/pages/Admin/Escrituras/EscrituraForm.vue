  <template>
    <q-card class="my-card">
      <!-- Título + Botón Cerrar -->
      <q-card-section class="row items-center justify-between bg-primary text-white">
        <div class="text-h6">{{ title }}</div>
        <q-btn icon="close" color="negative" round v-close-popup />
      </q-card-section>

      <q-form @submit.prevent="submit">
        <q-card-section class="q-pa-md">

          <!-- ================= Sección 1 ================= -->
          <q-form ref="formStep1">
            <div v-if="step === 1">
              <!-- Fila 1:  Bien – Código de Escritura  | Subserie – Libro-->
              <q-card flat bordered class="q-pa-md q-mb-md">
                <div class="row q-col-gutter-md">
                  <div class="col-6">
                    <q-badge class="q-mb-xs text-subtitle2"> Código de Escritura y Nombre del Bien </q-badge>
                    <div class="row input-group">
                      <q-input dense outlined v-model="form.cod_escritura" label="Número de Escritura" mask="E-#######" :error="!!form.errors.cod_escritura" class="col" :rules="[val => !!val || 'Campo obligatorio']"/>
                      <q-input dense outlined v-model="form.bien" label="Bien *" :error="!!form.errors.bien" class="col" :rules="[val => !!val || 'Campo obligatorio']"/>
                      </div>
                  </div>

                  <!-- Subserie – Libro | Folios -->

                  <div class="col-6">
                    <q-badge class="q-mb-xs text-subtitle2">Subserie y Libro</q-badge>
                    <div class="row q-gutter-sm">
                      <!-- <q-select class="col" v-model="form.subserie_id" :options="subseries" label="Subserie *" :error="!!form.errors.subserie_id" option-label="nombre" option-value="id" dense outlined emit-value map-options :rules="[val => !!val || 'Campo obligatorio']" /> -->
                      <q-select
                        class="col"
                        v-model="form.subserie_id"
                        :options="subseries"
                        label="Subserie *"
                        option-label="nombre"
                        option-value="id"
                        dense outlined emit-value map-options
                        :rules="[val => !!val || 'Campo obligatorio']"
                      >
                        <template v-slot:before-options>
                          <q-item>
                            <q-input
                              dense
                              outlined
                              v-model="filtroSubserie"
                              label="Buscar subserie..."
                              @update:model-value="filtrarSubseriesLocal"
                              clearable
                              autofocus
                              class="full-width"
                            />
                          </q-item>
                          <q-separator />
                        </template>
                      </q-select>


                        <q-dialog v-model="dialogSubserie" persistent>
                          <SubSeriesForm :title="'Nueva Subserie'" @save="handleSaveSubserie" />
                        </q-dialog>
                      <q-btn class="btn-alto" dense icon="person_add" color="primary" @click="dialogSubserie = true" size="md" />
                      <q-select class="col" v-model="form.libro_id" :options="libros" label="Libro *" :error="!!form.errors.libro_id" option-label="protocolo" option-value="id" dense outlined emit-value map-options :rules="[val => !!val || 'Campo obligatorio']"/>
                    </div>
                  </div>
                </div>
              </q-card>



              <!--Fila 2: Otorgante/Favorecido -->
              <q-card flat bordered class="q-pa-md q-mb-md">
                <q-badge class="q-mb-sm text-subtitle2">Datos de Otorgante y Favorecido</q-badge>
                <div class="row q-col-gutter-md">
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
                    :rules="[val => val && val.length > 0 || 'Seleccione al menos un otorgante']"
                  >

                    <template v-slot:before-options>
                      <q-item>
                        <q-input
                          dense
                          outlined
                          v-model="filtroOtorgante"
                          label="Buscar otorgante..."
                          @update:model-value="filtrarOtorgantesLocal"
                          clearable
                          autofocus
                          class="full-width"
                        />

                      </q-item>
                      <q-separator />
                    </template>
                  </q-select>

                      <!-- <q-select class="col" v-model="form.otorgantes" :options="otorgantesOptions" label="Otorgantes *" option-label="nombre_completo" option-value="id" dense outlined emit-value map-options multiple use-chips @filter="buscarOtorgantes" :rules="[val => val && val.length > 0 || 'Seleccione al menos un otorgante']"/> -->
                      <q-btn class="btn-alto" dense icon="person_add" color="primary" @click="dialogOtorgante = true" size="md"/>
                    </div>
                  </div>

                  <q-dialog v-model="dialogOtorgante" persistent>
                    <OtorganteForm :title="'Nuevo Otorgante'" @save="handleSaveOtorgante" />
                  </q-dialog>

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
                        :rules="[val => val && val.length > 0 || 'Seleccione al menos un favorecido']"
                      >
                        <!-- Input de búsqueda al inicio -->
                        <template v-slot:before-options>
                          <q-item>
                            <q-input
                              dense
                              outlined
                              v-model="filtroFavorecido"
                              label="Buscar favorecido..."
                              @update:model-value="filtrarFavorecidosLocal"
                              clearable
                              autofocus
                              class="full-width"
                            />
                          </q-item>
                          <q-separator />
                        </template>

                      </q-select>

                      <!-- <q-select class="col" v-model="form.favorecidos" :options="favorecidosOptions" label="Favorecidos *" option-label="nombre_completo" option-value="id" dense outlined emit-value map-options multiple use-chips @filter="buscarFavorecidos" :rules="[val => val && val.length > 0 || 'Seleccione al menos un favorecido']"/> -->
                      <q-btn class="btn-alto" dense icon="person_add" color="primary" @click="dialogFavorecido = true" size="md"/>
                    </div>
                  </div>
                  <q-dialog v-model="dialogFavorecido" persistent>
                    <FavorecidoForm :title="'Nuevo Favorecido'" @save="handleSaveFavorecido" />
                  </q-dialog>


                </div>
              </q-card>
            </div>
          </q-form>

          <!-- ================= Sección 2 ================= -->
          <q-form ref="formStep2">
            <div v-if="step === 2">
              <!-- Otorgante/Favorecido -->
              <q-card flat bordered class="q-pa-md q-mb-md">
                <q-badge class="q-mb-sm text-subtitle2">Datos de Fecha y Folios</q-badge>
                <div class="row q-col-gutter-md">


                  <div class="col-6">
                    <q-badge class="q-mb-xs text-subtitle2">Fecha</q-badge>
                    <div class="row input-group">
                      <InputAnio :requerido="true" class="col" dense outlined v-model="form.anio" :error="!!form.errors.anio" :rules="[val => !!val || 'Campo obligatorio']"/>
                      <InputMes :readonly="!form.anio" class="col" dense outlined v-model="form.mes" :modelAnio="form.anio" :error="!!form.errors.mes"/>
                      <InputDia :readonly="!form.anio || !form.mes" class="col" dense outlined v-model="form.dia" v-model:modelAnio="form.anio" v-model:modelMes="form.mes" :error="!!form.errors.dia" />
                    </div>
                  </div>
                  <div class="col-6">
                    <q-badge class="q-mb-xs text-subtitle2">Folios</q-badge>
                    <div class="row input-group">
                      <q-input class="col" dense outlined v-model="form.cod_folioInicial" label="Folio Inicial *" mask="F-######" :loading="form.validating" @change="form.validate('cod_folioInicial')" :error="form.invalid('cod_folioInicial')" :rules="[val => !!val || 'Campo obligatorio']">
                        <template v-slot:append>
                          <q-toggle v-model="form.vueltaFI" size="xm" dense checked-icon="check" color="blue" unchecked-icon="clear" :disable="deshabili_FI" />
                          <div class="text-caption">Vuelta</div>
                        </template>
                      </q-input>
                      <q-input class="col" dense outlined v-model="form.cod_folioFinal" label="Folio Final *" mask="F-######" :loading="form.validating" @change="form.validate('cod_folioFinal')" :error="form.invalid('cod_folioFinal')" :rules="[val => !!val || 'Campo obligatorio']">
                        <template v-slot:append>
                          <q-toggle v-model="form.vueltaFF" size="xm" dense checked-icon="check" color="blue" unchecked-icon="clear" :disable="deshabili_FF" />
                          <div class="text-caption">Vuelta</div>
                        </template>
                      </q-input>
                    </div>
                  </div>
                </div>
              </q-card>

              <!-- Archivo -->
              <q-card flat bordered class="q-pa-md q-mb-md">
                <div class="row q-col-gutter-md">
                  <div class="col-6">
                    <q-badge class="q-mb-xs text-subtitle2">Archivo (PDF)</q-badge>
                    <div>
                      <div v-if="file || (edit && form.file_name)">
                        <q-chip removable color="primary" text-color="white" icon="attach_file" @remove="removerArchivo">
                          {{ file ? file.name : obtenerNombreArchivo(form.file_name) }}
                        </q-chip>
                      </div>
                      <div v-else>
                        <input type="file" @change="handleFileUpload" accept=".pdf,image/*" class="q-mb-sm"/>
                      </div>
                    </div>
                  </div>
                  <div class="col-6 flex items-center">
                    <q-badge class="q-mb-xs text-subtitle2">Acciones</q-badge>
                    <div>
                      <q-btn round dense flat icon="visibility" @click="verArchivo" v-if="edit && form.file_name && !file" class="q-ml-sm"/>
                    </div>
                  </div>
                </div>
              </q-card>

              <!-- Observaciones -->
              <q-card flat bordered class="q-pa-md q-mb-md">
                <q-badge class="q-mb-sm text-subtitle2">Observaciones</q-badge>
                <q-input dense outlined v-model="form.observaciones" label="Observaciones" type="textarea" class="q-mb-s" />
              </q-card>
            </div>
          </q-form>

        </q-card-section>

        <q-card-section>
          <q-card-actions align="between">
            <div>
              <q-btn
                v-if="step > 1"
                label="Regresar"
                color="secondary"
                @click="step--"
              />
            </div>

            <q-btn
              v-if="step < 2"
              label="Siguiente"
              color="primary"
              @click="async () => { if (await validarStep()) step++ }"
            />

            <q-btn
              v-else
              label="Enviar (Guardar)"
              :loading="form.processing"
              type="submit"
              color="positive"
              @click="async (e) => { e.preventDefault(); if (await validarStep()) submit() }"
            />
          </q-card-actions>
        </q-card-section>

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
  const step = ref(1) // controla secciones

  const file = ref(null);

  const dialogOtorgante = ref(false);
  const dialogFavorecido = ref(false);
  const dialogSubserie = ref(false);

  const formStep1 = ref(null)
  const formStep2 = ref(null)

  const filtroOtorgante = ref("")
  const filtroFavorecido = ref("")
  const otorgantesTodos = ref([])  // lista completa
  const favorecidosTodos = ref([])   // 👈 esta línea faltaba

const subseriesTodos = ref([])
const subseries = ref([])
const filtroSubserie = ref("")

const filtrarSubseriesLocal = () => {
  if (!filtroSubserie.value) {
    subseries.value = [...subseriesTodos.value]
  } else {
    const filtrados = subseriesTodos.value.filter(s =>
      s.nombre.toLowerCase().includes(filtroSubserie.value.toLowerCase())
    )

    subseries.value = filtrados.length
      ? filtrados
      : [{ id: 0, nombre: '--- Sin coincidencias ---', disabled: true }]
  }
}



  const actualizarListados = async () => {
    try {
      const [otorgantesRes, favorecidosRes] = await Promise.all([
        OtorganteService.getAll(),
        FavorecidoService.getAll()
      ]);

      otorgantesTodos.value = otorgantesRes.data || [];
      otorgantesOptions.value = [...otorgantesTodos.value];

      favorecidosTodos.value = favorecidosRes.data || [];
      favorecidosOptions.value = [...favorecidosTodos.value];
    } catch (error) {
      console.error("Error actualizando listados:", error);
      $q.notify({ type: 'negative', message: 'Error actualizando registros' });
    }
  };


const filtrarOtorgantesLocal = () => {
  if (!filtroOtorgante.value) {
    // restaurar lista completa
    otorgantesOptions.value = [...otorgantesTodos.value];
  } else {
    // filtrar
    const filtrados = otorgantesTodos.value.filter(o =>
      o.nombre_completo.toLowerCase().includes(filtroOtorgante.value.toLowerCase())
    );

    otorgantesOptions.value = filtrados.length
      ? filtrados
      : [{ id: 0, nombre_completo: '--- Sin coincidencias ---', disabled: true }];
  }
};

const filtrarFavorecidosLocal = () => {
  if (!filtroFavorecido.value) {
    favorecidosOptions.value = [...favorecidosTodos.value];
  } else {
    const filtrados = favorecidosTodos.value.filter(f =>
      f.nombre_completo.toLowerCase().includes(filtroFavorecido.value.toLowerCase())
    );

    favorecidosOptions.value = filtrados.length
      ? filtrados
      : [{ id: 0, nombre_completo: '--- Sin coincidencias ---', disabled: true }];
  }
};




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

  const validarStep = async () => {
    if (step.value === 1) {
      return await formStep1.value.validate()
    }
    if (step.value === 2) {
      return await formStep2.value.validate()
    }
    return true
  }

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





  // Funciones de búsqueda mejoradas
  const buscarOtorgantes = async (val) => {
    if (!val) {
      otorgantesOptions.value = [...otorgantesTodos.value];
      return;
    }

    try {
      const resultados = await OtorganteService.buscar(val);
      otorgantesOptions.value = resultados.data;
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

  // const subseries = ref([]);
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
        SubSerieService.getAll(),
        LibroService.getData()
      ]);

      // subseries.value = subseriesRes.data?.data || subseriesRes.data || [];
      subseriesTodos.value = subseriesRes.data?.data || subseriesRes.data || []
      subseries.value = [...subseriesTodos.value] // inicial

      // subseriesTodos.value = subseriesRes || []
      // subseries.value = [...subseriesTodos.value]


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
