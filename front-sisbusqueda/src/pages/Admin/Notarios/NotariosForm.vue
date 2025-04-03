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
            v-model="form.nombres"
            :loading="form.validating"
            label="Nombres *"
            @change="form.validate('nombres')"
            :error="form.invalid('nombres')"
            :class="form.invalid('nombres') ? 'q-mb-sm' : ''"
            ><template v-slot:prepend>
              <q-icon name="mdi-key" />
            </template>
            <template v-slot:error>
              <div>
                {{ form.errors.nombres }}
              </div>
            </template>
          </q-input>
          <q-input
            dense
            outlined
            v-model="form.apellido_paterno"
            :loading="form.validating"
            label="Apellido Paterno *"
            @change="form.validate('apellido_paterno')"
            :error="form.invalid('apellido_paterno')"
            :class="form.invalid('apellido_paterno') ? 'q-mb-sm' : ''"
            ><template v-slot:prepend>
              <q-icon name="mdi-key" />
            </template>
            <template v-slot:error>
              <div>
                {{ form.errors.apellido_paterno }}
              </div>
            </template>
          </q-input>
          <q-input
            dense
            outlined
            v-model="form.apellido_materno"

            label="Apellido Materno *"

            :error="form.invalid('apellido_materno')"

            >

            <template v-slot:prepend>
              <q-icon name="mdi-key" />
            </template>



            <!-- reemplazado

            <q-input
            dense
            outlined
            v-model="form.apellido_materno"
            :loading="form.validating"
            label="Apellido Materno *"
            @change="form.validate('apellido_materno')"
            :error="form.invalid('apellido_materno')"
            :class="form.invalid('apellido_materno') ? 'q-mb-sm' : ''"
            >

            <template v-slot:prepend>
              <q-icon name="mdi-key" />
            </template>

            <template v-slot:error>
              <div>
                {{ form.errors.apellido_materno }}
              </div>
            </template> -->

          </q-input>
          <q-input
            dense
            outlined
            v-model="form.año_inicio"
            :loading="form.validating"
            label="Fecha Inicial *"
            @change="form.validate('año_inicio')"
            :error="form.invalid('año_inicio')"
            :class="form.invalid('año_inicio') ? 'q-mb-sm' : ''"
            ><template v-slot:prepend>
              <q-icon name="mdi-key" />
            </template>
            <template v-slot:error>
              <div>
                {{ form.errors.año_inicio }}
              </div>
            </template>
          </q-input>
          <q-input
            dense
            outlined
            v-model="form.año_final"
            :loading="form.validating"
            label="Fecha Final *"
            @change="form.validate('año_final')"
            :error="form.invalid('año_final')"
            :class="form.invalid('año_final') ? 'q-mb-sm' : ''"
            ><template v-slot:prepend>
              <q-icon name="mdi-key" />
            </template>
            <template v-slot:error>
              <div>
                {{ form.errors.año_final }}
              </div>
            </template>
          </q-input>
          <!-- <q-input
            dense
            outlined
            v-model="form.ubigeo_cod"
            :loading="form.validating"
            label="Ubigeo *"
            @change="form.validate('ubigeo_cod')"
            :error="form.invalid('ubigeo_cod')"
            :class="form.invalid('ubigeo_cod') ? 'q-mb-sm' : ''"
            ><template v-slot:prepend>
              <q-icon name="mdi-key" />
            </template>
            <template v-slot:error>
              <div>
                {{ form.errors.ubigeo_cod }}
              </div>
            </template>
          </q-input> -->
          <div class="row justify-between">
            <SelectUbigeo ref="ubigeoSelectRef" :ubigeo_cod="form.ubigeo_cod" @selectedItem="updateUbigeo($event)">
            </SelectUbigeo>
        </div>
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
  import SelectUbigeo from "src/components/SelectUbigeo.vue";
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
    form = useForm("put", "api/notarios/" + props.id, {
      id: "",
      nombres: "",
      apellido_paterno: "",
      apellido_materno: "",
      año_inicio: "",
      año_final: "",
      nombre_completo: "",
      ubigeo_cod: null,

    });
  } else {
    form = useForm("post", "api/notarios", {
      id: "",
      nombres: "",
      apellido_paterno: "",
      apellido_materno: "",
      año_inicio: "",
      año_final: "",
      nombre_completo: "",
      ubigeo_cod: null,
    });
  }
  const ubigeoSelectRef = ref("");

  function setValue(values) {
      form.value = values;
      ubigeoSelectRef.value.getUbigeo(form.value.ubigeo_cod);
  }

  function updateUbigeo(event) {
      form.ubigeo_cod = event;

      console.log(event);
    }

  // async function cargar() {
  //   const { data } = await RoleService.getData({
  //     params: { rowsPerPage: 0, order_by: "id" },
  //   });
  //   roles.value = data;
  //   console.log(roles.value);
  // }

  const submit = () => {
    if (form.nombre_completo === "") {
        form.nombre_completo =
            form.nombres + " " + form.apellido_paterno + " " + (form.apellido_materno || "");
    }

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
    setValue
  });
  </script>
  <style scoped>
  .my-card{
    width: 100%;
    max-width: 80vw;
    }
  </style>
