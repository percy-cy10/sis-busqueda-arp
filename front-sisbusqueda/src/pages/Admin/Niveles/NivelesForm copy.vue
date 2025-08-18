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
          :class="form.invalid('nombre') ? 'q-mb-sm' : ''"
        >
          <template v-slot:prepend>
            <q-icon name="mdi-file-tree" />
          </template>
          <template v-slot:error>
            <div>
              {{ form.errors.nombre }}
            </div>
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
import { onMounted, ref } from "vue";
const emits = defineEmits(["save"]);
const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false },
});

let form;
if (props.edit) {
  form = useForm("put", "api/niveles/" + props.id, {
    id: "",
    nombre: "",
  });
} else {
  form = useForm("post", "api/niveles", {
    id: "",
    nombre: "",
  });
}

function setValue(values) {
  form.value = { ...values };
}

const submit = () => {
  form
    .submit()
    .then(() => {
      form.reset();
      emits("save");
    })
    .catch(() => {});
};

onMounted(() => {
  // Puedes cargar datos adicionales aquí si lo necesitas
});

defineExpose({
  form,
  setValue,
});
</script>
<style scoped>
.my-card {
  width: 100%;
  max-width: 80vw;
}
</style>
