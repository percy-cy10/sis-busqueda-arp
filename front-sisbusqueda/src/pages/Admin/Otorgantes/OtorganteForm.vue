<template>
  <q-card class="my-card" style="width: 25%; max-width: 900px">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">{{ title }}</div>
    </q-card-section>

    <q-form @submit.prevent="submit">
      <q-card-section class="q-pa-md">
        <q-select
          dense
          outlined
          v-model="form.tipo"
          :options="['natural', 'juridica']"
          label="Tipo Persona *"
          :rules="[val => !!val || 'Seleccione un tipo']"
        />

        <template v-if="form.tipo === 'natural'">
          <q-input
            dense
            outlined
            v-model="form.nombre"
            label="Nombre *"
            :rules="[val => !!val || 'Campo requerido']"
          />
          <q-input
            dense
            outlined
            v-model="form.apellido_paterno"
            label="Apellido Paterno *"
            :rules="[val => !!val || 'Campo requerido']"
          />
          <q-input
            dense
            outlined
            v-model="form.apellido_materno"
            label="Apellido Materno *"
            :rules="[val => !!val || 'Campo requerido']"
          />
        </template>

        <template v-else-if="form.tipo === 'juridica'">
          <q-input
            dense
            outlined
            v-model="form.razon_social"
            label="Razón Social *"
            :rules="[val => !!val || 'Campo requerido']"
          />
        </template>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn label="Cancelar" flat v-close-popup />
        <q-btn
          label="Guardar"
          type="submit"
          color="positive"
          :loading="form.processing"
        />
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script setup>
import { useForm } from "laravel-precognition-vue";
import { onMounted, nextTick, ref } from "vue";
import { useQuasar } from "quasar";
import { useUserStore } from "src/stores/user-store";
import OtorganteService from "src/services/OtorganteService";

const $q = useQuasar();
const userStore = useUserStore();

const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false },
});

const emits = defineEmits(["save"]);

let form;
if (props.edit && props.id) {
  form = useForm("put", `/api/otorgantes/${props.id}`, {
    tipo: "natural",
    nombre: "",
    apellido_paterno: "",
    apellido_materno: "",
    razon_social: "",
    nombre_completo: "",
    user_id: userStore.getId,
  });
} else {
  form = useForm("post", "/api/otorgantes", {
    tipo: "natural",
    nombre: "",
    apellido_paterno: "",
    apellido_materno: "",
    razon_social: "",
    nombre_completo: "",
    user_id: userStore.getId,
  });
}

const loadData = async (id) => {
  try {
    const data = await OtorganteService.get(id);
    form.setData(data);
  } catch (error) {
    $q.notify({ type: "negative", message: "Error al cargar datos" });
  }
};

const submit = () => {
  if (form.tipo === "natural") {
    form.nombre_completo = `${form.nombre} ${form.apellido_paterno} ${form.apellido_materno}`.trim();
    form.razon_social = null;
  } else {
    form.nombre_completo = form.razon_social;
    form.nombre = null;
    form.apellido_paterno = null;
    form.apellido_materno = null;
  }

  form.submit()
    .then(() => {
      $q.notify({
        type: "positive",
        message: "Guardado exitoso!",
        position: "top-right",
        progress: true,
        timeout: 1000
      });
      emits("save");
    })
    .catch((error) => {
      $q.notify({
        type: "negative",
        message: "Error al guardar",
        position: "top-right",
        progress: true,
        timeout: 1000
      });
    });
};

defineExpose({ loadData });
</script>
