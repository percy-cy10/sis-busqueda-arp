<template>
    <!-- content -->
    <q-card class="my-card">
      <q-card-section class="bg-primary text-white">
        <div class="text-h6">{{ title }}</div>
        <!-- <div class="text-subtitle2">Usuario</div> -->
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
            ><template v-slot:prepend>
              <q-icon name="mdi-key" />
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
          <q-btn label="Cancelar" flat v-close-popup></q-btn>
          <q-btn
            label="Guardar"
            :loading="form.processing"
            type="submit"
            color="positive"
          ></q-btn>
        </q-card-actions>
      </q-form>
    </q-card>
  </template>
  
  <script setup>
  import { useForm } from "laravel-precognition-vue";
  import { onMounted, ref } from "vue";
  // import RoleService from "src/services/RoleService"
  // const isPwd = ref(true);
  // const roles = ref(false);
  const emits = defineEmits(["save"]);
  const props = defineProps({
    title: String,
    id: Number,
    edit: {
      type: Boolean,
      default: false,
    },
  });
  
  let form;
  if (props.edit) {
    form = useForm("put", "api/subseries/" + props.id, {
      id: "",
      nombre: "",
  
    });
  } else {
    form = useForm("post", "api/subseries", {
      id: "",
      nombre: "",
    });
  }
  // async function cargar() {
  //   const { data } = await RoleService.getData({
  //     params: { rowsPerPage: 0, order_by: "id" },
  //   });
  //   roles.value = data;
  //   console.log(roles.value);
  // }
  
  const submit = () => {
    form
      .submit()
      .then((response) => {
        form.reset();
        // form.setData()
  
        emits("save");
      })
      .catch((error) => {
        // alert("An error occurred.");
      });
  };
  
  onMounted(() => {
    // setData();
    console.log(props.edit);
    // cargar();
    // console.log(form);
  });
  
  defineExpose({
    // setData,
    form,
  });
  </script>
  <style scoped>
  .my-card{
    width: 100%;
    max-width: 80vw;
  }
  </style>
  