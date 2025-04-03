import { api } from "src/boot/axios";

class PagoService {
    static async getData(params) {
        return (await api.get('/api/pagos',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/pagos/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/pagos/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/pagos", reg)).data;
        } else {
            return (await api.put(`/api/pagos/${reg.id}`, reg)).data;
        }
    }
}

export default PagoService;
