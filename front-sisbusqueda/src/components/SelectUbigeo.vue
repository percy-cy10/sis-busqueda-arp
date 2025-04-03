<template>
    <div class="col-12 col-sm-4 q-my-md">
        <q-select @update:model-value="updateDepartamento($event)" v-model="modelDepartamento" borderless dense
            debounce="300" outlined use-input hide-selected fill-input input-debounce="0" label="Departamento"
            :options="optionsDepartamentos" option-label="nombre" option-value="id" @filter="filterDepartamentos"
            :disable="disabled">
            <template v-slot:no-option>
                <q-item>
                    <q-item-section class="text-grey">
                        No se han encontrado resultados
                    </q-item-section>
                </q-item>
            </template>
        </q-select>
    </div>
    <div class="col-12 col-sm-4 q-my-md">
        <q-select @update:model-value="updateProvincia($event)" v-model="modelProvincia" borderless dense debounce="300"
            outlined use-input hide-selected fill-input input-debounce="0" label="Provincia" :options="optionsProvincias"
            option-label="nombre" option-value="id" @filter="filterProvincias" :disable="disabled">
            <template v-slot:no-option>
                <q-item>
                    <q-item-section class="text-grey">
                        No se han encontrado resultados
                    </q-item-section>
                </q-item>
            </template>
        </q-select>
    </div>
    <div class="col-12 col-sm-4 q-my-md">
        <q-select @update:model-value="updateDistrito($event)" v-model="modelDistrito" borderless dense debounce="300"
            outlined use-input hide-selected fill-input input-debounce="0" label="Distrito" :options="optionsDistritos"
            option-label="nombre" option-value="id" @filter="filterDistritos" :disable="disabled">
            <template v-slot:no-option>
                <q-item>
                    <q-item-section class="text-grey">
                        No se han encontrado resultados
                    </q-item-section>
                </q-item>
            </template>
        </q-select>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import UbigeoService from 'src/services/UbigeoService';

//Emits
const emits = defineEmits(["selectedItem"]);

//Props
const props = defineProps({
    ubigeo_cod: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    obra: { default: null },
});

//Estados reactivos
const stringDepartamentos = ref([]);
const optionsDepartamentos = ref([]);
const modelDepartamento = ref({
    "id": 2090,
    "codigo": 210000,
    "tipo": "departamento",
    "cod_dep": "21",
    "cod_prov": "00",
    "cod_dist": "00",
    "nombre": "Puno",
    "created_at": null,
    "updated_at": null
});

const stringProvincias = ref([]);
const optionsProvincias = ref([]);
const modelProvincia = ref(null);

const stringDistritos = ref([]);
const optionsDistritos = ref([]);
const modelDistrito = ref(null);

//OnMounted
onMounted(async () => {
    if (props.ubigeo_cod != null) {
        await getUbigeo(props.ubigeo_cod);
    }
    if (props.obra != null) {
        setUbigeo();
    }else{
        await getDepartamentos();
    }
})

//Métodos
function setUbigeo() {
    modelDepartamento.value = props.obra.departamento;
    modelProvincia.value = props.obra.provincia;
    modelDistrito.value = props.obra.ubigeo;
}
async function getUbigeo(ubigeo_cod) {
    await UbigeoService.getUbigeo({ params: { ubigeo_cod: ubigeo_cod } }).then(
        (response) => {
            modelDepartamento.value = response.departamento;
            modelProvincia.value = response.provincia;
            modelDistrito.value = response.distrito;
        }
    );
    getProvincias(modelDepartamento.value.cod_dep);
    getDistritos(modelDepartamento.value.cod_dep, modelProvincia.value.cod_prov);
}

async function getDepartamentos() {
    modelDepartamento.value = {
        "id": 2090,
        "codigo": 210000,
        "tipo": "departamento",
        "cod_dep": "21",
        "cod_prov": "00",
        "cod_dist": "00",
        "nombre": "Puno",
    };
    modelProvincia.value = null;
    modelDistrito.value = null;
    getProvincias(modelDepartamento.value.cod_dep);
    stringDepartamentos.value = await UbigeoService.getDepartamentos();
};

async function getProvincias(cod_dep) {
    stringProvincias.value = await UbigeoService.getProvincias(cod_dep);
};

async function getDistritos(cod_dep, cod_prov) {
    stringDistritos.value = await UbigeoService.getDistritos(cod_dep, cod_prov);
};

async function filterDepartamentos(val, update) {
    setTimeout(() => {
        update(async () => {
            if (val === '') {
                optionsDepartamentos.value = stringDepartamentos.value;
            }
            else {
                const needle = val.toLowerCase()
                optionsDepartamentos.value = stringDepartamentos.value.filter((x) => x.nombre.toLowerCase().search(needle) != -1);
            }
        })
    }, 500)
};

async function filterProvincias(val, update) {
    setTimeout(() => {
        update(async () => {
            if (val === '') {
                optionsProvincias.value = stringProvincias.value;
            }
            else {
                const needle = val.toLowerCase()
                optionsProvincias.value = stringProvincias.value.filter((x) => x.nombre.toLowerCase().search(needle) != -1);
            }
        })
    }, 500)
};

async function filterDistritos(val, update) {
    setTimeout(() => {
        update(async () => {
            if (val === '') {
                optionsDistritos.value = stringDistritos.value;
            }
            else {
                const needle = val.toLowerCase()
                optionsDistritos.value = stringDistritos.value.filter((x) => x.nombre.toLowerCase().search(needle) != -1);
            }
        })
    }, 500)
};

function updateUbigeo() {
    if (modelDepartamento.value != null && modelProvincia.value != null && modelDistrito.value != null) {
        const ubigeo_cod = modelDepartamento.value.cod_dep + modelProvincia.value.cod_prov + modelDistrito.value.cod_dist;
        emits("selectedItem", ubigeo_cod);
    };
}

function updateDepartamento(event) {
    modelDepartamento.value = event;
    modelProvincia.value = null;
    modelDistrito.value = null;
    getProvincias(event.cod_dep);
}

function updateProvincia(event) {
    modelProvincia.value = event;
    modelDistrito.value = null;
    getDistritos(event.cod_dep, event.cod_prov);
}

function updateDistrito(event) {
    modelDistrito.value = event;
    updateUbigeo();
}

function setData(provincia, distrito){
    modelDistrito.value = distrito;
    modelProvincia.value = provincia;
}

function reset() {
    // modelDepartamento.value = null;
    modelProvincia.value = null;
    modelDistrito.value = null;
}

//Expose
defineExpose({
    reset,
    getUbigeo,
    setUbigeo,
    setData
});
</script>