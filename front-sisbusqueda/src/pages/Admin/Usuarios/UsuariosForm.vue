<template>
  <q-card class="my-card">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">{{ title }}</div>
    </q-card-section>

    <q-form @submit.prevent="submit">
      <q-card-section class="q-pa-md">

        <!-- DNI -->
        <q-input
          dense
          outlined
          v-model="form.dni"
          label="DNI *"
          maxlength="15"
          autocomplete="off"
          @change="form.validate('dni')"
          :error="form.invalid('dni')"
          :class="form.invalid('dni') ? 'q-mb-sm' : ''"
        >
          <template v-slot:prepend><q-icon name="badge" /></template>
          <template v-slot:error><div>{{ form.errors.dni }}</div></template>
        </q-input>

        <!-- Nombre -->
        <q-input
          dense
          outlined
          v-model="form.name"
          :loading="form.validating"
          label="Nombre *"
          autocomplete="name"
          @update:model-value="form.validate('name')"
          :error="form.invalid('name')"
          :class="form.invalid('name') ? 'q-mb-sm' : ''"
        >
          <template v-slot:prepend><q-icon name="mdi-account" /></template>
          <template v-slot:error><div>{{ form.errors.name }}</div></template>
        </q-input>

        <!-- Email -->
        <q-input
          dense
          outlined
          v-model="form.email"
          label="Email *"
          type="email"
          autocomplete="email"
          @change="form.validate('email')"
          :error="form.invalid('email')"
          :class="form.invalid('email') ? 'q-mb-sm' : ''"
        >
          <template v-slot:prepend><q-icon name="mail" /></template>
          <template v-slot:error><div>{{ form.errors.email }}</div></template>
        </q-input>

        <!-- Contraseña -->
        <q-input
          dense
          outlined
          v-model="form.password"
          :type="isPwd ? 'password' : 'text'"
          label="Contraseña *"
          autocomplete="current-password"
          @change="form.validate('password')"
          :error="form.invalid('password')"
          :class="form.invalid('password') ? 'q-mb-sm' : ''"
        >
          <template v-slot:append>
            <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="isPwd = !isPwd" />
          </template>
          <template v-slot:prepend><q-icon name="lock" /></template>
          <template v-slot:error><div>{{ form.errors.password }}</div></template>
        </q-input>

        <!-- Nivel -->
        <q-select
          dense
          outlined
          v-model="form.nivel"
          :options="nivelOptions"
          label="Nivel *"
          emit-value
          map-options
          class="q-mb-sm"
          :rules="[val => val !== null || 'Seleccione nivel']"
        >
          <template v-slot:prepend><q-icon name="mdi-account-star" /></template>
        </q-select>

        <!-- Estado -->
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
          <template v-slot:prepend><q-icon name="mdi-state-machine" /></template>
        </q-select>

        <!-- Área -->
        <SelectArea
          ref="areaSelectRef"
          class="q-mb-md"
          label="Área de Trabajo"
          :id="form.area_id || idSelectArea"
          @selectedItem="updateArea($event)"
        />

        <!-- Roles -->
        <div class="q-gutter-y-sm column">
          <q-list bordered separator>
            <template v-for="(p, i) in roles" :key="i">
              <q-item clickable v-ripple>
                <q-item-section>
                  <q-toggle
                    keep-color
                    v-model="form.rolesSelected"
                    :label="p.name"
                    color="secondary"
                    :val="p.id"
                    hide-details
                  />
                </q-item-section>
              </q-item>
            </template>
          </q-list>
        </div>

      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn label="Cancelar" flat v-close-popup />
        <q-btn label="Guardar" :loading="form.processing" type="submit" color="positive" />
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script setup>
import { useForm } from 'laravel-precognition-vue'
import { onMounted, ref } from 'vue'
import RoleService from 'src/services/RoleService'
import SelectArea from 'src/components/SelectArea.vue'

const isPwd = ref(true)
const roles = ref([])
const emits = defineEmits(['save'])
const areaSelectRef = ref()
const idSelectArea = ref(null)

const nivelOptions = [
  { label: 'Archivo Central', value: 'central' },
  { label: 'Archivo Historico', value: 'historico' },
  { label: 'Archivo Intermedio', value: 'intermedio' }
]

const estadoOptions = [
  { label: 'Activo', value: true },
  { label: 'Inactivo', value: false }
]

const props = defineProps({
  title: String,
  id: Number,
  edit: { type: Boolean, default: false }
})

let form
if (props.edit) {
  form = useForm('put', `api/usuarios/${props.id}`, {
    id: '', name: '', email: '', password: '',
    area_id: null, rolesSelected: [], dni: '', nivel: '', estado: true
  })
} else {
  form = useForm('post', 'api/usuarios', {
    id: '', name: '', email: '', password: '',
    area_id: '', rolesSelected: [], dni: '', nivel: '', estado: true
  })
}

async function cargar() {
  const { data } = await RoleService.getData({ params: { rowsPerPage: 0, order_by: 'id' } })
  roles.value = data
}

function updateArea(event) {
  if (event) {
    form.area_id = event.id
    idSelectArea.value = event.id
  } else {
    idSelectArea.value = null
    form.area_id = null
  }
}

function setValue(values) {
  values.estado = Boolean(values.estado)
  form.setData(values)
  if (values.area_id) {
    areaSelectRef.value.get(values.area_id)
    idSelectArea.value = values.area_id
  }
}

function submit() {
  if (form.password === '') {
    delete form.password
  }
  form.submit()
    .then(() => {
      form.reset()
      emits('save')
    })
    .catch(() => {})
}

onMounted(() => {
  cargar()
})

defineExpose({ setValue, form })
</script>

<style lang="sass" scoped>
p
  font-size: 12px
  line-height: 1

.my-card
  width: 100%
  max-width: 80vw
</style>
