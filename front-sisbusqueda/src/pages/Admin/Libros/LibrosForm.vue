<template>
  <q-card class="my-card">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">{{ title }}</div>
    </q-card-section>

    <q-form @submit.prevent="submit" class="q-gutter-md">
      <q-card-section class="q-pa-md">
        <!-- Campo Protocolo con prefijo fijo -->
        <q-input
          dense
          outlined
          v-model="protocoloCompleto"
          label="Protocolo *"
          class="q-mb-sm"
          mask="P-######"
          @update:model-value="handleProtocoloInput"
          :rules="[val => !!val || 'Campo requerido']"
        >
          <template v-slot:prepend>
            <q-icon name="mdi-text-box" />
          </template>
        </q-input>

        <!-- Selector de Notario con placeholder -->
        <q-select
          dense
          outlined
          v-model="form.notario_id"
          :options="notarios"
          option-value="id"
          option-label="nombre_completo"
          label="Notario *"
          emit-value
          map-options
          class="q-mb-sm"
          :rules="[val => !!val || 'Seleccione un notario']"
          placeholder="Seleccione un notario"
        >
          <template v-slot:prepend>
            <q-icon name="mdi-account-tie" />
          </template>
        </q-select>

        <!-- Campo Estado -->
        <q-select
          dense
          outlined
          v-model="form.estado"
          :options="estadoOptions"
          label="Estado *"
          emit-value
          map-options
          class="q-mb-sm"
          :rules="[val => val !== null || 'Seleccione estado']"
        >
          <template v-slot:prepend>
            <q-icon name="mdi-state-machine" />
          </template>
        </q-select>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right" class="q-pa-md">
        <q-btn label="Cancelar" flat v-close-popup class="q-mr-sm"/>
        <q-btn
          label="Guardar"
          :loading="form.processing"
          type="submit"
          color="positive"
        />
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script setup>
import { useForm } from "laravel-precognition-vue";
import { ref, onMounted, computed } from "vue";
import NotarioService from "src/services/NotarioService";
import LibroService from "src/services/LibroService";

const emits = defineEmits(["save"]);
const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false },
});

// Configuración del prefijo del protocolo
const protocoloPrefix = "P-";
const protocoloValue = ref("");

const protocoloCompleto = computed({
  get: () => protocoloPrefix + protocoloValue.value,
  set: (newValue) => {
    const cleanValue = newValue.replace(protocoloPrefix, '');
    protocoloValue.value = cleanValue.replace(/[^0-9]/g, '');
  }
});

const handleProtocoloInput = (value) => {
  if (!value.startsWith(protocoloPrefix)) {
    protocoloValue.value = value.replace(protocoloPrefix, '');
  }
};

// Configuración de opciones
const estadoOptions = [
  { label: "Activo", value: true },
  { label: "Inactivo", value: false },
];

const notarios = ref([]);

// Carga inicial de datos
onMounted(async () => {
  try {
    const response = await NotarioService.getData({ params: {} });
    notarios.value = response.data;

    if (props.edit) {
      const libro = await LibroService.get(props.id);
      protocoloValue.value = libro.protocolo?.replace(protocoloPrefix, '') || '';
      form.notario_id = Number(libro.notario_id);
      form.estado = Boolean(libro.estado);
    }
  } catch (error) {
    console.error("Error loading data:", error);
  }
});

// Configuración del formulario
let form;
if (props.edit) {
  form = useForm("put", `api/libros/${props.id}`, {
    protocolo: protocoloCompleto.value,
    notario_id: null,
    estado: true,
  });
} else {
  form = useForm("post", "api/libros", {
    protocolo: protocoloCompleto.value,
    notario_id: null,
    estado: true,
  });
}

const submit = () => {
  form.protocolo = protocoloCompleto.value;
  form.estado = Boolean(form.estado);

  form.submit().then(() => {
    emits("save");
  }).catch(error => {
    console.error("Submission error:", error);
  });
};
</script>

<style scoped>
.my-card {
  width: 100%;
  max-width: 80vw;
}


</style>
