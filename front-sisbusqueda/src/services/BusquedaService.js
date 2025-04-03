import { api } from "src/boot/axios";

class BusquedaService {
    static async getData(params) {
        return (await api.get('/api/registro_busquedas',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/registro_busquedas/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/registro_busquedas/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/registro_busquedas", reg)).data;
        } else {
            return (await api.put(`/api/registro_busquedas/${reg.id}`, reg)).data;
        }
    }
}

export default BusquedaService;