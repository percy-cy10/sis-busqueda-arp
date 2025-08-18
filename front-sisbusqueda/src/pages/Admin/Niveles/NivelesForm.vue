<template>
  <q-card class="my-card" style="max-width: 800px">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">{{ title }}</div>
    </q-card-section>
    <q-form @submit.prevent="submit">
      <q-card-section class="q-pa-md">
        <q-input
          dense
          outlined
          v-model="form.nombre"
          :loading="form.validating"
          label="Nombre *"
          @change="form.validate('nombre')"
          :error="form.invalid('nombre')"
          :error-message="form.errors.nombre"
          class="q-mb-md"
        >
          <template v-slot:prepend>
            <q-icon name="mdi-file-tree" />
          </template>
        </q-input>
      </q-card-section>
      <q-separator />
      <q-card-actions align="right">
        <q-btn label="Cancelar" flat v-close-popup />
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
import { onMounted, ref, watch } from "vue";

const emits = defineEmits(["save"]);
const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false },
});

const form = ref(null);

// Initialize form based on edit mode
watch(() => props.edit, (newValue) => {
  form.value = useForm(newValue ? 'put' : 'post',
    newValue ? `api/niveles/${props.id}` : 'api/niveles',
    {
      nombre: ''
    }
  );
}, { immediate: true });

function setValue(values) {
  if (form.value) {
    form.value.nombre = values.nombre;
  }
}

const submit = async () => {
  try {
    await form.value.submit();
    emits("save");
  } catch (error) {
    console.error("Error submitting form:", error);
  }
};

defineExpose({
  form,
  setValue,
});
</script>
