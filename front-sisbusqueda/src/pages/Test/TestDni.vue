<template>
    <h1>face</h1>
    <q-form @submit="onSubmitDni" @reset="onResetDni" class="q-gutter-md">
      <q-input
        outlined
        dense
        v-model="formDni.dni"
        label="DNI"
        hint="dni"
        lazy-rules
        :rules="[(val) => (val && val.length > 0) || 'Please type something']"
      />
      <div>
        <q-btn label="Submit" type="submit" color="primary" />
        <q-btn label="Reset" type="reset" color="primary" flat class="q-ml-sm" />
      </div>
    </q-form>
    <q-form
      v-if="formDni.encontrado"
      @submit="onSubmit"
      @reset="onReset"
      class="q-gutter-md"
    >
      <q-input
        outlined
        dense
        v-model="formPersona.nombre"
        label="Nombres *"
        hint="Nombres"
        lazy-rules
        :rules="[(val) => (val && val.length > 0) || 'Please type something']"
      />
      <q-input
        outlined
        dense
        v-model="formPersona.a_paterno"
        label="Apellido Paterno *"
        hint="Apellido Paterno"
        lazy-rules
        :rules="[(val) => (val && val.length > 0) || 'Please type something']"
      />
      <q-input
        outlined
        dense
        v-model="formPersona.a_materno"
        label="Apellido Materno *"
        hint="Apellido Materno"
        lazy-rules
        :rules="[(val) => (val && val.length > 0) || 'Please type something']"
      />
      <q-input
        outlined
        dense
        v-model="formPersona.correo"
        label="Correo *"
        hint="Correo"
        lazy-rules
        :rules="[(val) => (val && val.length > 0) || 'Please type something']"
      />
      <q-input
        outlined
        dense
        v-model="formPersona.numero"
        label="Numero *"
        hint="Numero"
        lazy-rules
        :rules="[(val) => (val && val.length > 0) || 'Please type something']"
      />
  
      <div>
        <q-btn label="Submit" type="submit" color="primary" />
        <q-btn label="Reset" type="reset" color="primary" flat class="q-ml-sm" />
      </div>
    </q-form>
  </template>
  
  <script setup>
  import { onMounted, ref } from "vue";
  import DniService from "src/services/DniService";
  const dni = ref(null);
  const formDni = ref({
    dni: null,
    encontrado: false,
  });
  const formPersona = ref({
    nombre: null,
    a_paterno: null,
    a_materno: null,
    correo: null,
    numero: null,
  });
  
  const onSubmitDni = async () => {
    // const res = await DniService.getSolicitanteDni(formDni.value.dni);
    // console.log(res);
  
    try {
      const res = await DniService.getSolicitanteDni(formDni.value.dni);
      if (res.message) {
        console.log(res.message);
        // console.log("object");
      } else {
        console.log(res);
        formDni.value.encontrado = true;
        formPersona.value.nombre = res.nombres;
        formPersona.value.a_paterno = res.apellidoPaterno;
        formPersona.value.a_materno = res.apellidoMaterno;
      }
    } catch (error) {
      formDni.value.encontrado = false;
    }
  };
  
  const onResetDni = () => {
    reset();
  };
  const onSubmit = async () => {};
  
  const onReset = () => {};
  </script>
  