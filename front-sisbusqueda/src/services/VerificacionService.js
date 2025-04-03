import { api } from "src/boot/axios";

class VerificacionService {
    static async getData(params) {
        return (await api.get('/api/registro_verificaciones',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/registro_verificaciones/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/registro_verificaciones/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/registro_verificaciones", reg)).data;
        } else {
            return (await api.put(`/api/registro_verificaciones/${reg.id}`, reg)).data;
        }
    }
}

export default VerificacionService;